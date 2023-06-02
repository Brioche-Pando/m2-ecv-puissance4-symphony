<?php

namespace App\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\GameRepository;
use App\Entity\Game;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

/**
 * @Security("is_granted('ROLE_USER')")
 */
class GameController extends AbstractController
{
    private const BOARD_COLUMNS = 7;
    private const BOARD_ROWS = 6;
    private $startTime;

    private $gameRepository;

    public function __construct(GameRepository $gameRepository)
    {
        $this->gameRepository = $gameRepository;
    }

    #[Route('/game', name: 'app_game')]
    public function play(Request $request): Response
    {
        $board = $request->getSession()->get('board', $this->getEmptyBoard());
        $currentPlayer = $request->getSession()->get('currentPlayer', 'yellow');
        $isEnd = false;
        $winner = false;

        if (!$request->getSession()->has('startTime')) {
            $this->startTime = time();
            $request->getSession()->set('startTime', $this->startTime);
        } else {
            $this->startTime = $request->getSession()->get('startTime');
        }

        if ($request->isMethod('POST')) {
            $column = $request->get('column');
            $board = $this->addTokenToColumn($board, $column, $currentPlayer);
            $isEnd = $this->checkEndGame($board, $currentPlayer);
            if ($isEnd) {
                $winner = 'tie' === $isEnd ? 'tie' : $currentPlayer;

                $endTime = time();
                $duration = $endTime - $this->startTime;
                $this->registerGameInDB($board, $currentPlayer, $duration);
                $mailerInterface = new MailerInterface();
                $this->sendEmail($mailerInterface);
            } else {
                // Switch player
                $currentPlayer = ('yellow' === $currentPlayer) ? 'red' : 'yellow';

                $request->getSession()->set('board', $board);
                $request->getSession()->set('currentPlayer', $currentPlayer);
            }
        }

        return $this->render('game/index.html.twig', [
            'board' => $board,
            'currentPlayer' => $currentPlayer,
            'isEnd' => $isEnd,
            'winner' => $winner,
        ]);
    }

    #[Route('/reset', name: 'game_reset')]
    public function reset(Request $request): Response
    {
        $request->getSession()->invalidate();

        return $this->redirectToRoute('app_game');
    }

    private function sendEmail(MailerInterface $mailer): void
    {
        $email = (new Email())
            ->from('b.neveu09@gmail.com')
            ->to('b.neveu09@gmail')
            //->cc('cc@example.com')
            //->bcc('bcc@example.com')
            //->replyTo('fabien@example.com')
            //->priority(Email::PRIORITY_HIGH)
            ->subject('Puissane 4 : Fin de partie')
            ->text('Fin de partie')
            ->html('<p>Fin de partie</p>');

        $mailer->send($email);
    }

    private function registerGameInDB(array $board, string $currentPlayer, int $duration): void
    {
        $game = new Game();
        $game->setDate(new \DateTime());
        $game->setPlayer1($this->getUser()->getId());
        $game->setPlayer2($this->getUser()->getId());
        $game->setWinner($currentPlayer);
        $game->setBoard($board);
        
        // Convertir la durée en minutes et secondes
        $minutes = floor($duration / 60);
        $seconds = $duration % 60;
        $formattedDuration = sprintf('%02d:%02d', $minutes, $seconds);
    
        $game->setDuration($formattedDuration);

        $this->gameRepository->save($game, true);
    }

    private function getEmptyBoard(): array
    {
        $board = [];

        for ($i = 0; $i < self::BOARD_ROWS; ++$i) {
            $board[$i] = array_fill(0, self::BOARD_COLUMNS, '');
        }

        return $board;
    }

    private function addTokenToColumn(array $board, int $column, string $token): array
    {
        for ($i = self::BOARD_ROWS - 1; $i >= 0; --$i) {
            if ('' === $board[$i][$column]) {
                $board[$i][$column] = $token;
                break;
            }
        }

        return $board;
    }

    private function checkEndGame(array $board, string $player): string
    {
        // Check horizontal
        for ($i = 0; $i < self::BOARD_ROWS; ++$i) {
            $count = 0;
            for ($j = 0; $j < self::BOARD_COLUMNS; ++$j) {
                if ($board[$i][$j] === $player) {
                    ++$count;
                    if (4 === $count) {
                        return $player;
                    }
                } else {
                    $count = 0;
                }
            }
        }

        // Check vertical
        for ($j = 0; $j < self::BOARD_COLUMNS; ++$j) {
            $count = 0;
            for ($i = 0; $i < self::BOARD_ROWS; ++$i) {
                if ($board[$i][$j] === $player) {
                    ++$count;
                    if (4 === $count) {
                        return $player;
                    }
                } else {
                    $count = 0;
                }
            }
        }

        // Check diagonal (top left to bottom right)
        for ($i = 0; $i <= self::BOARD_ROWS - 4; ++$i) {
            for ($j = 0; $j <= self::BOARD_COLUMNS - 4; ++$j) {
                $count = 0;
                for ($k = 0; $k < 4; ++$k) {
                    if ($board[$i + $k][$j + $k] === $player) {
                        ++$count;
                        if (4 === $count) {
                            return $player;
                        }
                    } else {
                        $count = 0;
                    }
                }
            }
        }

        // Check diagonal (bottom left to top right)
        for ($i = self::BOARD_ROWS - 1; $i >= 3; --$i) {
            for ($j = 0; $j <= self::BOARD_COLUMNS - 4; ++$j) {
                $count = 0;
                for ($k = 0; $k < 4; ++$k) {
                    if ($board[$i - $k][$j + $k] === $player) {
                        ++$count;
                        if (4 === $count) {
                            return $player;
                        }
                    } else {
                        $count = 0;
                    }
                }
            }
        }

        // Check for tie
        for ($i = 0; $i < self::BOARD_ROWS; ++$i) {
            for ($j = 0; $j < self::BOARD_COLUMNS; ++$j) {
                if ('' === $board[$i][$j]) {
                    return false;
                }
            }
        }

        return 'tie';
    }
}
