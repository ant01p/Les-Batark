<?php

namespace App\Controller;

use App\Entity\Cart;
use App\Entity\CartItem;
use App\Entity\Category;
use App\Entity\Product;
use App\Entity\Quality;
use App\Entity\Type;
use App\Repository\QualityRepository;
use App\Repository\TypeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_USER')]
class CartController extends AbstractController
{
    #[Route('/panier', name: 'cart_index', methods: ['GET'])]
    public function index(EntityManagerInterface $em): Response
    {
        $cart = $this->getOrCreateCart($em);

        return $this->render('cart/index.html.twig', [
            'cart' => $cart,
        ]);
    }

    #[Route('/panier/ajouter/{id}', name: 'cart_add', methods: ['POST'])]
    public function add(Request $request,
        Product $product,
        EntityManagerInterface $em,
        TypeRepository $typeRepository,
        QualityRepository $qualityRepository
    ): Response {
        if (!$this->isCsrfTokenValid('cart_add_' . $product->getId(), $request->request->get('_token'))) {
            throw $this->createAccessDeniedException();
        }

        $cart = $this->getOrCreateCart($em);

        $quantity = max(1, (int) $request->request->get('quantity', 1));

        $type = null;
        if ($product->hasType()) {
            $typeId = $request->request->get('type_id');
            if (!$typeId) {
                $this->addFlash('error', 'Merci de choisir un type.');
                return $this->redirectToRoute('product_show', ['id' => $product->getId()]);
            }
            $type = $typeRepository->find($typeId);
        }

        $quality = null;
        if ($product->hasQuality()) {
            $qualityId = $request->request->get('quality_id');
            if (!$qualityId) {
                $this->addFlash('error', 'Merci de choisir une qualité.');
                return $this->redirectToRoute('product_show', ['id' => $product->getId()]);
            }
            $quality = $qualityRepository->find($qualityId);
        }

        $sex = null;
        $version = null;
        $stat = null;
        $option = null;
        $color = null;

        if ($product->getCategory() && $product->getCategory()->getName() === Category::DINOSAURES_NAME) {
            if ($product->isSexeActif()) {
                $sex = $request->request->get('sex');
                if (!in_array($sex, ['Mâle', 'Femelle'], true)) {
                    $this->addFlash('error', 'Merci de choisir un sexe.');
                    return $this->redirectToRoute('product_show', ['id' => $product->getId()]);
                }
            }

            $versionsDisponibles = $product->getVersionsDisponibles() ?? [];
            if (!empty($versionsDisponibles)) {
                $version = $request->request->get('version');
                if (!in_array($version, $versionsDisponibles, true)) {
                    $this->addFlash('error', 'Merci de choisir une version.');
                    return $this->redirectToRoute('product_show', ['id' => $product->getId()]);
                }
            }

            $stat = $request->request->get('stat') ?: null;
            if ($stat !== null && !in_array($stat, Product::STATS, true)) {
                $stat = null;
            }

            $option = $request->request->get('option') ?: null;
            if ($option !== null && !in_array($option, Product::OPTIONS, true)) {
                $option = null;
            }

            $color = trim((string) $request->request->get('color'));
            $color = $color === '' ? null : $color;
        }

        // Cherche si une ligne identique existe déjà (même produit + type + qualité + choix dino)
        $existingItem = null;
        foreach ($cart->getCartItems() as $item) {
            if (
                $item->getProduct() === $product
                && $item->getType() === $type
                && $item->getQuality() === $quality
                && $item->getSex() === $sex
                && $item->getVersion() === $version
                && $item->getStat() === $stat
                && $item->getOption() === $option
                && $item->getColor() === $color
            ) {
                $existingItem = $item;
                break;
            }
        }

        if ($existingItem) {
            $existingItem->setQuantity($existingItem->getQuantity() + $quantity);
        } else {
            $cartItem = new CartItem();
            $cartItem->setProduct($product);
            $cartItem->setType($type);
            $cartItem->setQuality($quality);
            $cartItem->setSex($sex);
            $cartItem->setVersion($version);
            $cartItem->setStat($stat);
            $cartItem->setOption($option);
            $cartItem->setColor($color);
            $cartItem->setQuantity($quantity);
            $cartItem->setUnitPrice($product->getPrice());

            $em->persist($cartItem);
            $cart->addCartItem($cartItem);
        }

        $em->flush();

        $this->addFlash('success', 'Produit ajouté au panier.');

        return $this->redirectToRoute('app_shop', ['category' => $product->getCategory()->getId()]);
    }

    #[Route('/panier/quantite/{id}', name: 'cart_update_quantity', methods: ['POST'])]
    public function updateQuantity(CartItem $cartItem, Request $request, EntityManagerInterface $em): Response
    {
        $this->denyAccessUnlessOwner($cartItem);

        if (!$this->isCsrfTokenValid('cart_update_quantity_' . $cartItem->getId(), $request->request->get('_token'))) {
            throw $this->createAccessDeniedException();
        }

        $quantity = (int) $request->request->get('quantity', 1);

        if ($quantity <= 0) {
            $em->remove($cartItem);
        } else {
            $cartItem->setQuantity($quantity);
        }

        $em->flush();

        return $this->redirectToRoute('cart_index');
    }

    #[Route('/panier/supprimer/{id}', name: 'cart_remove', methods: ['POST'])]
    public function remove(CartItem $cartItem, Request $request, EntityManagerInterface $em): Response
    {
        $this->denyAccessUnlessOwner($cartItem);

        if (!$this->isCsrfTokenValid('cart_remove_' . $cartItem->getId(), $request->request->get('_token'))) {
            throw $this->createAccessDeniedException();
        }

        $em->remove($cartItem);
        $em->flush();

        $this->addFlash('success', 'Article retiré du panier.');

        return $this->redirectToRoute('cart_index');
    }

    private function getOrCreateCart(EntityManagerInterface $em): Cart
    {
        /** @var \App\Entity\User $user */
        $user = $this->getUser();

        $cart = $user->getCart();

        if (!$cart) {
            $cart = new Cart();
            $cart->setUser($user);
            $em->persist($cart);
            $em->flush();
        }

        return $cart;
    }

    private function denyAccessUnlessOwner(CartItem $cartItem): void
    {
        if ($cartItem->getCart()->getUser() !== $this->getUser()) {
            throw $this->createAccessDeniedException();
        }
    }
}