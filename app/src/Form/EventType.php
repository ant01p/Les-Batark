<?php

namespace App\Form;

use App\Entity\Event;
use Symfony\Component\Form\AbstractType;
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
                'label'       => 'Date & heure',
                'widget'      => 'single_text',
                'constraints' => [new NotBlank(message: 'La date est obligatoire.')],
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
            ->add('rewardsString', TextType::class, [
                'label'    => 'Récompenses (séparées par des virgules)',
                'mapped'   => false,
                'required' => false,
                'attr'     => ['placeholder' => 'Arme légendaire, 5000 points, Skin exclusif'],
            ])
        ;

        // Pré-remplir rewardsString à partir du tableau en BDD
        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
            $entity = $event->getData();
            if ($entity instanceof Event) {
                $event->getForm()->get('rewardsString')->setData($entity->getRewardsAsString());
            }
        });

        // Reconvertir rewardsString en tableau avant sauvegarde
        $builder->addEventListener(FormEvents::SUBMIT, function (FormEvent $event) {
            $form   = $event->getForm();
            $entity = $event->getData();
            if ($entity instanceof Event) {
                $raw     = $form->get('rewardsString')->getData() ?? '';
                $rewards = array_filter(array_map('trim', explode(',', $raw)));
                $entity->setRewards(array_values($rewards));
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