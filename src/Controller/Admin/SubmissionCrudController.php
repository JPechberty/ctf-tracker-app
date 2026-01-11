<?php

namespace App\Controller\Admin;

use App\Entity\Submission;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Filter\BooleanFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\EntityFilter;

class SubmissionCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Submission::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Submission')
            ->setEntityLabelInPlural('Submissions')
            ->setDefaultSort(['submittedAt' => 'DESC']);
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->disable(Action::NEW, Action::EDIT, Action::DELETE);
    }

    public function configureFields(string $pageName): iterable
    {
        yield IdField::new('id')->hideOnForm();
        yield AssociationField::new('team', 'Equipe');
        yield AssociationField::new('flag', 'Flag');
        yield TextField::new('submittedValue', 'Valeur soumise');
        yield BooleanField::new('success', 'Succes')->renderAsSwitch(false);
        yield DateTimeField::new('submittedAt', 'Date de soumission');
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add(EntityFilter::new('team', 'Equipe'))
            ->add(EntityFilter::new('flag', 'Flag'))
            ->add(BooleanFilter::new('success', 'Succes'));
    }
}
