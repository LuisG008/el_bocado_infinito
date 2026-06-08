<?php

namespace App\Service;

use Doctrine\ORM\EntityManagerInterface;
use App\Entity\HistorialEstado;
use DateTime;

class HistorialEstadoService
{
    private EntityManagerInterface $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    /**
     * Registra un nuevo estado he inactiva el anterior si existe
     *
     * @param array $data
     * @return void
     * @author Luis Sanchez <luis.sanchez@cerok.com> 2026-05-19
     */
    public function registrarEstado(array $data)
    {
        $HistorialEstadoOld = $this->em->getRepository(HistorialEstado::class)->findOneBy([
            'fk_pedido' => $data['idpedido'],
            'estado' => 'Activo'
        ]);

        if($HistorialEstadoOld) {
            $HistorialEstadoOld->setEstado('Inactivo');
            $this->em->flush();
        }

        $HistorialEstado = new HistorialEstado();
        $HistorialEstado->setFkPedido($data['idpedido']);
        $HistorialEstado->setFkEstado($data['idestado']);
        $HistorialEstado->setFkRol($data['idrol']);
        $HistorialEstado->setEstado('Activo');
        $HistorialEstado->setFechaHora(new DateTime());

        $this->em->persist($HistorialEstado);
        $this->em->flush();
    }
}