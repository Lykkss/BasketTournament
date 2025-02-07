<?php

namespace App\Controller\Admin;

use App\Entity\Tournoi;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;

class TournoiCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Tournoi::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->hideOnForm(),
            TextField::new('nom', 'Nom du Tournoi'),
            DateTimeField::new('dateDebut', 'Date de Début'),
            DateTimeField::new('dateFin', 'Date de Fin'),
            ChoiceField::new('status', 'Statut')
                ->setChoices(['À venir' => 'À venir', 'En cours' => 'En cours', 'Terminé' => 'Terminé'])
        ];
    }
}
