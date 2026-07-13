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
use Mpdf\Mpdf;

class PedidoService
{
    private EntityManagerInterface $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    /**
     * Finaliza el pedido, crea el cliente, el pedido y los items del pedido
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

        $facturaPdf = $this->generateFacturaPdf($resp);

        $pedido->setRutaFactura($facturaPdf);
        $this->em->flush();

        $resp['ruta_factura'] = $facturaPdf;

        return $resp;
    }

    /**
     * Genera el PDF de la factura del pedido
     *
     * @return string
     * @author Luis Sanchez <betancurluis20@gmail.com> 2026-07-13
     */
    private function generateFacturaPdf(array $data): string
    {
        $mpdf = new Mpdf([
            'format' => 'A4',
            'orientation' => 'P', // P = Vertical, L = Horizontal
            'margin_left' => 10,
            'margin_right' => 10,
            'margin_top' => 15,
            'margin_bottom' => 15
        ]);
        $mpdf->showImageErrors = true;

        $html = $this->getHtmlFactura($data);


        $css = file_get_contents($_SERVER['DOCUMENT_ROOT'] . '/css/factura.css');

        $mpdf->WriteHTML($css, \Mpdf\HTMLParserMode::HEADER_CSS);
        $mpdf->WriteHTML($html, \Mpdf\HTMLParserMode::HTML_BODY);

        $local = 'almacenamiento/facturas/';
        $rutaPdf = $local . 'factura_' . date('Y-m-d') . '_' . $data['idpedido'] . '.pdf';

        $mpdf->Output($_SERVER['DOCUMENT_ROOT'] . '/' . $rutaPdf, 'F');
    
        return $rutaPdf;
    }

    /**
     * Retorna el html de la factura
     *
     * @param array $data
     * @return string
     * @author Luis Sanchez <betancurluis20@gmail.com> 2026-07-13
     */
    private function getHtmlFactura(array $data): string
    {
        $items = '';
        $total = 0;

        foreach ($data['items'] as $item) {

            $subtotal = $item['cantidad'] * $item['precio'];
            $total += $subtotal;

            $precio = number_format($item['precio'], 0, ',', '.');
            $subtotalFormat = number_format($subtotal, 0, ',', '.');
            $items .= <<<HTML
                <tr>
                    <td>{$item['cantidad']}</td>
                    <td>{$item['nombre']}</td>
                    <td>{$precio}</td>
                    <td>{$subtotalFormat}</td>
                </tr>
            HTML;
        }

        $totalFormat = number_format($total, 0, ',', '.');
        
        $html = <<<HTML
            <div class="div-principal-factura">
                <div class='div-secundario-factura'>

                    <div class='logo-factura' style="text-align:center;">
                        <img src='/assets/img/logo.jpg' class='logo-fac'>
                    </div>

                    <div class='info-basic-pedido'  style="text-align:center; font-size:16px;">
                        Pedido No. {$data['idpedido']}<br>
                        Fecha: {$data['fecha']}<br>
                        Teléfono: 3001234567<br>
                        Dirección: Calle 123 #45-67<br>
                    </div>

                    <div class='info-items-pedido'>
                        <table class='w-100'>
                            <tr>
                                <td>Cant</td>
                                <td>Descripción</td>
                                <td>Precio</td>
                                <td>Total</td>
                            </tr>

                            {$items}

                        </table>
                    </div>

                    <table width="100%" class='total-pedido'>
                        <tr>
                            <td><strong>Total:</strong></td>
                            <td align="right">$ {$totalFormat}</td>
                        </tr>
                    </table>

                    <div class='frase'>
                        Por favor dirigirse a la caja para realizar el pago.<br>
                        Gracias por su visita.
                    </div>

                </div>
            </div>
        HTML;

        return $html;
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