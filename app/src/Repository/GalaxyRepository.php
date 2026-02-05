<?php

namespace App\Repository;

use App\Entity\Galaxy;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Galaxy>
 */
class GalaxyRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Galaxy::class);
    }

    /**
     * Récupère toutes les galaxies avec leurs modèles et fichiers en une requête
     * @return array
     */
    public function findAllWithModelsAndFiles(): array
    {
        $conn = $this->getEntityManager()->getConnection();
        $sql = '
            SELECT 
                g.id as galaxy_id,
                g.title as galaxy_title,
                g.description as galaxy_description,
                g.sort as galaxy_sort,
                m.id as modele_id,
                mf.id as modeles_file_id,
                mf.modeles_id,
                mf.directus_files_id,
                df.id as file_id,
                df.filename_disk
            FROM galaxy g
            LEFT JOIN modeles m ON m.id = g.modele
            LEFT JOIN modeles_files mf ON mf.modeles_id = m.id
            LEFT JOIN directus_files df ON df.id = mf.directus_files_id
            WHERE g.status = :status
            ORDER BY g.sort ASC, mf.id ASC
        ';
        
        $stmt = $conn->prepare($sql);
        $result = $stmt->executeQuery(['status' => 'published']);
        
        return $result->fetchAllAssociative();
    }

    //    /**
    //     * @return Galaxy[] Returns an array of Galaxy objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('g')
    //            ->andWhere('g.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('g.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Galaxy
    //    {
    //        return $this->createQueryBuilder('g')
    //            ->andWhere('g.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
