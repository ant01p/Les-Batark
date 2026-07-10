<?php

namespace App\Controller;

use App\Entity\Order;
use Doctrine\ORM\EntityManagerInterface;
use Stripe\Checkout\Session;
use Stripe\Stripe;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use App\Entity\OrderItem;

#[IsGranted('ROLE_USER')]
class CheckoutController extends AbstractController
{
    public function __construct(
        private readonly string $stripeSecretKey,
    ) {
    }

    #[Route('/commande/demarrer', name: 'checkout_start', methods: ['POST'])]
    public function start(Request $request, EntityManagerInterface $em): Response
    {
        if (!$this->isCsrfTokenValid('checkout_start', $request->request->get('_token'))) {
            throw $this->createAccessDeniedException();
        }

        /** @var \App\Entity\User $user */
        $user = $this->getUser();
        $cart = $user->getCart();

        if (!$cart || $cart->getCartItems()->isEmpty()) {
            $this->addFlash('error', 'Votre panier est vide.');
            return $this->redirectToRoute('cart_index');
        }

        Stripe::setApiKey($this->stripeSecretKey);

        $lineItems = [];
        foreach ($cart->getCartItems() as $item) {
            $productName = $item->getProduct()->getName();

            $details = [];
            if ($item->getType()) {
                $details[] = $item->getType()->getName();
            }
            if ($item->getQuality()) {
                $details[] = $item->getQuality()->getName();
            }
            $description = $details ? implode(' - ', $details) : null;

            $lineItems[] = [
                'price_data' => [
                    'currency' => 'eur',
                    'product_data' => [
                        'name' => $productName,
                        'description' => $description,
                    ],
                    'unit_amount' => $item->getUnitPrice(),
                ],
                'quantity' => $item->getQuantity(),
            ];
        }

        $session = Session::create([
            'mode' => 'payment',
            'payment_method_types' => ['card'],
            'line_items' => $lineItems,
            'success_url' => $this->generateUrl('checkout_success', [], 0) . '?session_id={CHECKOUT_SESSION_ID}',
            'cancel_url' => $this->generateUrl('checkout_cancel', [], 0),
            'customer_email' => $user->getEmail(),
        ]);

        return $this->redirect($session->url);
    }

    #[Route('/commande/succes', name: 'checkout_success', methods: ['GET'])]
    public function success(Request $request, EntityManagerInterface $em): Response
    {
        /** @var \App\Entity\User $user */
        $user = $this->getUser();
        $sessionId = $request->query->get('session_id');

        if (!$sessionId) {
            $this->addFlash('error', 'Session de paiement invalide.');
            return $this->redirectToRoute('cart_index');
        }

        Stripe::setApiKey($this->stripeSecretKey);
        $session = Session::retrieve($sessionId);

        if ($session->payment_status !== 'paid') {
            $this->addFlash('error', 'Le paiement n\'a pas été confirmé.');
            return $this->redirectToRoute('cart_index');
        }

        $cart = $user->getCart();

        if (!$cart || $cart->getCartItems()->isEmpty()) {
            // Le panier a déjà été vidé (ex: rafraîchissement de la page de succès)
            return $this->render('checkout/success.html.twig');
        }

        $orderCode = strtoupper(substr(uniqid('CMD'), 0, 12));

        $order = new Order();
        $order->setUser($user);
        $order->setCustomerEmail($user->getEmail());
        $order->setCustomerPseudo($user->getPseudo());
        $order->setStatus('payee');
        $order->setOrderCode($orderCode);
        $order->setStripePaymentId($session->payment_intent ?? $session->id);
        $order->setCreatedAt(new \DateTimeImmutable());

        $total = 0;

        foreach ($cart->getCartItems() as $item) {
            $orderItem = new OrderItem();
            $orderItem->setProduct($item->getProduct());
            $orderItem->setProductName($item->getProduct()->getName());
            $orderItem->setType($item->getType());
            $orderItem->setQuality($item->getQuality());
            $orderItem->setQuantity($item->getQuantity());
            $orderItem->setUnitPrice($item->getUnitPrice());

            $order->addOrderItem($orderItem);
            $total += $orderItem->getTotalPrice();

            $em->remove($item);
        }

        $order->setTotal($total);

        $em->persist($order);
        $em->flush();

        return $this->render('checkout/success.html.twig', [
            'orderCode' => $orderCode,
        ]);
    }

    #[Route('/commande/annulee', name: 'checkout_cancel', methods: ['GET'])]
    public function cancel(): Response
    {
        $this->addFlash('error', 'Le paiement a été annulé.');

        return $this->render('checkout/cancel.html.twig');
    }

    #[Route('/test-success', name: 'test_success')]
    public function testSuccess(): Response
    {
        return $this->render('checkout/success.html.twig', [
            'orderCode' => 'CMD-TEST-1234',
        ]);
    } 
}