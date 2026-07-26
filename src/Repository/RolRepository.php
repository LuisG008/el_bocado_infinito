<?php

namespace App\Repository;

use App\Entity\Rol;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Rol>
 */
class RolRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Rol::class);
    }

    //    /**
    //     * @return Rol[] Returns an array of Rol objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('r')
    //            ->andWhere('r.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('r.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Rol
    //    {
    //        return $this->createQueryBuilder('r')
    //            ->andWhere('r.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }

    public function obtenerRolesUsuario(int $idUsuario): array
    {
        $conn = $this->getEntityManager()->getConnection();

        $sql = "
            SELECT CONCAT('ROLE_', UPPER(c.nombre)) AS rol
            FROM rol r
            INNER JOIN cargo c ON c.idcargo = r.fk_cargo
            WHERE r.fk_usuario = :id
            AND r.estado = 'Activo'
            AND c.estado = 'Activo'
        ";

        return $conn->fetchFirstColumn($sql, [
            'id' => $idUsuario
        ]);
    }
}
