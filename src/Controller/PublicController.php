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
     * @param SessionInterface $session
     * @return Response
     * @author Luis Sanchez <betancurluis20@gmail.com> 2026-05-15
     */
    #[Route('/finalizarPedido', name: 'finalizarPedido', methods: ['POST'])]
    public function finalizarPedido(
        Request $request,
        SessionInterface $session
    ): Response {
        $Connection = $this->em->getConnection();
        $Connection->beginTransaction();

        try {
            $data = $request->request->all();
            
            if($data['identificacion'] == '' || !$data['pedido'] || $data['nombre'] == '' || $data['telefono'] == ''){
                throw new InvalidArgumentException("Todos los campos son obligatorios", Response::HTTP_BAD_REQUEST);
            }
            

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
}