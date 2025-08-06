<?php

namespace App\Controller;

use App\Repository\CategoryRepository;
use App\Repository\ProductRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/categories')]
class CategoryController extends AbstractController
{
    #[Route('/', name: 'app_categories')]
    public function index(CategoryRepository $categoryRepository, ProductRepository $productRepository): Response
    {
        $categories = $categoryRepository->findCategoriesWithProductCount();
        $rootCategories = $categoryRepository->findRootCategories();

        return $this->render('category/index.html.twig', [
            'categories' => $categories,
            'root_categories' => $rootCategories,
        ]);
    }

    #[Route('/{id}', name: 'app_category_show', requirements: ['id' => '\d+'])]
    public function show(int $id, CategoryRepository $categoryRepository, ProductRepository $productRepository): Response
    {
        $category = $categoryRepository->find($id);
        
        if (!$category || !$category->isActive()) {
            throw $this->createNotFoundException('Category not found');
        }

        $products = $productRepository->findByCategory($id);
        $subcategories = $categoryRepository->findBy(['parent' => $category, 'isActive' => true]);

        return $this->render('category/show.html.twig', [
            'category' => $category,
            'products' => $products,
            'subcategories' => $subcategories,
        ]);
    }
} 