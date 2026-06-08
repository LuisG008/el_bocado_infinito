<?php

namespace App\Entity;

use App\Repository\MenuRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: MenuRepository::class)]
class Menu
{
    const ESTADO_DISPONIBLE = 'Activo';
    const ESTADO_NO_DISPONIBLE = 'Inactivo';

    const TIPO_CONSUMO_MESA = 'Consumir en mesa';
    const TIPO_CONSUMO_LLEVAR = 'Para llevar';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $idmenu = null;

    #[ORM\Column(length: 255)]
    private ?string $nombre = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $descripcion = null;

    #[ORM\Column]
    private ?int $precio = null;

    #[ORM\Column(length: 255)]
    private ?string $ruta_imagen = null;

    #[ORM\Column(length: 8)]
    private ?string $estado = null;

    public function getId(): ?int
    {
        return $this->idmenu;
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

    public function getPrecio(): ?int
    {
        return $this->precio;
    }

    public function setPrecio(int $precio): static
    {
        $this->precio = $precio;

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
