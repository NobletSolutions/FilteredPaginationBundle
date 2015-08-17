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

    private $knpParams = array('pageParameterName' => 'page');

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
    public function process(Request $request, $formType, $query, $sessionKey, $perPage = 10, array $formOptions = array())
    {
        $filterForm  = $this->formFactory->create($formType,null,$formOptions);
        $requestData = ($filterForm->getConfig()->getMethod() == 'GET') ? $request->query->get($filterForm->getName()) : $request->request->get($filterForm->getName());

        if (isset($requestData['reset'])) {
            if($filterForm->getConfig()->getMethod() == 'POST') {
                $request->getSession()->remove($sessionKey);
                return array($filterForm, null, true);
            } else {
                $request->getSession()->set($sessionKey, array());
            }
        }

        $filterData = (empty($requestData)) ? $request->getSession()->get($sessionKey, $requestData) : $requestData;
        if (!empty($filterData)) {
            $filterForm->submit($filterData);

            if ($filterForm->isSubmitted()) {
                if ($filterForm->isValid()) {
                    if (empty($filterData)) {
                        $request->getSession()->remove($sessionKey);
                    }
                    else {
                        $request->getSession()->set($sessionKey, $filterData);
                    }

                    $this->queryBuilderUpdater->addFilterConditions($filterForm, $query);
                }
            }
        }

        $page = $request->query->get($this->knpParams['pageParameterName'], 1);

        return array($filterForm, $this->paginator->paginate($query, $page, $perPage, $this->knpParams),
            false);
    }

    /**
     * @param array $parts
     */
    public function setQueryBuilderParts(array $parts)
    {
        $this->queryBuilderUpdater->setParts($parts);
    }

    /**
     * @return array
     */
    public function getKnpParams()
    {
        return $this->knpParams;
    }

    /**
     *
     * @param array $knpParams
     * @return \NS\FilteredPaginationBundle\FilteredPagination
     */
    public function setKnpParams(array $knpParams)
    {
        $this->knpParams = $knpParams;
        return $this;
    }
}
