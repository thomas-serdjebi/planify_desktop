<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\IsTrue;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\EqualTo;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;

class RegistrationFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('email', EmailType::class, [
                'mapped' => true,
                'attr' => ['class' => 'form-control mb-3 rounded'],
                'label' => 'Email de l\'utilisateur',
                
            ])
            ->add('firstName', TextType::class,[
                'mapped' => true,
                'attr' => ['class' => 'form-control mb-3 rounded'],
                'label' => 'Prenom :',
                'constraints' => [
                    new NotBlank([
                        'message' => 'Veuillez entrer le prénom de l\'utilisateur :',
                    ])
                ],
            ])
            ->add('name', TextType::class,[
                'mapped' => true,
                'attr' => ['class' => 'form-control mb-3 rounded'],
                'label' => 'Nom :',
                'constraints' => [
                    new NotBlank([
                        'message' => 'Veuillez entrer le nom de l\'utilisateur :',
                    ])
                ],
            ])
            ->add('plainPassword', PasswordType::class, [
                'label' => 'Mot de passe :',
                'mapped' => false,
                'attr' => [
                    'autocomplete' => 'new-password',
                    'class' => 'form-control mb-3 rounded'
                ],
                'constraints' => [
                    new NotBlank([
                        'message' => 'Please enter a password',
                    ]),
                    new Length([
                        'min' => 6,
                        'minMessage' => 'Your password should be at least {{ limit }} characters',
                        // max length allowed by Symfony for security reasons
                        'max' => 4096,
                    ]),
                ],
            ])
            ->add('confirmedPassword', PasswordType::class, [
                'label' => 'Confirmer le mot de passe :',
                'mapped' => false,
                'attr' => [
                    'autocomplete' => 'new-password',
                    'class' => 'form-control mb-3 rounded'
                ],
                'constraints' => [
                    new NotBlank([
                        'message' => 'Veuillez confirmer votre mot de passe.',
                    ]),
                    // new EqualTo([
                    //     'value' => $builder->get('plainPassword')->getData(),
                    //     'message' => 'Les mots de passe doivent être identiques.',
                    // ]),
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
