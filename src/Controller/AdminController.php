<?php

namespace App\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\GameRepository;
use App\Repository\UserRepository;

/**
 * @Security("is_granted('ROLE_ADMIN')")
 */
class AdminController extends AbstractController
{
    #[Route('/admin', name: 'app_admin')]
    public function index(GameRepository $gameRepository, UserRepository $userRepository): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $games = $gameRepository->findAll();
        $users = $userRepository->findAll();

        return $this->render('admin/index.html.twig', [
            'users' => $users,
            'games' => $games,
        ]);
    }

    // Blocage/Déblocage d'un utilisateur
    #[Route('/admin/users/{userId}/block', name: 'admin_block_user')]
    public function blockUser(Request $request, UserRepository $userRepository, int $userId): Response
    {
        $user = $userRepository->find($userId);

        if (!$user) {
            throw $this->createNotFoundException('Utilisateur non trouvé.');
        }

        // Inverser l'état de blocage
        $user->setBlocked(!$user->isBlocked());
        $userRepository->save($user, true);

        return $this->redirectToRoute('app_admin');
    }
}
