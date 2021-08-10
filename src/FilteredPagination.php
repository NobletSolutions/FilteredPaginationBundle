<?php

namespace NS\FilteredPaginationBundle;

use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use Knp\Component\Pager\PaginatorInterface;
use Lexik\Bundle\FormFilterBundle\Filter\FilterBuilderUpdaterInterface;
use NS\FilteredPaginationBundle\Events\FilterEvent;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormTypeInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class FilteredPagination implements FilteredPaginationInterface
{
    /** @var PaginatorInterface */
    private $paginator;

    /** @var FormFactoryInterface */
    private $formFactory;

    /** @var FilterBuilderUpdaterInterface */
    private $queryBuilderUpdater;

    /** @var EventDispatcherInterface */
    private $eventDispatcher;

    /** @var array */
    private $knpParams = ['pageParameterName' => 'page'];

    /** @var int */
    private $perPage = 10;

    public function __construct(PaginatorInterface $pager, FormFactoryInterface $formFactory, FilterBuilderUpdaterInterface $queryBuilderUpdater, EventDispatcherInterface $dispatcher)
    {
        $this->paginator           = $pager;
        $this->formFactory         = $formFactory;
        $this->queryBuilderUpdater = $queryBuilderUpdater;
        $this->eventDispatcher     = $dispatcher;
    }

    /**
     *
     * @param Request             $request
     * @param AbstractType|string $formType
     * @param Query|QueryBuilder  $query
     * @param string              $sessionKey
     * @param array               $formOptions
     *
     * @return FilteredPaginationResult
     */
    public function process(Request $request, $formType, $query, string $sessionKey, array $formOptions = []): FilteredPaginationResult
    {
        $returnValue = $this->handleForm($request, $formType, $sessionKey, $formOptions, $query);

        if ($returnValue[1]) {
            return new FilteredPaginationResult($returnValue[0], null, $returnValue[1], $returnValue[2] ?? false);
        }

        $page  = $request->query->get($this->knpParams['pageParameterName'], 1);
        $event = $this->eventDispatcher->dispatch(new FilterEvent($query), FilterEvent::POST_FILTER);
        if ($event->hasNewQuery()) {
            $query = $event->getNewQuery();
        }

        return new FilteredPaginationResult($returnValue[0], $this->paginator->paginate($query, $page, $this->perPage, $this->knpParams), false, $returnValue[2]);
    }

    /**
     * @param Request                 $request
     * @param                         $formType
     * @param string                  $sessionKey
     * @param array                   $formOptions
     * @param QueryBuilder|Query|null $query
     *
     * @return array
     */
    public function handleForm(Request $request, $formType, string $sessionKey, array $formOptions = [], $query = null): array
    {
        /** @var FormTypeInterface $filterForm */
        $filterForm  = $this->formFactory->create($formType, null, $formOptions);
        $method      = $filterForm->getConfig()->getMethod();
        $formName    = method_exists($filterForm, 'getName') ? $filterForm->getName() : $filterForm->getBlockPrefix();
        $requestData = ($method === 'GET') ? $request->query->get($formName) : $request->request->get($formName);

        if (isset($requestData['reset'])) {
            if ($method === 'POST') {
                $request->getSession()->remove($sessionKey);
                return [$filterForm, true, false];
            }

            $request->getSession()->set($sessionKey, []);
            $requestData = [];
        }

        $haveData       = false;
        $page           = $request->query->get('page');
        /**
         * If we have data and are on a page past page 1 we need to redirect back in case we're past the end of the number of pages of data/results
         */
        $shouldRedirect = !empty($requestData) && $page && $page > 1;
        $filterData     = empty($requestData) ? $request->getSession()->get($sessionKey, $requestData) : $requestData;
        if (!empty($filterData)) {
            $haveData = true;
            $this->applyFilter($filterForm, $filterData, $query);
            $request->getSession()->set($sessionKey, $filterData);
        }

        $this->updatePerPage($request, $sessionKey);


        return [$filterForm, $shouldRedirect, $haveData];
    }

    /**
     * @param FormInterface      $form
     * @param                    $filterData
     * @param Query|QueryBuilder $query
     */
    public function applyFilter(FormInterface $form, $filterData, $query): void
    {
        $form->submit($filterData);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->eventDispatcher->dispatch(new FilterEvent($query), FilterEvent::PRE_FILTER);

            $this->queryBuilderUpdater->addFilterConditions($form, $query);
        }
    }

    public function updatePerPage(Request $request, string $sessionKey): void
    {
        $limitSessionKey = sprintf('%s.limit', $sessionKey);
        if ($request->query->getInt('limit')) {
            $this->perPage = $request->query->getInt('limit');
            $request->getSession()->set($limitSessionKey, $this->perPage);
        } elseif ($request->getSession()->has($limitSessionKey)) {
            $this->perPage = $request->getSession()->get($limitSessionKey, $this->perPage);
        }
    }

    public function setPerPage(int $perPage): void
    {
        $this->perPage = $perPage;
    }

    public function getPerPage(): int
    {
        return $this->perPage;
    }

    public function setQueryBuilderParts(array $parts): void
    {
        $this->queryBuilderUpdater->setParts($parts);
    }

    public function getKnpParams(): array
    {
        return $this->knpParams;
    }

    public function setKnpParams(array $knpParams): void
    {
        $this->knpParams = $knpParams;
    }

    /**
     * @param mixed $key
     * @param mixed $value
     */
    public function addKnpParam($key, $value): void
    {
        $this->knpParams[$key] = $value;
    }
}
