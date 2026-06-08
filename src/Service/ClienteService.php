<?php

namespace App\Service;

use App\Entity\Cliente;
use Doctrine\ORM\EntityManagerInterface;
use phpDocumentor\Reflection\Types\False_;

class ClienteService
{
    private EntityManagerInterface $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    /**
     * Registra o actualiza un cliente
     *
     * @param array $data
     * @return Cliente
     * @author Luis Sanchez <betancurluis20@gmail.com> 2026-05-15
     */
    public function create(array $data): Cliente
    {
        $cliente = $this->em->getRepository(Cliente::class)->findOneBy([
            'identificacion' => $data['identificacion']
        ]);
        
        $add = false;
        if(!$cliente) {
            $add = true;
            $cliente = new Cliente();
            $cliente->setIdentificacion($data['identificacion']);
        }
        
        $cliente->setNombres($data['nombre']);
        $cliente->setTelefono($data['telefono']);

        if($add){
            $this->em->persist($cliente);
        }

        $this->em->flush();

        return $cliente;
    }
}