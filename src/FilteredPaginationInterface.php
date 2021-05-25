<?php

namespace NS\FilteredPaginationBundle;

use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;

interface FilteredPaginationInterface
{
    public function process(Request $request, $formType, $query, string $sessionKey, array $formOptions = []): FilteredPaginationResult;
    public function handleForm(Request $request, $formType, string $sessionKey, array $formOptions = [], $query = null): array;
    public function applyFilter(FormInterface $form, $filterData, $query): void;
    public function updatePerPage(Request $request, string $sessionKey): void;
    public function setPerPage(int $perPage): void;
    public function getPerPage(): int;
    public function setQueryBuilderParts(array $parts): void;

}