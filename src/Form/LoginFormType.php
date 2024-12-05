<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Length;

class LoginFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('mail', EmailType::class, [
                'constraints' => [
                    new NotBlank(['message' => 'Veuillez entrer un email']),
                ],
            ])
            ->add('plainPassword', PasswordType::class, [
                'mapped' => false, 
                'constraints' => [
                    new NotBlank(['message' => 'Veuillez entrer votre mot de passe']),
                ],
            ]);
    }
}