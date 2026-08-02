<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Formulaire de la section "Permissions administratives" de la fiche membre.
 *
 * Les choix proposés sont strictement limités à User::MANAGEABLE_ROLES (liste blanche) :
 * Symfony rejette automatiquement toute valeur soumise en dehors de cette liste, et
 * MemberModerationService revalide malgré tout ces rôles avant application.
 */
class MemberPermissionsType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('roles', ChoiceType::class, [
            'choices' => array_flip(User::MANAGEABLE_ROLES),
            'multiple' => true,
            'expanded' => true,
            'required' => false,
            'label' => false,
        ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => null,
        ]);
    }
}
