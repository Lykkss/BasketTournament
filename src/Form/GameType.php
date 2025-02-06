<?php

namespace App\Form;

use App\Entity\Equipe;
use App\Entity\Game;
use App\Entity\Tournoi;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class GameType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('scoreEquipeA')
            ->add('scoreEquipeB')
            ->add('equipeA', EntityType::class, [
                'class' => Equipe::class,
                'choice_label' => 'id',
            ])
            ->add('equipeB', EntityType::class, [
                'class' => Equipe::class,
                'choice_label' => 'id',
            ])
            ->add('vainqueur', EntityType::class, [
                'class' => Equipe::class,
                'choice_label' => 'id',
            ])
            ->add('tournoi', EntityType::class, [
                'class' => Tournoi::class,
                'choice_label' => 'id',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Game::class,
        ]);
    }
}
