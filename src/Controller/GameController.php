<?php

namespace App\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Security("is_granted('ROLE_USER')")
 */
class GameController extends AbstractController
{
    private const BOARD_COLUMNS = 7;
    private const BOARD_ROWS = 6;

    #[Route('/game', name: 'app_game')]
    public function play(Request $request): Response
    {
        $board = $request->getSession()->get('board', $this->getEmptyBoard());
        $currentPlayer = $request->getSession()->get('currentPlayer', 'yellow');
        $isEnd = false;
        $winner = false;

        if ($request->isMethod('POST')) {
            $column = $request->get('column');
            $board = $this->addTokenToColumn($board, $column, $currentPlayer);
            $isEnd = $this->checkEndGame($board, $currentPlayer);

            var_dump($isEnd);
            if ($isEnd) {
                $winner = 'tie' === $isEnd ? 'tie' : $currentPlayer;
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
