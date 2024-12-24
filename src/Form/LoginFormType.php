<?php
namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\NotBlank;

class LoginFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('_username', EmailType::class, [
                'constraints' => [
                    new NotBlank(['message' => 'Veuillez entrer un email']),
                ],
            ])
            ->add('_password', PasswordType::class, [
                'mapped' => false, 
                'constraints' => [
                    new NotBlank(['message' => 'Veuillez entrer votre mot de passe']),
                ],
            ]);
    }
}
