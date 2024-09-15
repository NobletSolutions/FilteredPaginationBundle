<?php

namespace NS\FilteredPaginationBundle;

use Knp\Component\Pager\Pagination\PaginationInterface;
use Symfony\Component\Form\FormInterface;

class FilteredPaginationResult
{
    private FormInterface $form;

    private ?PaginationInterface $pagination = null;

    private bool $redirect = false;

    private bool $dataWasFiltered = false;

    public function __construct(FormInterface $form, ?PaginationInterface $pagination, ?bool $redirect, ?bool $dataWasFiltered = false)
    {
        $this->form = $form;
        $this->pagination = $pagination;
        if ($redirect !== null) {
            $this->redirect = $redirect;
        }

        if ($dataWasFiltered !== null) {
            $this->dataWasFiltered = $dataWasFiltered;
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

    public function getDataWasFiltered(): bool
    {
        return $this->dataWasFiltered;
    }
}
