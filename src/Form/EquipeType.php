<?php

namespace App\Form;

use App\Entity\Equipe;
use App\Entity\Tournoi;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EquipeType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom')
            ->add('tournoi', EntityType::class, [
                'class' => Tournoi::class,
                'choice_label' => 'nom',
                'label' => 'Tournoi'
            ])
            ->add('membres', EntityType::class, [
                'class' => User::class,
                'choice_label' => 'email',
                'multiple' => true,
                'expanded' => true,
                'label' => 'Membres de l\'équipe'
            ])
            ->add('save', SubmitType::class, [
                'label' => 'Créer une équipe'
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Equipe::class,
        ]);
    }
}
