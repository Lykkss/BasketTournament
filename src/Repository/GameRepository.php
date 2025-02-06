<?php

namespace App\Repository;

use App\Entity\Game;
use App\Entity\Tournoi;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;


/**
 * @extends ServiceEntityRepository<Game>
 *
 * @method Game|null find($id, $lockMode = null, $lockVersion = null)
 * @method Game|null findOneBy(array $criteria, array $orderBy = null)
 * @method Game[]    findAll()
 * @method Game[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
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
     * Méthode pour obtenir les gagnants par tournoi
     * @param Tournoi $tournoi
     * @return array
     */
    public function getWinnersByTournoi(Tournoi $tournoi): array
    {  return $this->createQueryBuilder('g')
            ->select('v.id')  // ✅ Sélectionne uniquement l'ID du vainqueur
            ->join('g.vainqueur', 'v')
            ->where('g.tournoi = :tournoi')
            ->andWhere('g.vainqueur IS NOT NULL')
            ->setParameter('tournoi', $tournoi)
            ->getQuery()
            ->getSingleColumnResult();  // ✅ Renvoie une liste d'IDs
    }

}