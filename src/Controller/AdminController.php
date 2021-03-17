<?php

namespace App\Controller;

use App\Repository\UserRepository;
use App\Repository\CommentRepository;
use App\Repository\TutorialRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
 * @IsGranted("ROLE_ADMIN")
 */
class AdminController extends AbstractController
{
    /**
     * @Route("/admin", name="admin_dashboard")
     */
    public function index(
        TutorialRepository $tutorialRepository,
        UserRepository $userRepository,
        CommentRepository $commentRepository
    ): Response {
        $recentTutorials = $tutorialRepository->findBy(
            ['isPublished' => true],
            ['publishedAt' => 'DESC'],
            10,
            0
        );

        $popularTutorials = $tutorialRepository->findBy(
            ['isPublished' => true],
            ['views' => 'DESC'],
            10,
            0
        );

        $recentLogins = $userRepository->findBy(
            ['enabled' => true],
            ['lastLogin' => 'DESC'],
            10,
            0
        );

        $recentComments = $commentRepository->findBy(
            [],
            ['createdAt' => 'DESC'],
            10,
            0
        );

        return $this->render(
            'admin/index.html.twig',
            [
                'totalPageViews' => $tutorialRepository
                    ->getTutorialsTotalPageViews(),
                'totalPublishedTutorials' => $tutorialRepository
                    ->countPublishedTutorials(),
                'totalActiveUsers' => $userRepository->countActiveUsers(),
                'totalPublishedComments' => $commentRepository
                    ->countPublishedComments(),
                'tutorials' => $recentTutorials,
                'popularTutorials' => $popularTutorials,
                'recentLogins' => $recentLogins,
                'recentComments' => $recentComments
            ]
        );
    }
}
