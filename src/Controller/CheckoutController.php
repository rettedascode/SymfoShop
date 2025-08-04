<?php

namespace App\Controller;

use App\Entity\Order;
use App\Entity\OrderItem;
use App\Entity\Address;
use App\Repository\ProductRepository;
use App\Repository\OrderRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;

#[Route('/checkout')]
class CheckoutController extends AbstractController
{
    #[Route('/', name: 'app_checkout')]
    public function index(SessionInterface $session, ProductRepository $productRepository): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');

        $cart = $session->get('cart', []);
        if (empty($cart)) {
            return $this->redirectToRoute('app_cart');
        }

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

        return $this->render('checkout/index.html.twig', [
            'cart_items' => $cartItems,
            'total' => $total,
        ]);
    }

    #[Route('/process', name: 'app_checkout_process', methods: ['POST'])]
    public function process(Request $request, SessionInterface $session, EntityManagerInterface $entityManager): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');

        $cart = $session->get('cart', []);
        if (empty($cart)) {
            return $this->redirectToRoute('app_cart');
        }

        $user = $this->getUser();
        
        // Create order
        $order = new Order();
        $order->setUser($user);
        $order->setStatus(Order::STATUS_PENDING);
        $order->setPaymentMethod($request->request->get('payment_method'));
        $order->setShippingMethod($request->request->get('shipping_method'));

        // Create addresses
        $billingAddress = new Address();
        $billingAddress->setUser($user);
        $billingAddress->setFirstName($request->request->get('billing_first_name'));
        $billingAddress->setLastName($request->request->get('billing_last_name'));
        $billingAddress->setStreet($request->request->get('billing_street'));
        $billingAddress->setCity($request->request->get('billing_city'));
        $billingAddress->setState($request->request->get('billing_state'));
        $billingAddress->setPostalCode($request->request->get('billing_postal_code'));
        $billingAddress->setCountry($request->request->get('billing_country'));
        $billingAddress->setPhone($request->request->get('billing_phone'));

        $shippingAddress = new Address();
        $shippingAddress->setUser($user);
        $shippingAddress->setFirstName($request->request->get('shipping_first_name'));
        $shippingAddress->setLastName($request->request->get('shipping_last_name'));
        $shippingAddress->setStreet($request->request->get('shipping_street'));
        $shippingAddress->setCity($request->request->get('shipping_city'));
        $shippingAddress->setState($request->request->get('shipping_state'));
        $shippingAddress->setPostalCode($request->request->get('shipping_postal_code'));
        $shippingAddress->setCountry($request->request->get('shipping_country'));
        $shippingAddress->setPhone($request->request->get('shipping_phone'));

        $order->setBillingAddress($billingAddress);
        $order->setShippingAddress($shippingAddress);

        // Add order items
        $subtotal = 0;
        foreach ($cart as $productId => $quantity) {
            $product = $entityManager->getRepository('App\Entity\Product')->find($productId);
            if ($product && $product->isActive()) {
                $orderItem = new OrderItem();
                $orderItem->setOrder($order);
                $orderItem->setProduct($product);
                $orderItem->setQuantity($quantity);
                $orderItem->setUnitPrice($product->getPrice());
                $orderItem->setSubtotal($product->getPrice() * $quantity);
                
                $subtotal += $orderItem->getSubtotal();
                $entityManager->persist($orderItem);
            }
        }

        $order->setSubtotal($subtotal);
        $order->setTotal($subtotal); // Add shipping and tax calculation here

        $entityManager->persist($billingAddress);
        $entityManager->persist($shippingAddress);
        $entityManager->persist($order);
        $entityManager->flush();

        // Clear cart
        $session->remove('cart');

        $this->addFlash('success', 'Order placed successfully!');

        return $this->redirectToRoute('app_order_show', ['id' => $order->getId()]);
    }
} 