<?php

namespace NS\FilteredPaginationBundle;

use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;

interface FilteredPaginationInterface
{
    public function process(Request $request, AbstractType|string $formType, Query|QueryBuilder $query, string $sessionKey, array $formOptions = []): FilteredPaginationResult;
    public function handleForm(Request $request, AbstractType|string $formType, string $sessionKey, array $formOptions = [], Query|QueryBuilder|null $query = null): array;
    public function applyFilter(FormInterface $form, $filterData, Query|QueryBuilder $query): void;
    public function updatePerPage(Request $request, string $sessionKey): void;
    public function setPerPage(int $perPage): void;
    public function getPerPage(): int;
    public function setQueryBuilderParts(array $parts): void;

}
