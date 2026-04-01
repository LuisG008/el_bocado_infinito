<?php

namespace App\Entity;

use App\Repository\UsuarioRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;

#[ORM\Entity(repositoryClass: UsuarioRepository::class)]
class Usuario implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $idusuario = null;

    #[ORM\Column]
    private ?int $identificacion = null;

    #[ORM\Column(length: 255)]
    private ?string $nombres = null;

    #[ORM\Column(length: 10)]
    private ?string $telefono = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $clave = null;

    #[ORM\Column(length: 8)]
    private ?string $estado = null;

    public function getId(): ?int
    {
        return $this->idusuario;
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

    public function getClave(): ?string
    {
        return $this->clave;
    }

    public function setClave(string $clave): static
    {
        $this->clave = $clave;

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

    public function getUserIdentifier(): string
    {
        return $this->identificacion;
    }

    public function getRoles(): array
    {
        return ['ROLE_USER']; // luego lo conectamos con tu tabla rol
    }

    public function eraseCredentials(): void
    {
        // si no manejas datos sensibles temporales, déjalo vacío
    }

    public function getPassword(): string
    {
        return $this->clave;
    }

}
