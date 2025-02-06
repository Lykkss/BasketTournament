<?php

namespace App\Repository;

use App\Entity\Game;
use App\Entity\Tournoi;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class GameRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Game::class);
    }

    /**
     * 🔍 Récupère les vainqueurs des matchs d'un tournoi donné
     * Retourne un tableau contenant les IDs des équipes gagnantes
     */
    public function getWinnersBySousTournois(Tournoi $tournoi): array
    {
        return $this->createQueryBuilder('g')
            ->select('IDENTITY(g.vainqueur) as winner_id')
            ->join('g.tournoi', 't')
            ->where('t.parentTournoi = :tournoi') // Sélectionne uniquement les sous-tournois
            ->andWhere('g.vainqueur IS NOT NULL') // Ne prend que les matchs avec un vainqueur
            ->setParameter('tournoi', $tournoi)
            ->groupBy('g.vainqueur') // Assure une seule ligne par vainqueur
            ->getQuery()
            ->getSingleColumnResult(); // Retourne uniquement les IDs des vainqueurs
    }


}
