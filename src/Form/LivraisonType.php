<?php

namespace App\Form;

use App\Entity\Trajet;
use App\Entity\Livraison;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class LivraisonType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('numero', TextType::class, [
                'label' => 'Numéro de livraison :',
                'required' => true
            ])
            ->add('adresse', TextType::class, [
                'label' => 'Adresse :'
            ])
            ->add('code_postal', TextType::class, [
                'label' => 'Code postal :',
                'required' => true
            ])
            ->add('ville', TextType::class, [
                'label' => 'Ville :',
                'required' => true
            ])
            ->add('client_nom', TextType::class, [
                'label' => "Nom :",
                'required' => true
            ])
            ->add('client_prenom', TextType::class, [
                'label' => "Prénom :",
                'required' => true
            ])
            ->add('client_email', EmailType::class, [
                'label' => 'Email :',
                'required' => true
            ])
            ->add('client_telephone', TextType::class, [
                'label' => 'Téléphone :',
                'required' => true
            ])
            ->add('date', null, [
                'widget' => 'single_text',
                'label' => 'Date de livraison :',
                'required' => true,
                'data' => new \DateTime('+1 day'), // Définit la date par défaut au lendemain
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
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Livraison::class,
        ]);
    }
}
