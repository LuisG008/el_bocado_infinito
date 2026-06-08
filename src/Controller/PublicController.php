<?php

namespace App\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Request;
use InvalidArgumentException;
use App\Entity\Menu;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use App\Service\MenuService;
use App\Service\PedidoService;
use App\Service\VPedidoService;
use App\Entity\Cliente;

#[Route('/api/public')]
final class PublicController extends AbstractController
{
    private $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    /**
     * Retorna todos los menus disponibles
     *
     * @param Request $request
     * @return Response
     * @author Luis Sanchez <betancurluis20@gmail.com> 2026-04-26
     */
    #[Route('/menu/allAvailable', name: 'allAvailable', methods: ['GET'])]
    public function allAvailable(Request $request): Response
    {
        try {
            $data = $this->em->getRepository(Menu::class)->findByAllAvailable();
            
            $data = $data ?: [];
            
            return $this->json([
                'data' => $data
            ]);

        } catch (\Throwable $th) {            
            return $this->json([
                'message' => $th->getMessage()
                ], $th->getCode() ?: Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }

    /**
     * Guarda en cooki el pedido
     *
     * @param Request $request
     * @param SessionInterface $session
     * @return Response
     * @author Luis Sanchez <betancurluis20@gmail.com> 2026-04-27
     */
    #[Route('/prepedido', name: 'set_prepedido', methods: ['POST'])]
    public function setPrepedido(
        Request $request,
        SessionInterface $session
    ): Response {
        $Connection = $this->em->getConnection();
        $Connection->beginTransaction();

        try {
            $data = $request->request->all();

            if($data['pedido'] == ''){
                throw new InvalidArgumentException("Ocurrio un error, No hay pedido", Response::HTTP_BAD_REQUEST);
            }

            $session->set('pedidos', $data['pedido']);

            $Connection->commit();
            return $this->json([
                'message' => 'Ok'
            ]);

        } catch (\Throwable $th) {
            $Connection->rollBack();
            
            return $this->json([
                'message' => $th->getMessage()
                ], $th->getCode() ?: Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }

    /**
     * Retorna el prepedido para mostrarlo en la vista de terminar pedido
     *
     * @param SessionInterface $session
     * @param MenuService $menuService
     * @return Response
     * @author Luis Sanchez <betancurluis20@gmail.com> 2026-05-15
     */
    #[Route('/prepedido', name: 'get_prepedido', methods: ['GET'])]
    public function getPrepedido(
        SessionInterface $session,
        MenuService $menuService
    ): Response {
        try {
            $pedidos = $session->get('pedidos', []);
            $data = $menuService->prePedidos($pedidos);

            $response = [];
            foreach ($data as $value) {
                $response[] = [
                    'idmenu' => $value->getId(),
                    'nombre' => $value->getNombre(),
                    'precio' => $value->getPrecio()
                ];
            }

            return $this->json([
                'data' => $response
            ]);

        } catch (\Throwable $th) {            
            return $this->json([
                'message' => $th->getMessage()
                ], $th->getCode() ?: Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }

    /**
     * Finaliza el pedido
     *
     * @param Request $request
     * @param PedidoService $PedidoService
     * @return Response
     * @author Luis Sanchez <betancurluis20@gmail.com> 2026-05-15
     */
    #[Route('/finalizarPedido', name: 'finalizarPedido', methods: ['POST'])]
    public function finalizarPedido(
        Request $request,
        PedidoService $PedidoService,
        SessionInterface $session
    ): Response {
        $Connection = $this->em->getConnection();
        $Connection->beginTransaction();

        try {
            $data = $request->request->all();
            
            if($data['identificacion'] == '' || !$data['pedido'] || $data['nombre'] == '' || $data['telefono'] == ''){
                throw new InvalidArgumentException("Todos los campos son obligatorios", Response::HTTP_BAD_REQUEST);
            }

            $Pedido = $PedidoService->finalizarPedido($data);
            
            $session->set('pedido_generado', $Pedido);
            $session->set('idcliente', $Pedido['cliente']['id']);

            $response = [
                'idpedido' => $Pedido['idpedido'],
            ];

            $Connection->commit();
            return $this->json([
                'data' => $response
            ]);

        } catch (\Throwable $th) {
            $Connection->rollBack();
            
            return $this->json([
                'message' => $th->getMessage()
                ],  Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }

    /**
     * Retorna el pedido generado para mostrarlo en la vista de factura
     *
     * @param SessionInterface $session
     * @return Response
     * @author Luis Sanchez <betancurluis20@gmail.com> 2026-05-18
     */
    #[Route('/pedidoGenerado', name: 'get_pedido_generado', methods: ['GET'])]
    public function getPedidoGenerado(
        SessionInterface $session,
    ): Response {
        try {
            $data = $session->get('pedido_generado', []);

            return $this->json([
                'data' => $data
            ]);

        } catch (\Throwable $th) {            
            return $this->json([
                'message' => $th->getMessage()
                ], $th->getCode() ?: Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }

    /**
     * Retorna todos los pedidos generados en el dia actual
     *
     * @param SessionInterface $session
     * @return Response
     * @author Luis Sanchez <luis.sanchez@cerok.com> 2026-05-18
     */
    #[Route('/estadosPedidosGenerados', name: 'get_estados_pedidos_generados', methods: ['GET'])]
    public function getEstadosPedidosGenerados(
        Request $request,
        SessionInterface $session,
        VPedidoService $VPedidoService
    ): Response {
        try {
            $identificacion = $request->query->get('identificacion', '');
            $idcliente = $session->get('idcliente');

            if($identificacion){
                $usuarioExistente = $this->em->getRepository(Cliente::class)->findOneBy([
                    'identificacion' => $identificacion
                ]);
                if($usuarioExistente){
                    $idcliente = $usuarioExistente->getId();
                    $session->set('idcliente', $idcliente);
                } else {
                    throw new InvalidArgumentException("No se encontró un usuario con la identificación proporcionada", Response::HTTP_BAD_REQUEST);
                }
            }

            if(!$idcliente){
                throw new InvalidArgumentException("Falta el identificador cliente", Response::HTTP_BAD_REQUEST);
            }

            $estadosPedidos = $VPedidoService->pedidosActuales($idcliente);
            $historial = $VPedidoService->historialPedidos($estadosPedidos);

            return $this->json([
                'data' => $estadosPedidos,
                'historial' => $historial
            ]);

        } catch (\Throwable $th) {            
            return $this->json([
                'message' => $th->getMessage()
                ], $th->getCode() ?: Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }

    #[Route('/cancelarPedido', name: 'cancelarPedido', methods: ['POST'])]
    public function cancelarPedido(
        Request $request,
        PedidoService $PedidoService,
        SessionInterface $session
    ): Response {
        $Connection = $this->em->getConnection();
        $Connection->beginTransaction();

        try {
            $data = $request->request->all();
            
            if($data['idpedido'] == ''){
                throw new InvalidArgumentException("El ID del pedido es obligatorio", Response::HTTP_BAD_REQUEST);
            }

            $idcliente = $session->get('idcliente');
            if(!$idcliente){
                throw new InvalidArgumentException("Falta el identificador cliente", Response::HTTP_BAD_REQUEST);
            }

            $PedidoService->cancelarPedido($data['idpedido'], $idcliente);
            

            $Connection->commit();
            return $this->json([
                'message' => 'Pedido cancelado exitosamente'
            ]);

        } catch (\Throwable $th) {
            $Connection->rollBack();
            
            return $this->json([
                'message' => $th->getMessage()
                ],  Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }
}