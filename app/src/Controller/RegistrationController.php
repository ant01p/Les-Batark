<?php

namespace App\Controller;

use App\Entity\AdminActivityLog;
use App\Entity\User;
use App\Form\RegistrationFormType;
use App\Form\ResendVerificationEmailFormType;
use App\Repository\UserRepository;
use App\Security\EmailVerifier;
use App\Service\AdminActivityLogger;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mime\Address;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\RateLimiter\RateLimiterFactory;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;
use SymfonyCasts\Bundle\VerifyEmail\Exception\VerifyEmailExceptionInterface;

class RegistrationController extends AbstractController
{
    public function __construct(
        private EmailVerifier $emailVerifier,
        private string $mailerFromAddress,
        private string $mailerFromName,
    ) {
    }

    #[Route('/register', name: 'app_register')]
    public function register(Request $request, UserPasswordHasherInterface $userPasswordHasher,
        EntityManagerInterface $entityManager, AdminActivityLogger $activityLogger,
    ): Response {
        $user = new User();

        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var string $plainPassword */
            $plainPassword = $form->get('plainPassword')->getData();

            $user->setRoles(['ROLE_USER']);

            $hashedPassword = $userPasswordHasher->hashPassword(
                $user,
                $plainPassword
            );

            $user->setPassword($hashedPassword);
            $user->setIsVerified(false);

            $entityManager->persist($user);
            $entityManager->flush();

            $activityLogger->log(actor: $user, action: AdminActivityLog::ACTION_MEMBER_REGISTERED, subjectType: AdminActivityLog::SUBJECT_MEMBER, subjectId: $user->getId(), subjectLabel: $user->getPseudo());

            $this->emailVerifier->sendEmailConfirmation('app_verify_email', $user,
                (new TemplatedEmail())
                    ->from(new Address($this->mailerFromAddress, $this->mailerFromName))
                    ->to((string) $user->getEmail())
                    ->subject('Confirmez votre adresse email — LES-BATARK')
                    ->htmlTemplate('emails/confirmation_email.html.twig')
            );

            $this->addFlash('info', 'Bravo, vous avez survécu au formulaire d\'inscription ! Une dernière épreuve vous attend : cliquez sur le lien de vérification envoyé dans votre boîte mail. Même un Dodo peut le faire… normalement.');

            return $this->redirectToRoute('app_login');
        }

        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form,
        ]);
    }

    #[Route('/verify/email', name: 'app_verify_email')]
    public function verifyUserEmail(Request $request, UserRepository $userRepository, TranslatorInterface $translator): Response
    {
        $id = $request->query->get('id');

        if (null === $id || null === $user = $userRepository->find($id)) {
            return $this->redirectToRoute('app_login');
        }

        try {
            $this->emailVerifier->handleEmailConfirmation($request, $user);
        } catch (VerifyEmailExceptionInterface $exception) {
            $this->addFlash('error', $translator->trans($exception->getReason(), [], 'VerifyEmailBundle'));

            return $this->redirectToRoute('app_resend_verification_email');
        }

        $this->addFlash(
            'success',
            'Félicitations, vous êtes désormais un vrai BatArk ! Vous pouvez maintenant vous connecter.'
        );

        return $this->redirectToRoute('app_login');
    }

    #[Route('/verify/email/resend', name: 'app_resend_verification_email')]
    public function resendVerificationEmail(
        Request $request,
        UserRepository $userRepository,
        #[Autowire(service: 'limiter.verify_email_ip')] RateLimiterFactory $verifyEmailIpLimiter,
    ): Response {
        $form = $this->createForm(ResendVerificationEmailFormType::class, [
            'email' => $request->query->get('email', ''),
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Message neutre dans tous les cas : ne révèle jamais si le compte existe.
            $neutralFlash = 'Si un compte existe avec cette adresse et n\'est pas encore vérifié, un nouvel email de vérification vient d\'être envoyé.';

            $limiter = $verifyEmailIpLimiter->create($request->getClientIp());
            if (!$limiter->consume(1)->isAccepted()) {
                $this->addFlash('info', $neutralFlash);

                return $this->redirectToRoute('app_login');
            }

            $user = $userRepository->findOneBy(['email' => $form->get('email')->getData()]);

            if ($user && !$user->isVerified()) {
                $this->emailVerifier->sendEmailConfirmation('app_verify_email', $user,
                    (new TemplatedEmail())
                        ->from(new Address($this->mailerFromAddress, $this->mailerFromName))
                        ->to((string) $user->getEmail())
                        ->subject('Confirmez votre adresse email — LES-BATARK')
                        ->htmlTemplate('emails/confirmation_email.html.twig')
                );
            }

            $this->addFlash('info', $neutralFlash);

            return $this->redirectToRoute('app_login');
        }

        return $this->render('registration/resend_verification_email.html.twig', [
            'resendForm' => $form,
        ]);
    }
}
