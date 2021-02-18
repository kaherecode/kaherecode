<?php

namespace App\Controller;

use App\Entity\Tutorial;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class CommentController extends AbstractController
{
    /**
     * @Route("/comment/{uuid}", name="add_comment", methods={"POST"})
     */
    public function addComment(Tutorial $tutorial, Request $request): Response
    {
        dd($request->request->get("comment")["markdownContent"]);

        return $this->render('comment/index.html.twig', [
            'controller_name' => 'CommentController',
        ]);
    }
}
