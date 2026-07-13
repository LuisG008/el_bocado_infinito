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
     * Retorna todos los pedidos generados del dia pendientes para preparar en cocina
     *
     * @return array
     * @author Luis Sanchez <betancurluis20@gmail.com> 2026-07-11
     */
    public function allPedidosCocina(): array
    {
        $qb =  $this->em->getConnection()->createQueryBuilder();

        $qb->select('*')
            ->from('vpedido', 'v')
            ->where("fecha_hora_pedido BETWEEN CONCAT(CURDATE(), ' 00:00:00') AND CONCAT(CURDATE(), ' 23:59:59')")
            ->andWhere("estado IN ('" . Pedido::PAGADO . "', '" . Pedido::EN_PREPARACION . "')")
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

    /**
     * Retorna la cantidad total de cada producto vendido
     *
     * @return array
     * @author Luis Sanchez <betancurluis20@gmail.com> 2026-07-12
     */
    public function cantidadProductosVendidos(): array
    {
        $qb =  $this->em->getConnection()->createQueryBuilder();

        $qb->select('nombre_item_pedido, SUM(cantidad) AS cantidad_vendida')
            ->from('vpedido', 'v')
            // ->where("fecha_hora_pedido BETWEEN CONCAT(CURDATE(), ' 00:00:00') AND CONCAT(CURDATE(), ' 23:59:59')")
            ->groupBy('nombre_item_pedido')
            ->orderBy('cantidad_vendida', 'DESC');

        $prodictos = $qb->executeQuery()->fetchAllAssociative();

        $resultados = [];
        foreach ($prodictos as $producto) {
            $resultados['series'][] = (int)$producto['cantidad_vendida'];
            $resultados['labels'][] = $producto['nombre_item_pedido'];
        }

        return $resultados;
    }

    /**
     * Retorna la cantidad de productos vendidos por semana
     *
     * @return array
     * @author Luis Sanchez <betancurluis20@gmail.com> 2026-07-12
     */
    public function cantidadVentasSemana(): array
    {
        $qb =  $this->em->getConnection()->createQueryBuilder();

        $qb->select('DAYOFWEEK(fecha_hora_pedido) AS dia_semana, COUNT(DISTINCT idpedido) AS cantidad_ventas')
            ->from('vpedido', 'v')
            ->where("fecha_hora_pedido >= DATE_SUB(CURDATE(), INTERVAL 6 DAY)")
            ->groupBy('DAYOFWEEK(fecha_hora_pedido)')
            ->orderBy('DAYOFWEEK(fecha_hora_pedido)', 'ASC');

        $ventas = $qb->executeQuery()->fetchAllAssociative();
        
        $resultados = [];
        foreach ($ventas as $venta) {
            $resultados['series'][] = (int)$venta['cantidad_ventas'];
            $resultados['labels'][] = $this->getNombreDiaSemana($venta['dia_semana']);
        }

        return $resultados;
    }

    /**
     * Retorna el dia de la semana en texto
     *
     * @param int $diaSemana
     * @return string
     * @author Luis Sanchez <betancurluis20@gmail.com> 2026-07-12
     */
    private function getNombreDiaSemana(int $diaSemana): string
    {
        $dias = [
            1 => 'Domingo',
            2 => 'Lunes',
            3 => 'Martes',
            4 => 'Miércoles',
            5 => 'Jueves',
            6 => 'Viernes',
            7 => 'Sábado'
        ];

        return $dias[$diaSemana] ?? '';
    }

    /**
     * Retorna la cantidad de productos vendidos cada dia de la semana
     *
     * @return array
     * @author Luis Sanchez <betancurluis20@gmail.com> 2026-07-12
     */
    public function cantidadProductosVendidosSemana(): array
    {
        $qb =  $this->em->getConnection()->createQueryBuilder();

        $qb->select('DAYOFWEEK(fecha_hora_pedido) AS dia, nombre_item_pedido, SUM(cantidad) AS cantidad')
            ->from('vpedido', 'v')
            ->where("fecha_hora_pedido >= DATE_SUB(CURDATE(), INTERVAL 6 DAY) 
                AND nombre_item_pedido IN (
                SELECT nombre_item_pedido
                FROM (
                    SELECT
                        nombre_item_pedido
                    FROM vpedido
                    WHERE fecha_hora_pedido >= DATE_SUB(CURDATE(), INTERVAL 6 DAY)
                    GROUP BY nombre_item_pedido
                    ORDER BY SUM(cantidad) DESC
                    LIMIT 5
                ) t
            )")
            ->groupBy('DAYOFWEEK(fecha_hora_pedido),nombre_item_pedido')
            ->orderBy('DAYOFWEEK(fecha_hora_pedido)', 'ASC')
            ->addOrderBy('nombre_item_pedido', 'ASC');

        $productos = $qb->executeQuery()->fetchAllAssociative();

        // Orden que quieres mostrar
        $dias = [
            2 => 'Lunes',
            3 => 'Martes',
            4 => 'Miércoles',
            5 => 'Jueves',
            6 => 'Viernes',
            7 => 'Sábado',
            1 => 'Domingo'
        ];

        $resultado = [
            'labels' => array_values($dias),
            'series' => [],
            'ranking' => []
        ];

        foreach ($productos as $producto) {

            $nombre = $producto['nombre_item_pedido'];
            $cantidad = (int)$producto['cantidad'];

            if (!isset($resultado['series'][$nombre])) {

                $resultado['series'][$nombre] = [
                    'name' => $nombre,
                    'data' => array_fill(0, 7, 0)
                ];

                // Inicializar total para el ranking
                $resultado['ranking'][$nombre] = 0;
            }

            // Posición dentro del arreglo de días
            $indice = match ((int)$producto['dia']) {
                2 => 0,
                3 => 1,
                4 => 2,
                5 => 3,
                6 => 4,
                7 => 5,
                1 => 6,
            };

            $resultado['series'][$nombre]['data'][$indice] = $cantidad;

            // Acumular el total vendido del producto
            $resultado['ranking'][$nombre] += $cantidad;
        }

        // ApexCharts necesita un arreglo indexado
        $resultado['series'] = array_values($resultado['series']);

        // Convertir el ranking al formato que necesita el frontend
        $ranking = [];

        foreach ($resultado['ranking'] as $nombre => $cantidad) {
            $ranking[] = [
                'nombre' => $nombre,
                'cantidad' => $cantidad
            ];
        }

        // Ordenar por cantidad descendente
        usort($ranking, function ($a, $b) {
            return $b['cantidad'] <=> $a['cantidad'];
        });

        $resultado['ranking'] = $ranking;

        return $resultado;
    }
}