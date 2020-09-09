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

    /** @var bool */
    private $dataWasFiltered;

    public function __construct(FormInterface $form, ?PaginationInterface $pagination, ?bool $redirect, ?bool $dataWasFiltered = false)
    {
        $this->form = $form;
        $this->pagination = $pagination;
        if ($redirect !== null) {
            $this->redirect = $redirect;
        }

        $this->dataWasFiltered = $dataWasFiltered ?? false;
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
