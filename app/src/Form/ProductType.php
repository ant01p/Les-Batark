<?php

namespace App\Form;

use App\Entity\Product;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use App\Entity\Category;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Validator\Constraints\File;

class ProductType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'Nom du produit',
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Description',
                'required' => false,
            ])
            ->add('price', MoneyType::class, [
                'label' => 'Prix',
                'currency' => 'EUR',
                'divisor' => 100,
            ])
            
            ->add('category', EntityType::class, [
                'class' => Category::class,
                'choice_label' => 'name',
                'placeholder' => 'Choisir une catégorie',
                'label' => 'Catégorie',
            ])
            ->add('hasType', CheckboxType::class, [
                'label' => 'Le client choisit Déjà crafté / Blueprint',
                'required' => false,
            ])
            ->add('hasQuality', CheckboxType::class, [
                'label' => 'Le client choisit la Qualité',
                'required' => false,
            ])
            ->add('images', CollectionType::class, [
                'label' => false,
                'mapped' => false,
                'required' => false,
                'entry_type' => FileType::class,
                'entry_options' => [
                    'label' => false,
                    'required' => false,
                    'constraints' => [
                        new File(
                            maxSize: '20M',
                            mimeTypes: ['image/jpeg', 'image/png', 'image/webp', 'image/gif'],
                            mimeTypesMessage: 'Merci de choisir une image valide (jpeg, png, webp, gif).',
                        ),
                    ],
                ],
                'allow_add' => true,
                'allow_delete' => true,
                'prototype' => true,
                'prototype_name' => '__image_name__',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Product::class,
        ]);
    }
}