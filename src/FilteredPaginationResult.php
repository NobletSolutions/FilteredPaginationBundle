<?php

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

    public function __construct(FormInterface $form, ?PaginationInterface $pagination, ?bool $redirect)
    {
        $this->form = $form;
        $this->pagination = $pagination;
        if ($redirect !== null) {
            $this->redirect = $redirect;
        }
    }

    public function getForm(): FormInterface
    {
        return $this->form;
    }

    public function getPagination(): ?PaginationInterface
    {
        return $this->pagination;
    }

    public function shouldRedirect(): ?bool
    {
        return $this->redirect;
    }
}
