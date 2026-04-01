<?php

namespace App\Service;

use Doctrine\ORM\EntityManagerInterface;

class VRolService
{
    const ESTADO_ACTIVO = 'Activo';
    const ESTADO_INACTIVO = 'Inactivo';

    private EntityManagerInterface $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    /**
     * Retorna todos los usuarios con un rol activo
     *
     * @return array
     * @author Luis Sanchez <betancurluis20@gmail.com> 2026-03-28
     */
    public function allUsers(): array
    {
        $qb =  $this->em->getConnection()->createQueryBuilder();

        $qb->select('*')
            ->from('vrol', 'v')
            ->where("estado_rol = :estado")
            ->setParameter('estado', self::ESTADO_ACTIVO)
            ->orderBy('idusuario', 'DESC');

        return $qb->executeQuery()->fetchAllAssociative();
    }

    /**
     * Retorna el rol de un usuario por su idusuario, solo si el rol está activo
     *
     * @param int $id
     * @return array|null
     * @author Luis Sanchez <betancurluis20@gmail.com> 2026-03-28
     */
    public function findByIdUsuario(int $id): ?array
    {
        $qb =  $this->em->getConnection()->createQueryBuilder();

        $qb->select('*')
            ->from('vrol', 'v')
            ->where("idusuario = :id")
            ->andWhere("estado_rol = :estado")
            ->setParameter('id', $id)
            ->setParameter('estado', self::ESTADO_ACTIVO);

        return $qb->executeQuery()->fetchAssociative();
    }

    /**
     * Retorna el rol de un usuario por su idrol, solo si el rol está activo
     *
     * @param int $id
     * @return array|null
     * @author Luis Sanchez <betancurluis20@gmail.com> 2026-03-28
     */
    public function findByIdRol(int $id): ?array
    {
        $qb =  $this->em->getConnection()->createQueryBuilder();

        $qb->select('*')
            ->from('vrol', 'v')
            ->where("idrol = :id")
            ->andWhere("estado_rol = :estado")
            ->setParameter('id', $id)
            ->setParameter('estado', self::ESTADO_ACTIVO);

        return $qb->executeQuery()->fetchAssociative();
    }

    /**
     * Busca un usuario por su nombre o identificación, solo si el rol está activo
     *
     * @return array
     * @author Luis Sanchez <betancurluis20@gmail.com> 2026-03-28
     */
    public function findByRoleIdentification(string $texto): array
    {
        $qb =  $this->em->getConnection()->createQueryBuilder();

        $qb->select('*')
            ->from('vrol', 'v')
            ->where("estado_rol = :estado")
            ->andWhere($qb->expr()->or(
                $qb->expr()->like('nombres', ':texto'),
                $qb->expr()->like('identificacion', ':texto')
            ))
            ->setParameter('estado', self::ESTADO_ACTIVO)
            ->setParameter('texto', "%{$texto}%")
            ->orderBy('idusuario', 'DESC');

        return $qb->executeQuery()->fetchAllAssociative();
    }
}