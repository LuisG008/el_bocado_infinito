<?php

namespace App\Entity;

use App\Repository\EstadoRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: EstadoRepository::class)]
class Estado
{
    const ESTADO_PENDIENTE_PAGO = 1;
    const ESTADO_CANCELADO = 2;
    const ESTADO_PAGADO = 3;
    const ESTADO_EN_PREPARACION = 4;
    const ESTADO_LISTO_PARA_ENTREGAR = 5;
    const ESTADO_ENTREGADO = 6;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $idestado = null;

    #[ORM\Column(length: 255)]
    private ?string $nombre = null;

    #[ORM\Column(length: 100)]
    private ?string $color = null;

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

    public function getColor(): ?string
    {
        return $this->color;
    }

    public function setColor(string $color): static
    {
        $this->color = $color;

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
