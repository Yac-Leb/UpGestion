<?php
namespace App\Form;

use App\Entity\Article;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ShopFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('titre', TextType::class, [
                'label' => 'Titre',
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Entrez le titre de l\'article'
                ]
            ])
            ->add('prix', NumberType::class, [
                'label' => 'Prix',
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Entrez le prix de l\'article',
                    'step' => '0.01'
                ]
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Description',
                'attr' => [
                    'class' => 'form-control',
                    'rows' => 5,
                    'placeholder' => 'Entrez une description détaillée'
                ]
            ])
            ->add('premiere_image', FileType::class, [
                'label' => 'Première Image',
                'mapped' => false, // le fichier ne sera pas lié à un champ d'entité
                'attr' => [
                    'class' => 'form-control-file'
                ],
                'required' => false
            ])
            ->add('deuxieme_image', FileType::class, [
                'label' => 'Deuxième Image',
                'mapped' => false, // le fichier ne sera pas lié à un champ d'entité
                'attr' => [
                    'class' => 'form-control-file'
                ],
                'required' => false
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'Ajouter l\'Article',
                'attr' => [
                    'class' => 'btn btn-primary btn-block'
                ]
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Article::class, // spécifie que ce formulaire est lié à l'entité Article
        ]);
    }
}
