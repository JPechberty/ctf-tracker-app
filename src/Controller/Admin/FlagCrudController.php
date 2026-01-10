<?php

namespace App\Controller\Admin;

use App\Entity\Flag;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class FlagCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Flag::class;
    }

    public function configureFields(string $pageName): iterable
    {
        yield IdField::new('id')->hideOnForm();
        yield TextField::new('name', 'Nom');
        yield TextField::new('value', 'Valeur')
            ->setFormTypeOption('attr', ['autocomplete' => 'off'])
            ->hideOnIndex();
        yield IntegerField::new('points', 'Points');
        yield AssociationField::new('challenge', 'Challenge');
    }
}
