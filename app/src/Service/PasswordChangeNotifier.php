<?php

namespace App\Service;

use App\Entity\User;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;

/**
 * Envoie l'email de notification "votre mot de passe a été modifié", partagé entre
 * le flow de reset et le changement de mot de passe depuis Mon compte.
 */
class PasswordChangeNotifier
{
    public function __construct(
        private MailerInterface $mailer,
        private string $mailerFromAddress,
        private string $mailerFromName,
    ) {
    }

    public function notify(User $user): void
    {
        try {
            $email = (new TemplatedEmail())
                ->from(new Address($this->mailerFromAddress, $this->mailerFromName))
                ->to((string) $user->getEmail())
                ->subject('Votre mot de passe a été modifié — LES-BATARK')
                ->htmlTemplate('emails/password_changed.html.twig')
                ->context([
                    'changedAt' => new \DateTimeImmutable(),
                ])
            ;

            $this->mailer->send($email);
        } catch (\Throwable) {
            // Non bloquant : un échec d'envoi ne doit pas empêcher le changement d'avoir réussi.
        }
    }
}
