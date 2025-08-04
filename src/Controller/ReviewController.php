<?php

namespace App\Controller;

use App\Entity\Review;
use App\Entity\Product;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;

#[Route('/reviews')]
class ReviewController extends AbstractController
{
    #[Route('/product/{id}', name: 'app_review_product', requirements: ['id' => '\d+'])]
    public function productReviews(Product $product): Response
    {
        $reviews = $product->getReviews()->filter(function($review) {
            return $review->isApproved();
        });

        return $this->render('review/product.html.twig', [
            'product' => $product,
            'reviews' => $reviews,
        ]);
    }

    #[Route('/new/{id}', name: 'app_review_new', requirements: ['id' => '\d+'])]
    public function new(Product $product, Request $request, EntityManagerInterface $entityManager): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');

        if ($request->isMethod('POST')) {
            $review = new Review();
            $review->setUser($this->getUser());
            $review->setProduct($product);
            $review->setRating($request->request->getInt('rating'));
            $review->setTitle($request->request->get('title'));
            $review->setComment($request->request->get('comment'));
            $review->setIsApproved(false); // Requires admin approval

            $entityManager->persist($review);
            $entityManager->flush();

            $this->addFlash('success', 'Review submitted successfully and is pending approval');

            return $this->redirectToRoute('app_product_show', ['id' => $product->getId()]);
        }

        return $this->render('review/form.html.twig', [
            'product' => $product,
        ]);
    }
} 