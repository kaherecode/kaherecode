<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\ProfileType;
use App\Form\UserRegistrationType;
use Symfony\Component\Mime\Address;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class UserController extends AbstractController
{
    /**
     * @Route("/register", name="user_register")
     */
    public function register(
        Request $request,
        UserPasswordEncoderInterface $passwordEncoder,
        MailerInterface $mailer
    ) {
        $user = new User();
        $form = $this->createForm(UserRegistrationType::class, $user);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();

            // check if email doesn't exists
            if ($em->getRepository(User::class)->findOneByEmail($user->getEmail())) {
                $this->addFlash(
                    'error',
                    'This email address is already taken. Use another one or login.'
                );

                return $this->render('user/register.html.twig', [
                    'form' => $form->createView(),
                ]);
            }

            // check if username doesn't exists
            if ($em->getRepository(User::class)->findOneByUsername($user->getUsername())) {
                $this->addFlash(
                    'error',
                    'This username is already taken. Use another one or login.'
                );

                return $this->render('user/register.html.twig', [
                    'form' => $form->createView(),
                ]);
            }

            // encode user password
            $user->setPassword(
                $passwordEncoder
                    ->encodePassword($user, $user->getPassword())
            );

            $user->setConfirmationToken(sha1(uniqid()));

            $em->persist($user);
            $em->flush();

            $email = (new TemplatedEmail())
                // TODO: create a event subscriber to set the same from address for the whole app
                ->from(new Address(
                    'contact@kaherecode.com',
                    'Aliou de Kaherecode'
                ))
                ->to(new Address($user->getEmail(), $user->getFullName()))
                ->subject("Bienvenue sur Kaherecode " .$user->getUsername(). "!")
                ->htmlTemplate('emails/signup.html.twig')
                ->context(['user' => $user]);

            $mailer->send($email);

            $this->addFlash(
                'success',
                'Thanks for registering. Check your mail for confirmation.'
            );
        }

        return $this->render('user/register.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/profile", name="profile")
     */
    public function profile()
    {
        $this->denyAccessUnlessGranted('ROLE_USER');

        return $this->render('user/profile.html.twig');
    }

    /**
     * @Route("/profile/edit", name="edit_profile")
     */
    public function editProfile(
        Request $request,
        UserPasswordEncoderInterface $passwordEncoder
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
            )) {
                $this->addFlash(
                    'error',
                    'Your password is not correct, try again!'
                );

                return $this->render('user/edit_profile.html.twig', [
                    'form' => $form->createView()
                ]);
            }

            $em = $this->getDoctrine()->getManager();
            $em->flush();

            $this->addFlash(
                'success',
                'Your profile have been successfully updated!'
            );
        }

        return $this->render('user/edit_profile.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/register/confirmation/{token}", name="account_confirmation")
     */
    public function accountConfirmation(Request $request, $token)
    {
    }
}
