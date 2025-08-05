<?php

namespace App\Controller\Admin;

use App\Entity\Order;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\MoneyField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class OrderCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Order::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Order')
            ->setEntityLabelInPlural('Orders')
            ->setSearchFields(['orderNumber', 'user.email', 'user.firstName', 'user.lastName'])
            ->setDefaultSort(['createdAt' => 'DESC'])
            ->setPaginatorPageSize(20);
    }

    public function configureFields(string $pageName): iterable
    {
        yield IdField::new('id')->hideOnForm();
        yield TextField::new('orderNumber');
        yield AssociationField::new('user');
        yield MoneyField::new('subtotal')->setCurrency('USD');
        yield MoneyField::new('tax')->setCurrency('USD')->hideOnIndex();
        yield MoneyField::new('shipping')->setCurrency('USD')->hideOnIndex();
        yield MoneyField::new('total')->setCurrency('USD');
        yield ChoiceField::new('status')
            ->setChoices([
                'Pending' => 'pending',
                'Processing' => 'processing',
                'Shipped' => 'shipped',
                'Delivered' => 'delivered',
                'Cancelled' => 'cancelled'
            ]);
        yield TextField::new('paymentMethod')->hideOnIndex();
        yield TextField::new('shippingMethod')->hideOnIndex();
        yield DateTimeField::new('createdAt')->hideOnForm();
        yield DateTimeField::new('updatedAt')->hideOnForm();
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->add(Crud::PAGE_INDEX, Action::DETAIL)
            ->update(Crud::PAGE_INDEX, Action::NEW, function (Action $action) {
                return $action->setLabel('Add Order');
            });
    }
} 