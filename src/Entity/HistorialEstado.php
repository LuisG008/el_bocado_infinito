<?php

namespace App\Entity;

use App\Repository\HistorialEstadoRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: HistorialEstadoRepository::class)]
class HistorialEstado
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $idhistorial_estado = null;

    #[ORM\Column]
    private ?int $fk_pedido = null;

    #[ORM\Column]
    private ?int $fk_estado = null;

    #[ORM\Column]
    private ?int $fk_rol = null;

    #[ORM\Column]
    private ?\DateTime $fecha_hora = null;

    public function getId(): ?int
    {
        return $this->idhistorial_estado;
    }

    public function getFkPedido(): ?int
    {
        return $this->fk_pedido;
    }

    public function setFkPedido(int $fk_pedido): static
    {
        $this->fk_pedido = $fk_pedido;

        return $this;
    }

    public function getFkEstado(): ?int
    {
        return $this->fk_estado;
    }

    public function setFkEstado(int $fk_estado): static
    {
        $this->fk_estado = $fk_estado;

        return $this;
    }

    public function getFkRol(): ?int
    {
        return $this->fk_rol;
    }

    public function setFkRol(int $fk_rol): static
    {
        $this->fk_rol = $fk_rol;

        return $this;
    }

    public function getFechaHora(): ?\DateTime
    {
        return $this->fecha_hora;
    }

    public function setFechaHora(\DateTime $fecha_hora): static
    {
        $this->fecha_hora = $fecha_hora;

        return $this;
    }
}
