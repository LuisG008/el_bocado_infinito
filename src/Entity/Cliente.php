<?php

namespace App\Entity;

use App\Repository\ClienteRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ClienteRepository::class)]
class Cliente
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $idcliente = null;

    #[ORM\Column]
    private ?int $identificacion = null;

    #[ORM\Column(length: 255)]
    private ?string $nombres = null;

    #[ORM\Column(length: 10)]
    private ?string $telefono = null;

    public function getId(): ?int
    {
        return $this->idcliente;
    }

    public function getIdentificacion(): ?int
    {
        return $this->identificacion;
    }

    public function setIdentificacion(int $identificacion): static
    {
        $this->identificacion = $identificacion;

        return $this;
    }

    public function getNombres(): ?string
    {
        return $this->nombres;
    }

    public function setNombres(string $nombres): static
    {
        $this->nombres = $nombres;

        return $this;
    }

    public function getTelefono(): ?string
    {
        return $this->telefono;
    }

    public function setTelefono(string $telefono): static
    {
        $this->telefono = $telefono;

        return $this;
    }
}
