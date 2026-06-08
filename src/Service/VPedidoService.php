<?php

namespace App\Service;

use Doctrine\ORM\EntityManagerInterface;

class VPedidoService
{
    private EntityManagerInterface $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    /**
     * Retorna todos los pedidos generado del dia 
     *
     * @return array
     * @author Luis Sanchez <betancurluis20@gmail.com> 2026-03-28
     */
    public function pedidosActuales(int $idcliente): array
    {
        $qb =  $this->em->getConnection()->createQueryBuilder();

        $qb->select('*')
            ->from('vpedido', 'v')
            ->where("idcliente = :idcliente")
            ->andWhere("fecha_hora_pedido BETWEEN CONCAT(CURDATE(), ' 00:00:00') AND CONCAT(CURDATE(), ' 23:59:59')")
            //->andWhere("v.estado = 'Activo'")
            ->setParameter('idcliente', $idcliente)
            ->orderBy('idpedido', 'DESC');

        $pedidos = $qb->executeQuery()->fetchAllAssociative();
        
        $pedidosAgrupados = [];

        foreach ($pedidos as $row) {

            $idPedido = $row['idpedido'];

            // Si el pedido no existe todavía, se crea
            if (!isset($pedidosAgrupados[$idPedido])) {

                $pedidosAgrupados[$idPedido] = [
                    'idpedido'          => $row['idpedido'],
                    'identificacion'    => $row['identificacion'],
                    'idcliente'         => $row['idcliente'],
                    'cliente'           => $row['cliente'],
                    'telefono_cliente'  => $row['telefono_cliente'],
                    'fecha_hora_pedido' => $row['fecha_hora_pedido'],
                    'estado'            => $row['estado'],
                    'color_estado'      => $row['color_estado'],
                    'items'             => []
                ];
            }

            // Agregar item al pedido
            $pedidosAgrupados[$idPedido]['items'][] = [
                'iditem_pedido' => $row['iditem_pedido'],
                'nombre_item_pedido' => $row['nombre_item_pedido'],
                'descripcion_pedido' => $row['descripcion_pedido'],
                'tipo_consumo' => $row['tipo_consumo'],
                'precio' => $row['precio'],
                'cantidad' => $row['cantidad']
            ];
        }

        // índices numéricos
        $pedidosAgrupados = array_values($pedidosAgrupados);

        return $pedidosAgrupados;
    }

    public function historialPedidos(array $estadosPedidos): array
    {
        $historial = [];
        foreach ($estadosPedidos as $pedido) {
            $idPedido = $pedido['idpedido'];
            $qb =  $this->em->getConnection()->createQueryBuilder()
                ->select('h.fecha_hora,e.nombre,h.estado')
                ->from('historial_estado', 'h')
                ->join('h', 'estado', 'e', 'h.fk_estado = e.idestado')
                ->where("h.fk_pedido = :idpedido")
                ->setParameter('idpedido', $idPedido)
                ->orderBy('h.idhistorial_estado', 'ASC');

            $historial[$idPedido] = $qb->executeQuery()->fetchAllAssociative();
        }

        return $historial;
    }
}