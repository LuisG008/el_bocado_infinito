<?php

namespace App\Entity;

use App\Repository\PedidoRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PedidoRepository::class)]
class Pedido
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $idpedido = null;

    #[ORM\Column]
    private ?int $fk_cliente = null;

    #[ORM\Column]
    private ?\DateTime $fecha_hora = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $ruta_factura = null;

    public function getId(): ?int
    {
        return $this->idpedido;
    }

    public function getFkCliente(): ?int
    {
        return $this->fk_cliente;
    }

    public function setFkCliente(int $fk_cliente): static
    {
        $this->fk_cliente = $fk_cliente;

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

    public function getRutaFactura(): ?string
    {
        return $this->ruta_factura;
    }

    public function setRutaFactura(?string $ruta_factura): static
    {
        $this->ruta_factura = $ruta_factura;

        return $this;
    }
}
