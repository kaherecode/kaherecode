<?php

namespace App\Controller;

use App\Entity\User;
use App\Service\Mailer;
use App\Form\ProfileType;
use App\Form\UserRegistrationType;
use App\Service\CloudinaryService;
use App\Repository\TutorialRepository;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class UserController extends AbstractController
{
    /**
     * @Route("/@{username}", name="show_user")
     */
    public function show(
        Request $request,
        User $user,
        TutorialRepository $tutorialRepository,
        PaginatorInterface $paginator,
        int $paginatorPerPage
    ) {
        $tutorials = $paginator->paginate(
            $tutorialRepository->getPublishedByUserQueryBuilder($user),
            $request->query->getInt('page', 1),
            $paginatorPerPage
        );

        return $this->render(
            'users/show.html.twig',
            ['user' => $user, 'tutorials' => $tutorials]
        );
    }

    /**
     * @Route("/register", name="user_register")
     */
    public function register(
        Request $request,
        UserPasswordEncoderInterface $passwordEncoder,
        Mailer $mailer,
        TranslatorInterface $translator
    ) {
        $user = new User();
        $form = $this->createForm(UserRegistrationType::class, $user);

        $form->handleRequest($request);
        if ($form->isSubmitted()&& $form->isValid()) {
            if ($this->captchaverify($request->get('g-recaptcha-response'))) {
                $em = $this->getDoctrine()->getManager();

                // encode user password
                $user->setPassword(
                    $passwordEncoder
                        ->encodePassword($user, $user->getPassword())
                );
                $user->setConfirmationToken(sha1(uniqid()));

                $em->persist($user);
                $em->flush();

                $mailer->sendSignUpMessage($user);

                $this->addFlash(
                    'success',
                    $translator->trans("notifications.check_email_for_confirmation")
                );
            } else {
                $this->addFlash(
                    'recaptcha',
                    $translator->trans("notifications.recaptcha")
                );
            }
        }

        return $this->render(
            'users/register.html.twig',
            ['form' => $form->createView()]
        );
    }

    /**
     * @Route("/profile", name="profile")
     */
    public function profile()
    {
        $this->denyAccessUnlessGranted('ROLE_USER');

        return $this->render('users/profile.html.twig');
    }

    /**
     * @Route("/profile/edit", name="edit_profile")
     */
    public function editProfile(
        Request $request,
        UserPasswordEncoderInterface $passwordEncoder,
        CloudinaryService $uploader,
        TranslatorInterface $translator
    ) {
        $this->denyAccessUnlessGranted('ROLE_USER');

        $user = $this->getUser();
        $form = $this->createForm(ProfileType::class, $user);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            // check if the password is valid
            if (!$passwordEncoder->isPasswordValid(
                $user,
                $form->get('currentPassword')->getData()
            )
            ) {
                $this->addFlash(
                    'error',
                    $translator->trans("notifications.wrong_password")
                );

                return $this->render(
                    'users/edit_profile.html.twig',
                    ['form' => $form->createView()]
                );
            }

            $avatar = $form->get("avatar")->getData();
            if ($avatar) {
                if ($user->getAvatar() !== null) {
                    $uploader->delete($user->getAvatar());
                }

                $upload = $uploader
                    ->upload($avatar, ['folder' => 'kaherecode/users/']);

                $user->setAvatar($upload['fileURL']);
            }

            $em = $this->getDoctrine()->getManager();
            $em->flush();

            $this->addFlash(
                'success',
                $translator->trans("notifications.profile_updated")
            );
        }

        return $this->render(
            'users/edit_profile.html.twig',
            ['form' => $form->createView()]
        );
    }

    /**
     * @Route("/register/confirmation/{token}", name="account_confirmation")
     */
    public function accountConfirmation(Request $request, $token)
    {
        $em = $this->getDoctrine()->getManager();
        $user = $em->getRepository(User::class)
            ->findOneByConfirmationToken($token);

        if ($user) {
            $user->setConfirmationToken(null);
            $user->setEnabled(true);
            $em->flush();

            // TODO: send a welcome email with details on first step to take
        }

        return $this->render(
            'users/account_confirmation.html.twig',
            ['user' => $user]
        );
    }

    /**
     * @Route("/password-reset/request", name="password_reset_request")
     */
    public function passwordResetRequest(
        Request $request,
        Mailer $mailer,
        TranslatorInterface $translator
    ) {
        if ($request->isMethod(Request::METHOD_POST)) {
            $em = $this->getDoctrine()->getManager();
            $user = $em->getRepository(User::class)
                ->findOneByEmail($request->get('email'));

            if (!$user) {
                $this->addFlash(
                    'error',
                    $translator->trans("notifications.not_registered_email")
                );

                return $this->render('users/password_reset_request.html.twig');
            }

            $user->setConfirmationToken(sha1(uniqid()));
            $user->setPasswordRequestedAt(new \DateTime());

            $em->persist($user);
            $em->flush();

            $mailer->sendRequestPasswordMessage($user);

            $this->addFlash(
                'success',
                $translator->trans("notifications.check_email_for_password")
            );
        }

        return $this->render('users/password_reset_request.html.twig');
    }

    /**
     * @Route("/reset-password/{token}", name="reset_password")
     */
    public function resetPassword(
        Request $request,
        UserPasswordEncoderInterface $passwordEncoder,
        TranslatorInterface $translator,
        $token
    ) {
        $em = $this->getDoctrine()->getManager();
        $user = $em->getRepository(User::class)
            ->findOneByConfirmationToken($token);

        if ($user && $request->isMethod(Request::METHOD_POST)) {
            $password = $request->get('password');

            if ($password !== $request->get('confirmPassword')) {
                $this->addFlash(
                    'error',
                    $translator->trans("notifications.different_password")
                );

                return $this->render(
                    'users/reset_password.html.twig',
                    ['user' => $user]
                );
            }

            // password validation
            if (!preg_match(
                '/^(?=.*\d)(?=.*[a-z])(?=.*[A-Z])(?=.*[a-zA-Z]).{8,}$/',
                $password
            )
            ) {
                $this->addFlash(
                    'error',
                    $translator->trans("notifications.not_valid_password")
                );

                return $this->render(
                    'users/reset_password.html.twig',
                    ['user' => $user]
                );
            }

            // encode user password
            $user->setPassword(
                $passwordEncoder
                    ->encodePassword($user, $password)
            );
            $user->setConfirmationToken(null);
            $user->setPasswordRequestedAt(null);

            $em->flush();

            $this->addFlash(
                'success',
                $translator->trans("notifications.successfully_updated_password")
            );

            return $this->redirectToRoute('app_login');
        }

        return $this->render('users/reset_password.html.twig', ['user' => $user]);
    }

    protected function captchaverify($recaptcha)
    {
        $url = "https://www.google.com/recaptcha/api/siteverify";
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt(
            $ch,
            CURLOPT_POSTFIELDS,
            ["secret" => $_ENV['RECAPTCHA_SECRET'], "response" => $recaptcha]
        );
        $response = curl_exec($ch);
        curl_close($ch);
        $data = json_decode($response);

        return $data->success;
    }
}
