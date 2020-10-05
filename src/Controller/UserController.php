<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserRegistrationType;
use Symfony\Component\HttpFoundation\Request;
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
        UserPasswordEncoderInterface $passwordEncoder
    ) {
        $user = new User();
        $form = $this->createForm(UserRegistrationType::class, $user);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            // encode user password
            $user->setPassword(
                $passwordEncoder
                    ->encodePassword($user, $user->getPassword())
            );

            $em = $this->getDoctrine()->getManager();
            $em->persist($user);
            $em->flush();

            $this->addFlash(
                'success',
                'Thanks for registering. Check your mail for confirmation.'
            );
        }

        return $this->render('user/register.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
