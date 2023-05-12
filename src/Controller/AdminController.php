<?php

namespace App\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\GameRepository;

/**
 * @Security("is_granted('ROLE_ADMIN')")
 */
class AdminController extends AbstractController
{
    #[Route('/admin', name: 'app_admin')]
    public function index(GameRepository $gameRepository): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $games = $gameRepository->findAll();

        foreach ($games as $game) {
            $board = $game->getBoard();
            dump($board);
        }

        return $this->render('admin/index.html.twig', [
            'games' => $games,
        ]);
    }
}
