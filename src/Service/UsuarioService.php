<?php

namespace App\Service;

use Doctrine\ORM\EntityManagerInterface;
use InvalidArgumentException;
use Symfony\Component\HttpFoundation\Response;

use App\Entity\Rol;
use App\Entity\Usuario;
use App\Service\VRolService;

class UsuarioService
{
    const ESTADO_ACTIVO = 'Activo';
    const ESTADO_INACTIVO = 'Inactivo';

    private EntityManagerInterface $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    /**
     * Crea un nuevo usuario
     *
     * @param array $data
     * @return Usuario
     * @author Luis Sanchez <betancurluis20@gmail.com> 2026-03-28
     */
    public function create(array $data): Usuario
    {
        $usuarioExistente = $this->em->getRepository(Usuario::class)->findByFilters([
            'identificacion' => $data['identificacion']
        ]);
        
        if ($usuarioExistente) {
            throw new InvalidArgumentException("El usuario con la identificación proporcionada ya existe", Response::HTTP_BAD_REQUEST);
        }

        $usuario = new Usuario();
        $rol = new Rol();

        $usuario->setIdentificacion($data['identificacion']);
        $usuario->setNombres($data['nombres']);
        $usuario->setTelefono($data['telefono']);
        $usuario->setClave(password_hash($data['clave'], PASSWORD_BCRYPT));
        $usuario->setEstado($data['estado']);
        
        $this->em->persist($usuario);
        $this->em->flush();
        
        $rol->setFkUsuario($usuario->getId());
        $rol->setFkCargo($data['idcargo']);
        $rol->setEstado($data['estado']);

        $this->em->persist($rol);
        $this->em->flush();

        return $usuario;
    }

    /**
     * Edita un usuario
     *
     * @param int $id
     * @param array $data
     * @param VRolService $VRolService
     * @return Usuario
     * @author Luis Sanchez <betancurluis20@gmail.com> 2026-03-28
     */
    public function edit(int $id, array $data, VRolService $VRolService): Usuario
    {
        $usuario = $this->em->getRepository(Usuario::class)->find($id);

        if (!$usuario) {
            throw new InvalidArgumentException("Usuario no encontrado", Response::HTTP_NOT_FOUND);
        }

        $usuario->setIdentificacion($data['identificacion']);
        $usuario->setNombres($data['nombres']);
        $usuario->setTelefono($data['telefono']);
        
        if($data['clave'] != $usuario->getClave()){
            $usuario->setClave(password_hash($data['clave'], PASSWORD_BCRYPT));
        }

        $vrol = $VRolService->findByIdUsuario($id);

        $crear = true;
        if ($vrol) {
            if( $vrol['idcargo'] != $data['idcargo']){
                $rolAnterior = $this->em->getRepository(Rol::class)->find($vrol['idrol']);
                $rolAnterior->setEstado(self::ESTADO_INACTIVO);
            }else{
                $crear = false;
            }
        }

        if($crear){
            $nuevoRol = new Rol();
            $nuevoRol->setFkUsuario($id);
            $nuevoRol->setFkCargo($data['idcargo']);
            $nuevoRol->setEstado(self::ESTADO_ACTIVO);
            $this->em->persist($nuevoRol);
        }

        $this->em->flush();

        return $usuario;
    }

    /**
     * Activa o inactiva un usuario
     *
     * @param int $id
     * @param string $accion
     * @return Usuario
     * @author Luis Sanchez <betancurluis20@gmail.com> 2026-03-28
     */
    public function activarInactivar(int $id, string $accion): Usuario
    {
        $usuario = $this->em->getRepository(Usuario::class)->find($id);
        if (!$usuario) {
            throw new InvalidArgumentException("Usuario no encontrado", Response::HTTP_BAD_REQUEST);
        }

        $estado = $accion == 'inactivar' ? self::ESTADO_INACTIVO : self::ESTADO_ACTIVO;

        $usuario->setEstado($estado);
        $this->em->flush();

        return $usuario;
    }
}