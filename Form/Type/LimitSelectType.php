<?php

namespace NS\FilteredPaginationBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

class LimitSelectType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->setMethod('GET')
            ->add('limit','choice',array('choices'=>array(10=>10,25=>25,50=>50,75=>75,100=>100)));
    }
}
