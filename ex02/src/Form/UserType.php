<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Validator\Constraints as Assert;

class UserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('username', null, [
                'constraints' => [
                    new Assert\NotBlank(message: 'Le nom d\'utilisateur est requis.'),
                    new Assert\Length([
                        'max' => 15,
                        'maxMessage' => 'Le nom d\'utilisateur ne peut pas dépasser {{ limit }} caractères.',
                    ]),
                ],
                'label' => 'Nom d’utilisateur',
                'attr' => ['maxlength' => 15],
            ])
            ->add('plainPassword', PasswordType::class, [
                'mapped' => false,
                'label' => 'Mot de passe',
                'constraints' => [
                    new Assert\NotBlank(message: 'Le mot de passe est requis.'),
                    new Assert\Length([
                        'max' => 20,
                        'maxMessage' => 'Le mot de passe ne peut pas dépasser {{ limit }} caractères.',
                    ]),
                ],
                'attr' => ['maxlength' => 20],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
