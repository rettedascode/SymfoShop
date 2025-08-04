<?php

namespace App\Controller;

use App\Entity\Product;
use App\Repository\ProductRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/cart')]
class CartController extends AbstractController
{
    #[Route('/', name: 'app_cart')]
    public function index(SessionInterface $session, ProductRepository $productRepository): Response
    {
        $cart = $session->get('cart', []);
        $cartItems = [];
        $total = 0;

        foreach ($cart as $productId => $quantity) {
            $product = $productRepository->find($productId);
            if ($product && $product->isActive()) {
                $cartItems[] = [
                    'product' => $product,
                    'quantity' => $quantity,
                    'subtotal' => $product->getPrice() * $quantity
                ];
                $total += $product->getPrice() * $quantity;
            }
        }

        return $this->render('cart/index.html.twig', [
            'cart_items' => $cartItems,
            'total' => $total,
        ]);
    }

    #[Route('/add/{id}', name: 'app_cart_add', requirements: ['id' => '\d+'])]
    public function add(Product $product, Request $request, SessionInterface $session): Response
    {
        if (!$product->isActive()) {
            throw $this->createNotFoundException('Product not found');
        }

        $quantity = $request->request->getInt('quantity', 1);
        $cart = $session->get('cart', []);

        if (isset($cart[$product->getId()])) {
            $cart[$product->getId()] += $quantity;
        } else {
            $cart[$product->getId()] = $quantity;
        }

        $session->set('cart', $cart);
        $this->addFlash('success', 'Product added to cart');

        return $this->redirectToRoute('app_cart');
    }

    #[Route('/update/{id}', name: 'app_cart_update', requirements: ['id' => '\d+'])]
    public function update(Product $product, Request $request, SessionInterface $session): Response
    {
        $quantity = $request->request->getInt('quantity', 0);
        $cart = $session->get('cart', []);

        if ($quantity > 0) {
            $cart[$product->getId()] = $quantity;
        } else {
            unset($cart[$product->getId()]);
        }

        $session->set('cart', $cart);
        $this->addFlash('success', 'Cart updated');

        return $this->redirectToRoute('app_cart');
    }

    #[Route('/remove/{id}', name: 'app_cart_remove', requirements: ['id' => '\d+'])]
    public function remove(Product $product, SessionInterface $session): Response
    {
        $cart = $session->get('cart', []);
        unset($cart[$product->getId()]);
        $session->set('cart', $cart);

        $this->addFlash('success', 'Product removed from cart');

        return $this->redirectToRoute('app_cart');
    }

    #[Route('/clear', name: 'app_cart_clear')]
    public function clear(SessionInterface $session): Response
    {
        $session->remove('cart');
        $this->addFlash('success', 'Cart cleared');

        return $this->redirectToRoute('app_cart');
    }
} 