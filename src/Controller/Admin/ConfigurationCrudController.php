<?php

namespace App\Controller\Admin;

use App\Entity\Configuration;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Filter\ChoiceFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\TextFilter;

class ConfigurationCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Configuration::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Configuration')
            ->setEntityLabelInPlural('Configurations')
            ->setSearchFields(['configKey', 'description', 'category'])
            ->setDefaultSort(['category' => 'ASC', 'configKey' => 'ASC'])
            ->setPageTitle('index', 'Shop Configuration')
            ->setPageTitle('new', 'Add Configuration')
            ->setPageTitle('edit', 'Edit Configuration')
            ->setHelp('index', 'Manage your shop configuration settings. Changes here will affect the entire application.')
            ->setHelp('new', 'Add a new configuration setting.')
            ->setHelp('edit', 'Modify an existing configuration setting.');
    }

    public function configureFields(string $pageName): iterable
    {
        yield IdField::new('id')->hideOnForm();
        
        yield TextField::new('configKey')
            ->setLabel('Configuration Key')
            ->setHelp('Unique identifier for this configuration (e.g., shop.name, products.per_page)')
            ->setFormTypeOption('attr', ['placeholder' => 'shop.name']);

        yield ChoiceField::new('dataType')
            ->setLabel('Data Type')
            ->setChoices([
                'String' => 'string',
                'Integer' => 'integer',
                'Boolean' => 'boolean',
                'JSON' => 'json',
                'Text' => 'text'
            ])
            ->setHelp('The data type of this configuration value');

        yield TextareaField::new('configValue')
            ->setLabel('Value')
            ->setHelp('The configuration value')
            ->setFormTypeOption('attr', ['rows' => 3]);

        yield TextField::new('description')
            ->setLabel('Description')
            ->setHelp('A brief description of what this configuration does')
            ->setFormTypeOption('attr', ['placeholder' => 'What does this setting control?']);

        yield ChoiceField::new('category')
            ->setLabel('Category')
            ->setChoices([
                'Shop' => 'shop',
                'Products' => 'products',
                'Orders' => 'orders',
                'Theme' => 'theme',
                'Email' => 'email',
                'Payment' => 'payment',
                'Security' => 'security',
                'System' => 'system'
            ])
            ->setHelp('Group related configurations together');

        yield BooleanField::new('isEditable')
            ->setLabel('Editable')
            ->setHelp('Whether this configuration can be modified through the admin interface');

        yield BooleanField::new('isPublic')
            ->setLabel('Public')
            ->setHelp('Whether this configuration is accessible in templates');

        yield DateTimeField::new('createdAt')
            ->setLabel('Created At')
            ->hideOnForm();

        yield DateTimeField::new('updatedAt')
            ->setLabel('Updated At')
            ->hideOnForm();
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->add(Crud::PAGE_INDEX, Action::DETAIL)
            ->update(Crud::PAGE_INDEX, Action::NEW, function (Action $action) {
                return $action->setLabel('Add Configuration');
            })
            ->update(Crud::PAGE_INDEX, Action::EDIT, function (Action $action) {
                return $action->setLabel('Edit');
            })
            ->update(Crud::PAGE_INDEX, Action::DELETE, function (Action $action) {
                return $action->setLabel('Delete');
            });
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add(TextFilter::new('configKey')->setLabel('Configuration Key'))
            ->add(ChoiceFilter::new('dataType')
                ->setChoices([
                    'String' => 'string',
                    'Integer' => 'integer',
                    'Boolean' => 'boolean',
                    'JSON' => 'json',
                    'Text' => 'text'
                ])
                ->setLabel('Data Type'))
            ->add(ChoiceFilter::new('category')
                ->setChoices([
                    'Shop' => 'shop',
                    'Products' => 'products',
                    'Orders' => 'orders',
                    'Theme' => 'theme',
                    'Email' => 'email',
                    'Payment' => 'payment',
                    'Security' => 'security',
                    'System' => 'system'
                ])
                ->setLabel('Category'))
            ->add(ChoiceFilter::new('isEditable')
                ->setChoices([
                    'Yes' => true,
                    'No' => false
                ])
                ->setLabel('Editable'))
            ->add(ChoiceFilter::new('isPublic')
                ->setChoices([
                    'Yes' => true,
                    'No' => false
                ])
                ->setLabel('Public'));
    }
} 