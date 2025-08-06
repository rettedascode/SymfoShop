<?php

namespace App\Repository;

use App\Entity\Category;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Category>
 *
 * @method Category|null find($id, $lockMode = null, $lockVersion = null)
 * @method Category|null findOneBy(array $criteria, array $orderBy = null)
 * @method Category[]    findAll()
 * @method Category[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CategoryRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Category::class);
    }

    public function save(Category $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Category $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * Find active categories
     */
    public function findActiveCategories(): array
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.isActive = :active')
            ->setParameter('active', true)
            ->orderBy('c.name', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Find root categories (no parent)
     */
    public function findRootCategories(): array
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.parent IS NULL')
            ->andWhere('c.isActive = :active')
            ->setParameter('active', true)
            ->orderBy('c.name', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Find categories with products count
     */
    public function findCategoriesWithProductCount(): array
    {
        $qb = $this->createQueryBuilder('c')
            ->select('c')
            ->addSelect('COUNT(p.id) as productCount')
            ->leftJoin('c.products', 'p')
            ->andWhere('c.isActive = :active')
            ->setParameter('active', true)
            ->groupBy('c.id')
            ->orderBy('c.name', 'ASC');

        $results = $qb->getQuery()->getResult();
        
        // Transform the results to make productCount accessible
        $categories = [];
        foreach ($results as $result) {
            if (is_array($result)) {
                $category = $result[0];
                $productCount = $result['productCount'];
                $category->productCount = $productCount;
                $categories[] = $category;
            }
        }
        
        return $categories;
    }
} 