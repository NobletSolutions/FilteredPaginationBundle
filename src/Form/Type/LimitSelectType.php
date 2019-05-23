<?php

namespace NS\FilteredPaginationBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;

class LimitSelectType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->setMethod('GET')
            ->add('limit', ChoiceType::class, ['choices' => [10 => 10, 25 => 25, 50 => 50, 75 => 75, 100 => 100]]);
    }
}
