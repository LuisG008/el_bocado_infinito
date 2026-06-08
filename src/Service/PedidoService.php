<?php

namespace App\Service;

use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Pedido;
use App\Entity\HistorialEstado;
use App\Entity\ItemPedido;
use App\Service\ClienteService;
use App\Entity\Menu;
use App\Entity\Estado;
use DateTime;
use InvalidArgumentException;
use Symfony\Component\HttpFoundation\Response;

class PedidoService
{
    private EntityManagerInterface $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    /**
     * Generar un nuevo pedido
     *
     * @param array $data
     * @return array
     * @author Luis Sanchez <betancurluis20@gmail.com> 2026-05-15
     */
    public function finalizarPedido(array $data): array
    {
        $resp = [];

        $clienteService = new ClienteService($this->em);
        $cliente = $clienteService->create($data);
        $clienteId = $cliente->getId();

        $pedido = new Pedido();
        $pedido->setFkCliente($clienteId);
        $pedido->setFechaHora(new DateTime());
        $this->em->persist($pedido);
        $this->em->flush();
        $idpedido = $pedido->getId();

        $resp = [
            'idpedido' => $idpedido,
            'fecha' => $pedido->getFechaHora()->format('Y-m-d H:i:s'),
            'cliente' => [
                'id' => $cliente->getId(),
                'identificacion' => $cliente->getIdentificacion(),
                'nombres' => $cliente->getNombres(),
                'telefono' => $cliente->getTelefono()
            ]
        ];
        
        foreach ($data['pedido'] as $item) {
            $ItemPedido = new ItemPedido();
            $ItemPedido->setFkPedido($idpedido);
            $ItemPedido->setFkMenu($item['idmenu']);
            $ItemPedido->setCantidad($item['cantidad']);

            $menu = $this->em->getRepository(Menu::class)->findOneBy(['idmenu' => $item['idmenu']]);
            
            $ItemPedido->setNombre($menu->getNombre());
            $ItemPedido->setDescripcion($menu->getDescripcion());
            $tipo = $item['tipo_consumo'] == 1 ? Menu::TIPO_CONSUMO_MESA : Menu::TIPO_CONSUMO_LLEVAR;
            $ItemPedido->setTipoConsumo($tipo);
            $ItemPedido->setRutaImagen($menu->getRutaImagen());

            $precio = $menu->getPrecio() * $item['cantidad'];
            $ItemPedido->setPrecio($precio);

            $this->em->persist($ItemPedido);
            
            $resp['items'][] = [
                'idmenu'   => $item['idmenu'],
                'nombre'   => $menu->getNombre(),
                'cantidad' => $item['cantidad'],
                'precio'   => $menu->getPrecio(),
                'subtotal' => $precio
            ];
        }
        
        $historialEstadoService = new HistorialEstadoService($this->em);
        $historialEstadoService->registrarEstado([
            'idpedido' => $idpedido,
            'idestado' => Estado::ESTADO_PENDIENTE_PAGO,
            'idrol' => 0
        ]);

        $this->em->persist($pedido);
        $this->em->flush();

        return $resp;
    }

    /**
     * Cancela el pedido solo cuando esta en pediente de pago, de lo contrario no se puede cancelar
     *
     * @param int $idpedido
     * @param int $idcliente
     * @return void
     * @author Luis Sanchez <betancurluis20@gmail.com> 2026-05-21
     */
    public function cancelarPedido(int $idpedido, int $idcliente): void
    {
        $pedido = $this->em->getRepository(Pedido::class)->findOneBy([
            'idpedido' => $idpedido,
            'fk_cliente' => $idcliente
        ]);
        if (!$pedido) {
            throw new InvalidArgumentException("Pedido no encontrado", Response::HTTP_BAD_REQUEST);
        }

        $HistorialEstado = $this->em->getRepository(HistorialEstado::class)->findOneBy([
            'fk_pedido' => $idpedido,
            'fk_estado' => Estado::ESTADO_PENDIENTE_PAGO
        ]);

        if(!$HistorialEstado){
            throw new InvalidArgumentException("Solo se puede cancelar pedidos en estado pendiente de pago", Response::HTTP_BAD_REQUEST);
        }

        $historialEstadoService = new HistorialEstadoService($this->em);
        $historialEstadoService->registrarEstado([
            'idpedido' => $idpedido,
            'idestado' => Estado::ESTADO_CANCELADO,
            'idrol' => 0
        ]);
    }

}