<?php

namespace App\Entity;

use App\Repository\LogAccesoRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: LogAccesoRepository::class)]
class LogAcceso
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $idlog_acceso = null;

    #[ORM\Column]
    private ?int $fk_usuario = null;

    #[ORM\Column]
    private ?\DateTime $fecha_ingreso = null;

    #[ORM\Column]
    private ?\DateTime $fecha_cierre = null;

    public function getId(): ?int
    {
        return $this->idlog_acceso;
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

    public function getFechaIngreso(): ?\DateTime
    {
        return $this->fecha_ingreso;
    }

    public function setFechaIngreso(\DateTime $fecha_ingreso): static
    {
        $this->fecha_ingreso = $fecha_ingreso;

        return $this;
    }

    public function getFechaCierre(): ?\DateTime
    {
        return $this->fecha_cierre;
    }

    public function setFechaCierre(\DateTime $fecha_cierre): static
    {
        $this->fecha_cierre = $fecha_cierre;

        return $this;
    }
}
