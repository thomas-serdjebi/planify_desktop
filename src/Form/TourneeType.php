<?php

namespace App\Form;

use App\Entity\Tournee;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TourneeType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('users', EntityType::class, [
                'class' => User::class,
                'choice_label' => function (User $user) {
                    return $user->getFirstName() . ' ' . $user->getName();
                },
                'multiple' => true,
                'expanded' => true, // true = cases à cocher, false = liste déroulante multiple
                'label' => 'Utilisateurs concernés',
                'mapped' => false
            ])
            ->add('date', DateType::class, [
                'widget' => 'single_text',
            ])
            ->add('creneau', ChoiceType::class, [
                'choices' => [
                    'Matin' => 1,
                    'Après-midi' => 2,
                ],
                'expanded' => true,  // Affiche les choix sous forme de boutons radio
                'multiple' => false, // Empêche la sélection multiple
                'label' => 'Créneau :',
                'required' => true,
            ])        ;
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            
        ]);
    }
}
