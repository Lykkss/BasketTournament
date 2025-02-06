<?php

namespace App\Repository;

use App\Entity\Game;
use App\Entity\Tournoi;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;


/**
 * @extends ServiceEntityRepository<Game>
 */
class GameRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Game::class);
    }

//    /**
//     * @return Game[] Returns an array of Game objects
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

//    public function findOneBySomeField($value): ?Game
//    {
//        return $this->createQueryBuilder('g')
//            ->andWhere('g.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }

/**
     * Récupère les gagnants d'un tournoi donné
     */
    public function getWinnersByTournoi(Tournoi $tournoi): array
    {
        return $this->createQueryBuilder('g')
            ->select('g.vainqueur')
            ->where('g.tournoi = :tournoi')
            ->andWhere('g.vainqueur IS NOT NULL') // S'assurer que le match a un vainqueur
            ->setParameter('tournoi', $tournoi)
            ->getQuery()
            ->getResult();
    }
}
