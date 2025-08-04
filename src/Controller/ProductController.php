<?php

namespace App\Controller;

use App\Entity\Product;
use App\Entity\Category;
use App\Repository\ProductRepository;
use App\Repository\CategoryRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/products')]
class ProductController extends AbstractController
{
    #[Route('/', name: 'app_products')]
    public function index(Request $request, ProductRepository $productRepository, CategoryRepository $categoryRepository): Response
    {
        $page = $request->query->getInt('page', 1);
        $categoryId = $request->query->getInt('category');
        $search = $request->query->get('search');
        
        $categories = $categoryRepository->findActiveCategories();
        
        if ($search) {
            $products = $productRepository->searchProducts($search);
        } elseif ($categoryId) {
            $products = $productRepository->findByCategory($categoryId);
        } else {
            $products = $productRepository->findActiveProducts();
        }

        return $this->render('product/index.html.twig', [
            'products' => $products,
            'categories' => $categories,
            'current_category' => $categoryId,
            'search' => $search,
        ]);
    }

    #[Route('/{id}', name: 'app_product_show', requirements: ['id' => '\d+'])]
    public function show(Product $product): Response
    {
        if (!$product->isActive()) {
            throw $this->createNotFoundException('Product not found');
        }

        return $this->render('product/show.html.twig', [
            'product' => $product,
        ]);
    }

    #[Route('/category/{id}', name: 'app_products_by_category', requirements: ['id' => '\d+'])]
    public function byCategory(Category $category, ProductRepository $productRepository): Response
    {
        $products = $productRepository->findByCategory($category->getId());

        return $this->render('product/category.html.twig', [
            'category' => $category,
            'products' => $products,
        ]);
    }
} 