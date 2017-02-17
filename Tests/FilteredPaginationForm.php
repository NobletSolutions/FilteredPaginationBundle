<?php

namespace NS\FilteredPaginationBundle\Tests;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

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
            ->setMethod($options['method'])
            ->add('date')
            ->add('amount')
            ->add('filter', 'Symfony\Component\Form\Extension\Core\Type\SubmitType', array('attr' => array('class' => 'btn btn-sm btn-success pull-right')))
            ->add('reset', 'Symfony\Component\Form\Extension\Core\Type\SubmitType', array('attr' => array('class' => 'btn btn-sm btn-info')))
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'NS\FilteredPaginationBundle\Tests\Filters\Payment',
            'method'=>'POST',
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $this->configureOptions($resolver);
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'FilteredPaginationForm';
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return $this->getBlockPrefix();
    }
}
