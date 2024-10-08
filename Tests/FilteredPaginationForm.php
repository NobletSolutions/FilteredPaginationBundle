<?php

namespace NS\FilteredPaginationBundle\Tests;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use NS\FilteredPaginationBundle\Tests\Filters\Payment;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

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
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->setMethod($options['method'])
            ->add('date')
            ->add('amount')
            ->add('filter', SubmitType::class, ['attr' => ['class' => 'btn btn-sm btn-success pull-right']])
            ->add('reset', SubmitType::class, ['attr' => ['class' => 'btn btn-sm btn-info']]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Payment::class,
            'method'     => 'POST',
        ]);
    }

    public function getBlockPrefix(): string
    {
        return 'FilteredPaginationForm';
    }
}
