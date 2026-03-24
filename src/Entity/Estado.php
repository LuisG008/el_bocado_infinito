<?php

namespace App\Entity;

use App\Repository\EstadoRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: EstadoRepository::class)]
class Estado
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $idestado = null;

    #[ORM\Column(length: 255)]
    private ?string $nombre = null;

    #[ORM\Column(length: 8)]
    private ?string $estado_nombre = null;

    public function getId(): ?int
    {
        return $this->idestado;
    }

    public function getNombre(): ?string
    {
        return $this->nombre;
    }

    public function setNombre(string $nombre): static
    {
        $this->nombre = $nombre;

        return $this;
    }

    public function getEstadoNombre(): ?string
    {
        return $this->estado_nombre;
    }

    public function setEstadoNombre(string $estado_nombre): static
    {
        $this->estado_nombre = $estado_nombre;

        return $this;
    }
}
