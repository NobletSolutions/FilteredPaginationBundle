<?php

namespace NS\FilteredPaginationBundle;

use \Doctrine\ORM\Query;
use \Symfony\Component\Form\AbstractType;
use \Symfony\Component\Form\FormFactoryInterface;
use \Symfony\Component\HttpFoundation\Request;
use \Symfony\Component\Routing\RouterInterface;

/**
 * Description of FilteredPagination
 *
 * @author gnat
 */
class FilteredPagination
{
    private $paginator;

    private $formFactory;

    private $queryBuilderUpdater;

    /**
     *
     * @param type $paginator
     * @param FormFactoryInterface $formFactory
     * @param type $queryBuilderUpdater
     * @param RouterInterface $router
     */
    public function __construct($paginator, FormFactoryInterface $formFactory, $queryBuilderUpdater, $router)
    {
        $this->paginator           = $paginator;
        $this->formFactory         = $formFactory;
        $this->queryBuilderUpdater = $queryBuilderUpdater;
        $this->router              = $router;
    }

    /**
     *
     * @param Request $request
     * @param AbstractType|string $formType
     * @param Query $query
     * @param string $sessionKey
     * @param integer $perPage
     * @return array
     */
    public function process(Request $request, $formType, $query, $sessionKey, $perPage = 10)
    {
        $filter      = $this->formFactory->create($formType);
        $requestData = $request->request->get($filter->getName());

        if (isset($requestData['reset'])) {
            $request->getSession()->remove($sessionKey);
            return array(null, null, true);
        }

        $filterData = (empty($requestData)) ? $request->getSession()->get($sessionKey, $requestData) : $requestData;
        $filter->submit($filterData);
        if ($filter->isSubmitted() && $filter->isValid()) {
            if (empty($filterData)) {
                $request->getSession()->remove($sessionKey);
            } else {
                $request->getSession()->set($sessionKey, $filterData);
            }
        }

        $this->queryBuilderUpdater->addFilterConditions($filter, $query);

        return array($filter, $this->paginator->paginate($query, $request->query->get('page', 1), $perPage), false);
    }
}