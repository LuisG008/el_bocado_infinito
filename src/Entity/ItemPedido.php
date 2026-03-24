<?php

namespace App\Entity;

use App\Repository\ItemPedidoRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ItemPedidoRepository::class)]
class ItemPedido
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $iditem_pedido = null;

    #[ORM\Column]
    private ?int $fk_pedido = null;

    #[ORM\Column]
    private ?int $fk_menu = null;

    #[ORM\Column(length: 255)]
    private ?string $nombre = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $descripcion = null;

    #[ORM\Column(length: 255)]
    private ?string $tipo_consumo = null;

    #[ORM\Column]
    private ?int $precio = null;

    #[ORM\Column]
    private ?int $cantidad = null;

    #[ORM\Column(length: 255)]
    private ?string $ruta_imagen = null;

    public function getId(): ?int
    {
        return $this->iditem_pedido;
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

    public function getFkMenu(): ?int
    {
        return $this->fk_menu;
    }

    public function setFkMenu(int $fk_menu): static
    {
        $this->fk_menu = $fk_menu;

        return $this;
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

    public function getDescripcion(): ?string
    {
        return $this->descripcion;
    }

    public function setDescripcion(string $descripcion): static
    {
        $this->descripcion = $descripcion;

        return $this;
    }

    public function getTipoConsumo(): ?string
    {
        return $this->tipo_consumo;
    }

    public function setTipoConsumo(string $tipo_consumo): static
    {
        $this->tipo_consumo = $tipo_consumo;

        return $this;
    }

    public function getPrecio(): ?int
    {
        return $this->precio;
    }

    public function setPrecio(int $precio): static
    {
        $this->precio = $precio;

        return $this;
    }

    public function getCantidad(): ?int
    {
        return $this->cantidad;
    }

    public function setCantidad(int $cantidad): static
    {
        $this->cantidad = $cantidad;

        return $this;
    }

    public function getRutaImagen(): ?string
    {
        return $this->ruta_imagen;
    }

    public function setRutaImagen(string $ruta_imagen): static
    {
        $this->ruta_imagen = $ruta_imagen;

        return $this;
    }
}
