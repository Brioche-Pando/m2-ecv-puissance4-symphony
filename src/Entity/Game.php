<?php

namespace App\Entity;

use App\Repository\GameRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: GameRepository::class)]
class Game
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTimeInterface $date = null;

    #[ORM\Column(length: 255)]
    private ?string $duration = null;

    #[ORM\Column(length: 255)]
    private ?string $winner = null;

    #[ORM\Column(length: 255)]
    private ?string $player1 = null;

    #[ORM\Column(length: 255)]
    private ?string $player2 = null;

    #[ORM\Column(type: Types::ARRAY)]
    private array $board = [];

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDate(): ?\DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(\DateTimeInterface $date): self
    {
        $this->date = $date;

        return $this;
    }

    public function getDuration(): ?string
    {
        return $this->duration;
    }

    public function setDuration(string $duration): self
    {
        $this->duration = $duration;

        return $this;
    }

    public function getWinner(): ?string
    {
        return $this->winner;
    }

    public function setWinner(string $winner): self
    {
        $this->winner = $winner;

        return $this;
    }

    public function getPlayer1(): ?string
    {
        return $this->player1;
    }

    public function setPlayer1(string $player1): self
    {
        $this->player1 = $player1;

        return $this;
    }

    public function getPlayer2(): ?string
    {
        return $this->player2;
    }

    public function setPlayer2(string $player2): self
    {
        $this->player2 = $player2;

        return $this;
    }

    public function getBoard(): array
    {
        return $this->board;
    }

    public function setBoard(array $board): self
    {
        $this->board = $board;

        return $this;
    }
}
