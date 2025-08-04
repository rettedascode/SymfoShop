<?php

namespace App\Controller;

use App\Repository\OrderRepository;
use App\Repository\ProductRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/admin')]
class AdminController extends AbstractController
{
    #[Route('/', name: 'app_admin')]
    public function index(OrderRepository $orderRepository, ProductRepository $productRepository, UserRepository $userRepository): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $recentOrders = $orderRepository->findRecentOrders(5);
        $lowStockProducts = $productRepository->findLowStockProducts(10);
        $orderStats = $orderRepository->getOrderStatistics();

        return $this->render('admin/index.html.twig', [
            'recent_orders' => $recentOrders,
            'low_stock_products' => $lowStockProducts,
            'order_stats' => $orderStats,
        ]);
    }

    #[Route('/orders', name: 'app_admin_orders')]
    public function orders(OrderRepository $orderRepository): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $orders = $orderRepository->findAll();

        return $this->render('admin/orders.html.twig', [
            'orders' => $orders,
        ]);
    }

    #[Route('/products', name: 'app_admin_products')]
    public function products(ProductRepository $productRepository): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $products = $productRepository->findAll();

        return $this->render('admin/products.html.twig', [
            'products' => $products,
        ]);
    }

    #[Route('/users', name: 'app_admin_users')]
    public function users(UserRepository $userRepository): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $users = $userRepository->findAll();

        return $this->render('admin/users.html.twig', [
            'users' => $users,
        ]);
    }
} 