<?php

namespace App\Form;

use App\Entity\Server;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Choice;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Positive;

class ServerType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            // Informations principales
            ->add('title', TextType::class, [
                'label'       => 'Titre du serveur',
                'attr'        => ['placeholder' => 'Ex : ARK PVE - The Island'],
                'constraints' => [new NotBlank(message: 'Le titre est obligatoire.')],
            ])
            ->add('gameMode', ChoiceType::class, [
                'label'       => 'Mode du serveur',
                'choices'     => [
                    'PVE' => Server::MODE_PVE,
                    'PVP' => Server::MODE_PVP,
                ],
                'expanded'    => true,
                'multiple'    => false,
                'constraints' => [
                    new NotBlank(message: 'Le mode du serveur est obligatoire.'),
                    new Choice(choices: [Server::MODE_PVE, Server::MODE_PVP], message: 'Le mode doit être PVE ou PVP.'),
                ],
            ])
            ->add('maxPlayers', IntegerType::class, [
                'label'       => 'Nombre maximum de places',
                'attr'        => ['min' => 1],
                'constraints' => [
                    new NotBlank(message: 'Le nombre de places est obligatoire.'),
                    new Positive(message: 'Le nombre de places doit être supérieur à zéro.'),
                ],
            ])
            ->add('address', TextType::class, [
                'label'    => 'Adresse du serveur',
                'required' => false,
                'attr'     => ['placeholder' => 'Ex : ark-pve-island.server.com:27015'],
            ])
            ->add('mods', TextType::class, [
                'label'       => 'Mods installés',
                'required'    => false,
                'attr'        => ['placeholder' => 'Ex : Awesome SpyGlass, Dino Storage, Structures Plus', 'maxlength' => 100],
                'constraints' => [new Length(max: 100, maxMessage: 'La liste des mods ne peut pas dépasser {{ limit }} caractères.')],
            ])
            ->add('images', TextType::class, [
                'label'       => 'Images du serveur',
                'required'    => false,
                'help'        => "Noms de fichiers séparés par des virgules, dans public/images/ (ex : the-island-1.jpg, the-island-2.jpg). Prévoir une image panoramique en premier.",
                'attr'        => ['placeholder' => 'Ex : the-island-1.jpg, the-island-2.jpg, the-island-3.jpg', 'maxlength' => 500],
                'constraints' => [new Length(max: 500, maxMessage: 'La liste des images ne peut pas dépasser {{ limit }} caractères.')],
            ])

            // Paramètres du serveur
            ->add('harvestRate', NumberType::class, [
                'label'    => 'Taux de récolte',
                'required' => false,
                'scale'    => 2,
                'html5'    => true,
                'attr'     => ['min' => 0, 'step' => '0.01'],
            ])
            ->add('tamingSpeed', NumberType::class, [
                'label'    => "Vitesse d'apprivoisement",
                'required' => false,
                'scale'    => 2,
                'html5'    => true,
                'attr'     => ['min' => 0, 'step' => '0.01'],
            ])
            ->add('matingInterval', NumberType::class, [
                'label'    => "Intervalle d'accouplement",
                'required' => false,
                'scale'    => 2,
                'html5'    => true,
                'attr'     => ['min' => 0, 'step' => '0.01'],
            ])
            ->add('maturationSpeed', NumberType::class, [
                'label'    => 'Vitesse de maturation',
                'required' => false,
                'scale'    => 2,
                'html5'    => true,
                'attr'     => ['min' => 0, 'step' => '0.01'],
            ])
            ->add('incubationSpeed', NumberType::class, [
                'label'    => "Vitesse d'incubation / gestation",
                'required' => false,
                'scale'    => 2,
                'html5'    => true,
                'attr'     => ['min' => 0, 'step' => '0.01'],
            ])
            ->add('itemStackSize', NumberType::class, [
                'label'    => 'Taille des piles d\'objets',
                'required' => false,
                'scale'    => 2,
                'html5'    => true,
                'attr'     => ['min' => 0, 'step' => '0.01'],
            ])
            ->add('wildDinoFoodDrain', NumberType::class, [
                'label'    => 'Faim des dinos sauvages',
                'required' => false,
                'scale'    => 2,
                'html5'    => true,
                'attr'     => ['min' => 0, 'step' => '0.01'],
            ])
            ->add('maxDinoLevel', IntegerType::class, [
                'label'    => 'Niveau maximum des dinos',
                'required' => false,
                'attr'     => ['min' => 0],
            ])
            ->add('maxTekDinoLevel', IntegerType::class, [
                'label'    => 'Niveau maximum des dinos Tek',
                'required' => false,
                'attr'     => ['min' => 0],
            ])
            ->add('maxEggLevel', IntegerType::class, [
                'label'    => 'Niveau maximum des œufs',
                'required' => false,
                'attr'     => ['min' => 0],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Server::class,
        ]);
    }
}
