<?php

namespace App\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Request;
use InvalidArgumentException;

use App\Service\ClienteService;

#[Route('/api/cliente')]
final class ClienteController extends AbstractController
{
    private $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    /**
     * Retorna todos los clientes 
     *
     * @return Response
     * @author Luis Sanchez <betancurluis20@gmail.com> 2026-03-14
     */
    #[Route('', name: 'get_clientes', methods: ['GET'])]
    public function clientes(ClienteService $clienteService): Response
    {
        try {
            $data = $clienteService->allClientes();

            return $this->json(['data' => $data]);
        } catch (\Throwable $th) {
            return $this->json([
                'message' => $th->getMessage()
                ], $th->getCode() ?: Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }

    /**
     * Busca un cliente por su nombre o identificación
     *
     * @param Request $request
     * @return Response
     * @author Luis Sanchez <betancurluis20@gmail.com> 2026-03-17
     */
    #[Route('/buscar', name: 'buscador_cliente', methods: ['GET'])]
    public function buscar(
        Request $request,
        ClienteService $clienteService
    ): Response {

        try {
            $texto = $request->query->get('texto', '');

            trim($texto);

            $data = $clienteService->findByNombreIdentificacion($texto);

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
}