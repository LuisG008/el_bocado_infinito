<?php

namespace App\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Request;
use InvalidArgumentException;

use App\Entity\Estado;
use App\Service\VPedidoService;

#[Route('/api/pedido')]
final class PedidoController extends AbstractController
{
    private $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    /**
     * Retorna todos los pedidos 
     *
     * @return Response
     * @author Luis Sanchez <betancurluis20@gmail.com> 2026-06-08
     */
    #[Route('', name: 'get_pedido', methods: ['GET'])]
    public function pedido(VPedidoService $VPedidoService): Response
    {
        try {
            $data = $VPedidoService->allPedidos();

            $listEstado = [];
            $Estados = $this->em->getRepository(Estado::class)->findBy([
                'estado_nombre' => 'Activo'
            ]);
            foreach ($Estados as $estado) {
                $listEstado[$estado->getNombre()] = [
                    'color' => $estado->getColor()
                ];
            }

            return $this->json([
                'data' => $data,
                'estados' => $listEstado
            ]);
        } catch (\Throwable $th) {
            return $this->json([
                'message' => $th->getMessage()
                ], $th->getCode() ?: Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }

    /**
     * Retorna todos los pedidos para entregar
     *
     * @return Response
     * @author Luis Sanchez <betancurluis20@gmail.com> 2026-06-08
     */
    #[Route('/entrega', name: 'get_pedido_entrega', methods: ['GET'])]
    public function entrega(VPedidoService $VPedidoService): Response
    {
        try {
            $data = $VPedidoService->allPedidosEntrega();

            $listEstado = [];
            $Estados = $this->em->getRepository(Estado::class)->findBy([
                'estado_nombre' => 'Activo'
            ]);
            foreach ($Estados as $estado) {
                $listEstado[$estado->getNombre()] = [
                    'color' => $estado->getColor()
                ];
            }

            return $this->json([
                'data' => $data,
                'estados' => $listEstado
            ]);
        } catch (\Throwable $th) {
            return $this->json([
                'message' => $th->getMessage()
                ], $th->getCode() ?: Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }

    /**
     * Retorna todos los pedidos para cobrar en caja
     *
     * @return Response
     * @author Luis Sanchez <betancurluis20@gmail.com> 2026-06-08
     */
    #[Route('/caja', name: 'get_pedido_caja', methods: ['GET'])]
    public function caja(VPedidoService $VPedidoService): Response
    {
        try {
            $data = $VPedidoService->allPedidosCaja();

            $listEstado = [];
            $Estados = $this->em->getRepository(Estado::class)->findBy([
                'estado_nombre' => 'Activo'
            ]);
            foreach ($Estados as $estado) {
                $listEstado[$estado->getNombre()] = [
                    'color' => $estado->getColor()
                ];
            }

            return $this->json([
                'data' => $data,
                'estados' => $listEstado
            ]);
        } catch (\Throwable $th) {
            return $this->json([
                'message' => $th->getMessage()
                ], $th->getCode() ?: Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }

    /**
     * Actualiza el estado de un pedido
     *
     * @param Request $request
     * @param VPedidoService $VPedidoService
     * @return Response
     * @author Luis Sanchez <betancurluis20@gmail.com> 2026-06-20
     */
    #[Route('/updateEstado', name: 'updateEstado', methods: ['POST'])]
    public function updateEstado(
        Request $request,
        VPedidoService $VPedidoService
    ): Response {
        $Connection = $this->em->getConnection();
        $Connection->beginTransaction();

        try {
            $data = $request->request->all();
            // $usuario = $this->getUser(); Para obtener el usuario autenticado
            
            if($data['id'] == '' || $data['estado'] == ''){
                throw new InvalidArgumentException("Faltan datos requeridos", Response::HTTP_BAD_REQUEST);
            }

            $VPedidoService->updateEstado($data['id'], $data['estado']);
            
            $Connection->commit();
            return $this->json([
                'message' => 'Estado actualizado'
            ]);

        } catch (\Throwable $th) {
            $Connection->rollBack();
            
            return $this->json([
                'message' => $th->getMessage()
                ], $th->getCode() ?: Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }
}