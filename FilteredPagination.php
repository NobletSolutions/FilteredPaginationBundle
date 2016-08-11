<?php

namespace NS\FilteredPaginationBundle;

use \Doctrine\ORM\Query;
use Knp\Component\Pager\Paginator;
use Lexik\Bundle\FormFilterBundle\Filter\FilterBuilderUpdaterInterface;
use NS\FilteredPaginationBundle\Events\FilterEvent;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use \Symfony\Component\Form\AbstractType;
use \Symfony\Component\Form\FormFactoryInterface;
use \Symfony\Component\HttpFoundation\Request;

/**
 * Description of FilteredPagination
 *
 * @author gnat
 */
class FilteredPagination
{
    /**
     * @var Paginator
     */
    private $paginator;

    /**
     * @var FormFactoryInterface
     */
    private $formFactory;

    /**
     * @var FilterBuilderUpdaterInterface
     */
    private $queryBuilderUpdater;

    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    /**
     * @var array
     */
    private $knpParams = array('pageParameterName' => 'page');

    /**
     * @var int
     */
    private $perPage = 10;

    /**
     *
     * @param Paginator $pager
     * @param FormFactoryInterface $formFactory
     * @param FilterBuilderUpdaterInterface $queryBuilderUpdater
     */
    public function __construct(Paginator $pager, FormFactoryInterface $formFactory, FilterBuilderUpdaterInterface $queryBuilderUpdater, EventDispatcherInterface $dispatcher)
    {
        $this->paginator = $pager;
        $this->formFactory = $formFactory;
        $this->queryBuilderUpdater = $queryBuilderUpdater;
        $this->eventDispatcher = $dispatcher;
    }

    /**
     *
     * @param Request $request
     * @param AbstractType|string $formType
     * @param Query $query
     * @param string $sessionKey
     * @param array $formOptions
     * @return array
     */
    public function process(Request $request, $formType, $query, $sessionKey, array $formOptions = array())
    {
        $filterForm = $this->formFactory->create($formType, null, $formOptions);
        $method = $filterForm->getConfig()->getMethod();
        $requestData = ($method == 'GET') ? $request->query->get($filterForm->getName()) : $request->request->get($filterForm->getName());

        if (isset($requestData['reset'])) {
            if ($method == 'POST') {
                $request->getSession()->remove($sessionKey);
                return array($filterForm, null, true);
            }

            $request->getSession()->set($sessionKey, array());
            $requestData = array();
        }

        $filterData = (empty($requestData)) ? $request->getSession()->get($sessionKey, $requestData) : $requestData;
        if (!empty($filterData)) {
            $filterForm->submit($filterData);

            if ($filterForm->isSubmitted()) {
                if ($filterForm->isValid()) {
                    if (empty($filterData)) {
                        $request->getSession()->remove($sessionKey);
                    } else {
                        $request->getSession()->set($sessionKey, $filterData);
                    }

                    $this->eventDispatcher->dispatch(FilterEvent::PRE_FILTER, new FilterEvent($query));

                    $this->queryBuilderUpdater->addFilterConditions($filterForm, $query);
                }
            }
        }

        $this->updatePerPage($request,$sessionKey);

        $page  = $request->query->get($this->knpParams['pageParameterName'], 1);
        $event = $this->eventDispatcher->dispatch(FilterEvent::POST_FILTER, new FilterEvent($query));
        if ($event->hasNewQuery()) {
            $query = $event->getNewQuery();
        }

        return array($filterForm, $this->paginator->paginate($query, $page, $this->perPage, $this->knpParams), false);
    }

    /**
     * @param Request $request
     * @param string $sessionKey
     */
    public function updatePerPage(Request $request, $sessionKey)
    {
        $limitSessionKey = sprintf('%s.limit',$sessionKey);
        if ($request->query->getInt('limit')) {
            $this->perPage = $request->query->getInt('limit');
            $request->getSession()->set($limitSessionKey, $this->perPage);
        } elseif ($request->getSession()->has($limitSessionKey)) {
            $this->perPage = $request->getSession()->get($limitSessionKey,$this->perPage);
        }
    }

    /**
     * @param int $perPage
     * @return FilteredPagination
     */
    public function setPerPage($perPage)
    {
        $this->perPage = $perPage;
        return $this;
    }

    /**
     * @return int
     */
    public function getPerPage()
    {
        return $this->perPage;
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

    /**
     * @param $key
     * @param $value
     */
    public function addKnpParam($key, $value)
    {
        $this->knpParams[$key] = $value;
    }
}
