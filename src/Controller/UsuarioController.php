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
     * @author Luis Sanchez <luis.sanchez@cerok.com> 2026-03-14
     */
    #[Route('', name: 'get_usuario', methods: ['GET'])]
    public function usuario(): Response
    {
        //$Usuario = $this->em->getRepository(Usuario::class)->findAll();
        //$Usuario = $this->em->getRepository(Usuario::class);

        try {
            /*$data = $Usuario->createQueryBuilder('u')
                ->select('u')
                //->from(Usuario::class, 'u')
                ->where("u.estado = 'Activo'")
                //->setParameter('estado', )
                ->getQuery()->getResult();*/

            /*$Usuario = $this->em->getRepository(Usuario::class)->findAll();

            $data = [
                'data' => $Usuario
            ];*/

            $Connection = $this->em->getConnection();

            $sql = "SELECT * FROM vrol where estado_rol = 'Activo' order by idusuario desc";

            $data = $Connection->executeQuery($sql)->fetchAllAssociative();
            
            return $this->json(['data' => $data]);

        } catch (\Throwable $th) {
            return $this->json($th->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Crea un nuevo usuario y su rol asociado
     *
     * @param Request $request
     * @return Response
     * @author Luis Sanchez <luis.sanchez@cerok.com> 2026-03-14
     */
    #[Route('/create', name: 'create_usuario', methods: ['POST'])]
    public function create(Request $request): Response
    {
        $Connection = $this->em->getConnection();
        $Connection->beginTransaction();

        try {
            $data = $request->request->all();

            if($data['nombres'] == '' || $data['identificacion'] == '' || $data['telefono'] == '' || $data['clave'] == '' || $data['idcargo'] == ''){
                throw new InvalidArgumentException("Todos los campos son obligatorios", Response::HTTP_BAD_REQUEST);
            }

            $usuario = new Usuario();
            $rol = new Rol();

            $usuario->setIdentificacion($data['identificacion']);
            $usuario->setNombres($data['nombres']);
            $usuario->setTelefono($data['telefono']);
            $usuario->setClave(password_hash($data['clave'], PASSWORD_BCRYPT));
            $usuario->setEstado($data['estado']);
            
            // usar este en el login
            // if (!password_verify($data['clave'], $usuario->getClave())) {
            //     throw new BadRequestHttpException("Clave incorrecta");
            // }

            $this->em->persist($usuario);
            $this->em->flush();
            
            $rol->setFkUsuario($usuario->getId());
            $rol->setFkCargo($data['idcargo']);
            $rol->setEstado($data['estado']);

            $this->em->persist($rol);
            $this->em->flush();

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
    public function edit(Request $request, int $id): Response
    {
        $Connection = $this->em->getConnection();
        $Connection->beginTransaction();

        try {
            $data = $request->request->all();

            if($data['nombres'] == '' || $data['identificacion'] == '' || $data['telefono'] == '' || $data['clave'] == '' || $data['idcargo'] == ''){
                throw new InvalidArgumentException("Todos los campos son obligatorios", Response::HTTP_BAD_REQUEST);
            }
  
            $usuario = $this->em->getRepository(Usuario::class)->find($id);
            if (!$usuario) {
                return $this->json(
                    ['error' => 'Usuario no encontrado'],
                    Response::HTTP_NOT_FOUND
                );
            }
            
            $usuario->setIdentificacion($data['identificacion']);
            $usuario->setNombres($data['nombres']);
            $usuario->setTelefono($data['telefono']);
            $usuario->setClave($data['clave']);

            $sql = "
                SELECT idrol,idcargo
                FROM vrol
                WHERE idusuario = :id
                AND estado_rol = 'Activo'
            ";

            $vrol = $Connection->executeQuery($sql, [
                'id' => $id,
                'idcargo' => $data['idcargo']
            ])->fetchAssociative();

            $crear = true;
            if ($vrol) {
                if( $vrol['idcargo'] != $data['idcargo']){
                    $rolAnterior = $this->em->getRepository(Rol::class)->find($vrol['idrol']);
                    $rolAnterior->setEstado('Inactivo');
                }else{
                    $crear = false;
                }
            }

            if($crear){
                $nuevoRol = new Rol();
                $nuevoRol->setFkUsuario($id);
                $nuevoRol->setFkCargo($data['idcargo']);
                $nuevoRol->setEstado('Activo');
                $this->em->persist($nuevoRol);
            }

            $this->em->flush();

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
     * @author Luis Sanchez <luis.sanchez@cerok.com> 2026-03-14
     */
    #[Route('/{id}', name: 'activar_inactivar_usuario', methods: ['PUT'])]
    public function activarInactivar(Request $request, int $id): Response
    {
        $Connection = $this->em->getConnection();
        $Connection->beginTransaction();

        try {
            $data = $request->request->all();

            if(!$id){
                throw new InvalidArgumentException("El ID del usuario es obligatorio", Response::HTTP_BAD_REQUEST);
            }

            $usuario = $this->em->getRepository(Usuario::class)->find($id);
            if (!$usuario) {
                throw new InvalidArgumentException("Usuario no encontrado", Response::HTTP_BAD_REQUEST);
            }
            
            $estado = $data['accion'] == 'inactivar' ? 'Inactivo' : 'Activo';
            $usuario->setEstado($estado);
            $this->em->flush();
            
            $Connection->commit();
            return $this->json([
                'message' => 'Usuario ' . $estado
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
     * @author Luis Sanchez <luis.sanchez@cerok.com> 2026-03-17
     */
    #[Route('/buscar', name: 'buscador_usuario', methods: ['GET'])]
    public function buscar(Request $request): Response
    {
        $Connection = $this->em->getConnection();
        //$Connection->beginTransaction();

        try {
            $texto = $request->query->get('texto', '');

            trim($texto);

            $sql = "
                SELECT * 
                FROM vrol
                WHERE estado_rol = 'Activo'
                AND (nombres like :texto OR identificacion like :texto)
                order by idusuario desc
            ";

            $data = $Connection->executeQuery($sql, [
                'texto' => "%{$texto}%"
            ])->fetchAllAssociative();

            
            $data = $data ?: [];
            
            //$Connection->commit();
            return $this->json([
                'data' => $data
            ]);

        } catch (\Throwable $th) {
            //$Connection->rollBack();
            
            return $this->json([
                'message' => $th->getMessage()
                ], $th->getCode() ?: Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }
}
