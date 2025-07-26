<?php

namespace App\Form;

use Symfony\Component\Validator\Constraints as Assert;
use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;

class UserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('username', null, [
                'constraints' => [
                    new Assert\NotBlank(message: 'Le nom d\'utilisateur est requis.'),
                    new Assert\Length([
                        'min' => 4,
                        'max' => 50,
                        'minMessage' => 'Le nom d\'utilisateur doit contenir au moins {{ limit }} caractères.',
                        'maxMessage' => 'Le nom d\'utilisateur ne peut pas dépasser {{ limit }} caractères.',
                    ]),
                ],
                'label' => 'Nom d’utilisateur',
            ])            ->add('plainPassword', PasswordType::class, [
                'mapped' => false,
                'label' => 'Mot de passe',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
