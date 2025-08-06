<?php

namespace App\Controller;

use App\Repository\ProductRepository;
use App\Repository\CategoryRepository;
use App\Service\ConfigurationService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function index(
        ProductRepository $productRepository, 
        CategoryRepository $categoryRepository,
        ConfigurationService $configurationService
    ): Response {
        $featuredProducts = $productRepository->findFeaturedProducts(8);
        $categories = $categoryRepository->findRootCategories();
        $latestProducts = $productRepository->findActiveProducts();

        return $this->render('home/index.html.twig', [
            'featured_products' => $featuredProducts,
            'categories' => $categories,
            'latest_products' => array_slice($latestProducts, 0, 12),
        ]);
    }
} 