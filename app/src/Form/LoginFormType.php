<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;

/**
 * Formulaire d'affichage pour /login.
 *
 * Le champ _csrf_token n'utilise pas le mécanisme csrf_protection du composant
 * Form : c'est le firewall (form_login, enable_csrf) qui valide ce jeton via
 * son propre CsrfTokenManager, indépendamment de Symfony Form. Ce formulaire
 * ne sert donc qu'au rendu ; il n'est jamais soumis/validé par le controller.
 */
class LoginFormType extends AbstractType
{
    public function __construct(private CsrfTokenManagerInterface $csrfTokenManager)
    {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('_username', EmailType::class, [
                'label' => 'Email',
                'attr' => [
                    'autocomplete' => 'email',
                    'placeholder' => 'Entrez votre email',
                ],
            ])
            ->add('_password', PasswordType::class, [
                'label' => 'Mot de passe',
                'attr' => [
                    'autocomplete' => 'current-password',
                    'placeholder' => 'Entrez votre mot de passe',
                ],
            ])
            ->add('_remember_me', CheckboxType::class, [
                'label' => 'Se souvenir de moi',
                'required' => false,
            ])
            ->add('_csrf_token', HiddenType::class, [
                'data' => $this->csrfTokenManager->getToken('authenticate')->getValue(),
                'attr' => [
                    // Nécessaire pour que le contrôleur Stimulus (lazy) charge le JS
                    // qui pose le cookie de double-submit CSRF sur ce formulaire.
                    'data-controller' => 'csrf-protection',
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'csrf_protection' => false,
        ]);
    }
}
