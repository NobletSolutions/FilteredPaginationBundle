<?php

namespace NS\FilteredPaginationBundle\Tests;

use \Symfony\Component\Form\AbstractType;
use \Symfony\Component\Form\FormBuilderInterface;
use \Symfony\Component\OptionsResolver\OptionsResolverInterface;

/**
 * Description of FilteredPaginationForm
 *
 * @author gnat
 */
class FilteredPaginationForm extends AbstractType
{
    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->setMethod('POST')
            ->add('date')
            ->add('amount')
            ->add('filter', 'submit', array('attr' => array('class' => 'btn btn-sm btn-success pull-right')))
            ->add('reset', 'submit', array('attr' => array('class' => 'btn btn-sm btn-info')))
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'NS\FilteredPaginationBundle\Tests\Filters\Payment',
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'FilteredPaginationForm';
    }
}
