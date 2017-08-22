<?php
/**
 * Created by PhpStorm.
 * User: gnat
 * Date: 22/08/17
 * Time: 1:27 PM
 */

namespace NS\FilteredPaginationBundle;

use Knp\Component\Pager\Pagination\PaginationInterface;
use Symfony\Component\Form\FormInterface;

class FilteredPaginationResult
{
    /** @var FormInterface */
    private $form;

    /** @var PaginationInterface */
    private $pagination;

    /** @var bool|null */
    private $redirect = false;

    /**
     * FilteredPaginationResult constructor.
     * @param FormInterface $form
     * @param null|PaginationInterface $pagination
     * @param null|bool $redirect
     */
    public function __construct(FormInterface $form, ?PaginationInterface $pagination, ?bool $redirect)
    {
        $this->form = $form;
        $this->pagination = $pagination;
        if ($redirect !== null) {
            $this->redirect = $redirect;
        }
    }

    /**
     * @return FormInterface
     */
    public function getForm()
    {
        return $this->form;
    }

    /**
     * @return PaginationInterface
     */
    public function getPagination()
    {
        return $this->pagination;
    }

    /**
     * @return bool|null
     */
    public function getRedirect()
    {
        return $this->redirect;
    }
}
