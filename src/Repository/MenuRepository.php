<?php

namespace App\Repository;

use App\Entity\Menu;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Menu>
 */
class MenuRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Menu::class);
    }

    /**
     * Retorna un array de menús que coincidan con el texto de búsqueda en su nombre, descripción o id
     * @param string $value El texto a buscar
    * @return Menu[] Returns an array of Menu objects
    */
    public function findByMenuText($value): array
    {
        return $this->createQueryBuilder('m')
            ->andWhere('m.nombre LIKE :val OR m.descripcion LIKE :val OR m.idmenu LIKE :val')
            ->setParameter('val', "%{$value}%")
            ->orderBy('m.idmenu', 'ASC')
            //->setMaxResults(10)
            ->getQuery()
            ->getResult();
    }

    //    public function findOneBySomeField($value): ?Menu
    //    {
    //        return $this->createQueryBuilder('m')
    //            ->andWhere('m.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
