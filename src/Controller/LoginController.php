<?php

namespace App\Controller;

use App\Entity\Usuario;
use App\Entity\Rol;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Request;
use InvalidArgumentException;

use App\Service\UsuarioService;

#[Route('/api/login')]
final class LoginController extends AbstractController
{
    private $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    /**
     * Valida las credenciales del usuario
     * 
     * @param Request $request
     * @return Response
     * @author Luis Sanchez <betancurluis20@gmail.com> 2026-03-27
     */
    #[Route('', name: 'validate', methods: ['GET'])]
    public function login(
        Request $request,
        UsuarioService $UsuarioService
    ): Response {
        try {
            $identificacion = $request->query->get('identificacion', '');
            $clave = $request->query->get('clave', '');

            $Usuario = $UsuarioService->validLogin($identificacion, $clave);

            $data = [];

            
            return $this->json(['data' => $data]);

        } catch (\Throwable $th) {
            return $this->json([
                'message' => $th->getMessage()
                ], $th->getCode() ?: Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }
}
