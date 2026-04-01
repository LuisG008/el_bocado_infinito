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

use App\Service\VRolService;
use App\Service\UsuarioService;

#[Route('/api/usuario')]
final class UsuarioController extends AbstractController
{
    private $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    /**
     * Retorna todos los usuarios 
     *
     * @return Response
     * @author Luis Sanchez <betancurluis20@gmail.com> 2026-03-14
     */
    #[Route('', name: 'get_usuario', methods: ['GET'])]
    public function usuario(VRolService $vRolService): Response
    {
        try {
            // $user = $this->getUser();
            // dd($user);
            $data = $vRolService->allUsers();

            return $this->json(['data' => $data]);
        } catch (\Throwable $th) {
            return $this->json([
                'message' => $th->getMessage()
                ], $th->getCode() ?: Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }

    /**
     * Crea un nuevo usuario y su rol asociado
     *
     * @param Request $request
     * @return Response
     * @author Luis Sanchez <betancurluis20@gmail.com> 2026-03-14
     */
    #[Route('/create', name: 'create_usuario', methods: ['POST'])]
    public function create(
        Request $request,
        UsuarioService $UsuarioService
    ): Response {
        $Connection = $this->em->getConnection();
        $Connection->beginTransaction();

        try {
            $data = $request->request->all();

            if($data['nombres'] == '' || $data['identificacion'] == '' || $data['telefono'] == '' || $data['clave'] == '' || $data['idcargo'] == ''){
                throw new InvalidArgumentException("Todos los campos son obligatorios", Response::HTTP_BAD_REQUEST);
            }

            $UsuarioService->create($data);

            $Connection->commit();
            return $this->json([
                'message' => 'Usuario creado'
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
     * Edita un usuario y su rol asociado
     */
    #[Route('/edit/{id}', name: 'edit_usuario', methods: ['PUT'])]
    public function edit(
        Request $request, 
        int $id,
        UsuarioService $UsuarioService,
        VRolService $VRolService
    ): Response {
        $Connection = $this->em->getConnection();
        $Connection->beginTransaction();

        try {
            $data = $request->request->all();

            if(!$id){
                throw new InvalidArgumentException("El ID del usuario es obligatorio", Response::HTTP_BAD_REQUEST);
            }

            if($data['nombres'] == '' || $data['identificacion'] == '' || $data['telefono'] == '' || $data['clave'] == '' || $data['idcargo'] == ''){
                throw new InvalidArgumentException("Todos los campos son obligatorios", Response::HTTP_BAD_REQUEST);
            }
  
            $UsuarioService->edit($id, $data, $VRolService);

            $Connection->commit();

            return $this->json([
                'message' => 'Usuario editado'
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
     * Activa o inactiva un usuario
     *
     * @param Request $request
     * @return Response
     * @author Luis Sanchez <betancurluis20@gmail.com> 2026-03-14
     */
    #[Route('/{id}', name: 'activar_inactivar_usuario', methods: ['PUT'])]
    public function activarInactivar(
        Request $request, 
        int $id,
        UsuarioService $UsuarioService,
    ): Response {
        $Connection = $this->em->getConnection();
        $Connection->beginTransaction();

        try {
            $data = $request->request->all();

            if(!$id){
                throw new InvalidArgumentException("El ID del usuario es obligatorio", Response::HTTP_BAD_REQUEST);
            }         
            
            $usuario = $UsuarioService->activarInactivar($id, $data['accion']);
            
            $Connection->commit();
            return $this->json([
                'message' => 'Usuario ' . $usuario->getEstado()
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
     * Busca un usuario por su nombre o identificación
     *
     * @param Request $request
     * @return Response
     * @author Luis Sanchez <betancurluis20@gmail.com> 2026-03-17
     */
    #[Route('/buscar', name: 'buscador_usuario', methods: ['GET'])]
    public function buscar(
        Request $request,
        VRolService $VRolService
    ): Response {

        try {
            $texto = $request->query->get('texto', '');

            trim($texto);

            $data = $VRolService->findByRoleIdentification($texto);

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
