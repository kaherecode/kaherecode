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
        Mailer $mailer
    ) {
        $user = new User();
        $form = $this->createForm(UserRegistrationType::class, $user);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
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
                'Thanks for registering. Check your mail for confirmation.'
            );
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
        CloudinaryService $uploader
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
                    'Your password is not correct, try again!'
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
                'Your profile have been successfully updated!'
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
        Mailer $mailer
    ) {
        if ($request->isMethod(Request::METHOD_POST)) {
            $em = $this->getDoctrine()->getManager();
            $user = $em->getRepository(User::class)
                ->findOneByEmail($request->get('email'));

            if (!$user) {
                $this->addFlash('error', 'This email address is not registered.');

                return $this->render('users/password_reset_request.html.twig');
            }

            $user->setConfirmationToken(sha1(uniqid()));
            $user->setPasswordRequestedAt(new \DateTime());

            $em->persist($user);
            $em->flush();

            $mailer->sendRequestPasswordMessage($user);

            $this->addFlash(
                'success',
                'A mail have been sent to you, check it to update your password.'
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
        $token
    ) {
        $em = $this->getDoctrine()->getManager();
        $user = $em->getRepository(User::class)
            ->findOneByConfirmationToken($token);

        if ($user && $request->isMethod(Request::METHOD_POST)) {
            $password = $request->get('password');

            if ($password !== $request->get('confirmPassword')) {
                $this->addFlash('error', 'Passwords are not the same.');

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
                    'Password is not valid. Sould be 8 or more characters. Should contains at least 1 special chars, 1 digit and 1 uppercace letter.'
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
                'Your password have been updated successfully. You can now log in!'
            );

            return $this->redirectToRoute('app_login');
        }

        return $this->render('users/reset_password.html.twig', ['user' => $user]);
    }
}
