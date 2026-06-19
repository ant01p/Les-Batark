<?php

namespace App\Form;

use App\Entity\Event;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class EventType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', TextType::class, [
                'label'       => 'Titre',
                'attr'        => ['placeholder' => 'Ex : Chasse au Trésor Légendaire'],
                'constraints' => [new NotBlank(message: 'Le titre est obligatoire.')],
            ])
            ->add('location', TextType::class, [
                'label'    => 'Lieu',
                'required' => false,
                'attr'     => ['placeholder' => 'Ex : The Island - Zone Nord'],
            ])
            ->add('date', DateTimeType::class, [
                'label'       => 'Début',
                'widget'      => 'single_text',
                'constraints' => [new NotBlank(message: 'La date est obligatoire.')],
            ])
            ->add('endDate', DateTimeType::class, [
                'label'    => 'Fin',
                'widget'   => 'single_text',
                'required' => false,
            ])
            ->add('duration', TextType::class, [
                'label'    => 'Durée',
                'required' => false,
                'attr'     => ['placeholder' => 'Ex : 1h30'],
            ])
            ->add('maxParticipants', IntegerType::class, [
                'label'    => 'Max participants',
                'required' => false,
                'attr'     => ['min' => 1],
            ])
            ->add('status', ChoiceType::class, [
                'label'   => 'Statut',
                'choices' => [
                    'À venir'  => Event::STATUS_UPCOMING,
                    'En cours' => Event::STATUS_ONGOING,
                    'Terminé'  => Event::STATUS_FINISHED,
                ],
            ])
            ->add('description', TextareaType::class, [
                'label'    => 'Description',
                'required' => false,
                'attr'     => ['rows' => 4, 'placeholder' => 'Décrivez l\'événement…'],
            ])
            ->add('isRankingMode', CheckboxType::class, [
                'label'    => 'Établir un classement',
                'mapped'   => false, //Sans mapped: false, Symfony essaierait (et échouerait) de trouver une propriété reward1 sur Event.//
                'required' => false,//Ce champ n'est pas obligatoire, il peut être laissé décoché pour les événements sans classement.//
            ])
            ->add('rewardSimple', TextType::class, [
                'label'    => 'Récompenses',
                'mapped'   => false,
                'required' => false,
                'attr'     => ['placeholder' => 'Ex : Arme légendaire, 5000 points'],
            ])
            ->add('reward1', TextType::class, [
                'label'    => '🥇 1er',
                'mapped'   => false,
                'required' => false,
                'attr'     => ['placeholder' => 'Ex : Arme légendaire + 5000 points'],
            ])
            ->add('reward2', TextType::class, [
                'label'    => '🥈 2ème',
                'mapped'   => false,
                'required' => false,
                'attr'     => ['placeholder' => 'Ex : 3000 points + Skin exclusif'],
            ])
            ->add('reward3', TextType::class, [
                'label'    => '🥉 3ème',
                'mapped'   => false,
                'required' => false,
                'attr'     => ['placeholder' => 'Ex : 1000 points'],
            ])
            ->add('rewardGeneral', TextType::class, [
                'label'    => 'Récompense générale',
                'mapped'   => false,
                'required' => false,
                'attr'     => ['placeholder' => 'Ex : Titre de champion + 500 points pour tous'],
            ])
        ;

        // Pré-remplir les champs à partir du tableau en BDD
        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
            $entity = $event->getData();
            if ($entity instanceof Event) {
                $form = $event->getForm();
                $form->get('isRankingMode')->setData($entity->isRanking());
                $form->get('rewardSimple')->setData($entity->getRewardSimple());
                $form->get('reward1')->setData($entity->getReward1());
                $form->get('reward2')->setData($entity->getReward2());
                $form->get('reward3')->setData($entity->getReward3());
                $form->get('rewardGeneral')->setData($entity->getRewardGeneral());
            }
        });

        // Reconvertir les champs en tableau avant sauvegarde
        $builder->addEventListener(FormEvents::SUBMIT, function (FormEvent $event) {
            $form   = $event->getForm();
            $entity = $event->getData();
            if ($entity instanceof Event) {
                $isRanking = $form->get('isRankingMode')->getData();
                if ($isRanking) {
                    $entity->setRewards([
                        'type'    => 'ranking',
                        '1'       => trim($form->get('reward1')->getData() ?? ''),
                        '2'       => trim($form->get('reward2')->getData() ?? ''),
                        '3'       => trim($form->get('reward3')->getData() ?? ''),
                        'general' => trim($form->get('rewardGeneral')->getData() ?? ''),
                    ]);
                } else {
                    $entity->setRewards([
                        'type'  => 'simple',
                        'value' => trim($form->get('rewardSimple')->getData() ?? ''),
                    ]);
                }
            }
        });
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Event::class,
        ]);
    }
}