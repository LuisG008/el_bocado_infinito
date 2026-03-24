<?php

namespace App\Entity;

use App\Repository\RolRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: RolRepository::class)]
class Rol
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $idrol = null;

    #[ORM\Column]
    private ?int $fk_usuario = null;

    #[ORM\Column]
    private ?int $fk_cargo = null;

    #[ORM\Column(length: 8)]
    private ?string $estado = null;

    public function getId(): ?int
    {
        return $this->idrol;
    }

    public function getFkUsuario(): ?int
    {
        return $this->fk_usuario;
    }

    public function setFkUsuario(int $fk_usuario): static
    {
        $this->fk_usuario = $fk_usuario;

        return $this;
    }

    public function getFkCargo(): ?int
    {
        return $this->fk_cargo;
    }

    public function setFkCargo(int $fk_cargo): static
    {
        $this->fk_cargo = $fk_cargo;

        return $this;
    }

    public function getEstado(): ?string
    {
        return $this->estado;
    }

    public function setEstado(string $estado): static
    {
        $this->estado = $estado;

        return $this;
    }
}
