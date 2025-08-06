<?php

namespace App\Controller;

use App\Entity\Order;
use App\Repository\OrderRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/orders')]
class OrderController extends AbstractController
{
    #[Route('/', name: 'app_orders')]
    public function index(OrderRepository $orderRepository): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');

        $user = $this->getUser();
        $orders = $orderRepository->findByUser($user);

        return $this->render('order/index.html.twig', [
            'orders' => $orders,
        ]);
    }

    #[Route('/{id}', name: 'app_order_show', requirements: ['id' => '\d+'])]
    public function show(Order $order): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');

        // Check if the order belongs to the current user
        if ($order->getUser() !== $this->getUser()) {
            throw $this->createAccessDeniedException('You can only view your own orders');
        }

        return $this->render('order/show.html.twig', [
            'order' => $order,
        ]);
    }

    #[Route('/{id}/cancel', name: 'app_order_cancel', requirements: ['id' => '\d+'])]
    public function cancel(Order $order, EntityManagerInterface $entityManager): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');

        if ($order->getUser() !== $this->getUser()) {
            throw $this->createAccessDeniedException('You can only cancel your own orders');
        }

        if (!$order->canBeCancelled()) {
            $this->addFlash('error', 'This order cannot be cancelled');
            return $this->redirectToRoute('app_order_show', ['id' => $order->getId()]);
        }

        $order->setStatus(Order::STATUS_CANCELLED);
        
        $entityManager->persist($order);
        $entityManager->flush();

        $this->addFlash('success', 'Order cancelled successfully');

        return $this->redirectToRoute('app_order_show', ['id' => $order->getId()]);
    }
} 