<?php

namespace App\Service;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;

use InvalidArgumentException;
use App\Entity\Estado;
use App\Entity\HistorialEstado;
use App\Entity\Rol;
use App\Entity\Pedido;
use Symfony\Component\HttpFoundation\Response;
use DateTime;

class VPedidoService
{
    private EntityManagerInterface $em;
    private Security $security;

    public function __construct(EntityManagerInterface $em, Security $security)
    {
        $this->em = $em;
        $this->security = $security;
    }

    /**
     * Retorna todos los pedidos generado del dia por un cliente 
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

    /**
     * Retorna todos los pedidos generado del dia por todos los clientes
     *
     * @return array
     * @author Luis Sanchez <betancurluis20@gmail.com> 2026-03-28
     */
    public function allPedidos(): array
    {
        $qb =  $this->em->getConnection()->createQueryBuilder();

        $qb->select('*')
            ->from('vpedido', 'v')
            ->where("fecha_hora_pedido BETWEEN CONCAT(CURDATE(), ' 00:00:00') AND CONCAT(CURDATE(), ' 23:59:59')")
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

    /**
     * Retorna todos los pedidos generados del dia pendientes de entrega
     *
     * @return array
     * @author Luis Sanchez <betancurluis20@gmail.com> 2026-07-11
     */
    public function allPedidosEntrega(): array
    {
        $qb =  $this->em->getConnection()->createQueryBuilder();

        $qb->select('*')
            ->from('vpedido', 'v')
            ->where("fecha_hora_pedido BETWEEN CONCAT(CURDATE(), ' 00:00:00') AND CONCAT(CURDATE(), ' 23:59:59')")
            ->andWhere("estado IN ('" . Pedido::LISTO_PARA_ENTREGA . "')")
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

    /**
     * Retorna todos los pedidos generados del dia pendientes de cobro en caja
     *
     * @return array
     * @author Luis Sanchez <betancurluis20@gmail.com> 2026-07-11
     */
    public function allPedidosCaja(): array
    {
        $qb =  $this->em->getConnection()->createQueryBuilder();

        $qb->select('*')
            ->from('vpedido', 'v')
            ->where("fecha_hora_pedido BETWEEN CONCAT(CURDATE(), ' 00:00:00') AND CONCAT(CURDATE(), ' 23:59:59')")
            ->andWhere("estado IN ('" . Pedido::PENDIENTE_PAGO . "')")
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
                    'total'             => 0,
                    'items'             => []
                ];
            }

            // Sumar al total del pedido
            $pedidosAgrupados[$idPedido]['total'] += $row['precio'] * $row['cantidad'];

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

    /**
     * Actualiza el estado del pedido
     *
     * @param int $idPedido
     * @param string $estado
     * @return void
     * @author Luis Sanchez <betancurluis20@gmail.com> 2026-06-22
     */
    public function updateEstado(int $idPedido, string $estado): void
    {
        $Estado = $this->em->getRepository(Estado::class)->findOneBy([
            'nombre' => $estado
        ]);
        if(!$Estado) {
            throw new InvalidArgumentException("Estado no encontrado", Response::HTTP_NOT_FOUND);
        }

        $HistorialEstado = $this->em->getRepository(HistorialEstado::class)->findOneBy([
            'fk_pedido' => $idPedido,
            'estado' => 'Activo'
        ]);
        if($HistorialEstado) {
            $HistorialEstado->setEstado('Inactivo');
            $this->em->flush();
        }
        
        $UsuarioActualSession = $this->security->getUser();

        $Rol = $this->em->getRepository(Rol::class)->findOneBy([
            'fk_usuario' => $UsuarioActualSession->getId(),
            'estado' => 'Activo'
        ]);
        if(!$Rol) {
            throw new InvalidArgumentException("El usuario no tiene un rol asignado o activo", Response::HTTP_NOT_FOUND);
        }

        $HistorialEstado = new HistorialEstado();
        $HistorialEstado->setFkPedido($idPedido);
        $HistorialEstado->setFkEstado($Estado->getId());
        $HistorialEstado->setFkRol($Rol->getId());
        $HistorialEstado->setFechaHora(new DateTime());
        $HistorialEstado->setEstado('Activo');

        $this->em->persist($HistorialEstado);
        $this->em->flush();
    }
}