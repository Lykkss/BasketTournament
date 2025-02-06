<?php

namespace App\Controller\Admin;

use App\Entity\Tournoi;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;

class TournoiCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Tournoi::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->hideOnForm(), // Cache l'ID dans le formulaire
            TextField::new('nom', 'Nom du tournoi'),
            DateField::new('dateDebut', 'Date de début'),
            DateField::new('dateFin', 'Date de fin'),
            ChoiceField::new('status', 'Statut')->setChoices([
                'À venir' => 'À venir',
                'En cours' => 'En cours',
                'Terminé' => 'Terminé',
            ]),
            IntegerField::new('nbMaxEquipes', 'Nombre max d\'équipes'),
            AssociationField::new('participants', 'Participants')->setFormTypeOptions([
                'by_reference' => false,
            ]),
            AssociationField::new('games', 'Matchs')->hideOnForm(), // Liste les matchs liés
        ];
    }
}
