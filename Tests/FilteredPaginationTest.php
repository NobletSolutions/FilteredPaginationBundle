<?php

namespace NS\FilteredPaginationBundle\Tests\Pagination;

use \Doctrine\ORM\Query;
use NS\FilteredPaginationBundle\Events\FilterEvent;
use \NS\FilteredPaginationBundle\FilteredPagination;
use \NS\FilteredPaginationBundle\Tests\FilteredPaginationForm;
use \Symfony\Component\Form\Test\TypeTestCase;
use \Symfony\Component\HttpFoundation\Request;
use \Symfony\Component\HttpFoundation\Session\Session;
use \Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;
use \NS\FilteredPaginationBundle\Tests\Configuration;

/**
 * Description of FilteredPaginationTest
 *
 * @author gnat
 */
class FilteredPaginationTest extends TypeTestCase
{
    const TEST_KEY = 'filtered.pagination';

    public function testEmpty()
    {
        list($queryBuilderUpdater, $paginator, $dispatcher, $entityMgr) = $this->getArguments(true);

        $queryBuilderUpdater
            ->expects($this->never())
            ->method('addFilterConditions');

        $query = new Query($entityMgr);
        $query->setDQL('SELECT s FROM NSFilteredPaginationBundle:Payment s');

        $paginator
            ->expects($this->once())
            ->method('paginate')
            ->with($query, 1, 10)
            ->willReturn(array());

        $filteredPagination = new FilteredPagination($paginator, $this->factory, $queryBuilderUpdater,$dispatcher);
        $session            = new Session(new MockArraySessionStorage());
        $request            = new Request();
        $request->setSession($session);

        list($form, $pagination, $redirect) = $filteredPagination->process($request, 'NS\FilteredPaginationBundle\Tests\FilteredPaginationForm', $query, self::TEST_KEY);
        $this->assertInstanceOf('Symfony\Component\Form\Form', $form);
        $this->assertEquals(array(), $pagination);
        $this->assertFalse($redirect);
    }

    public function testPostReset()
    {
        list($queryBuilderUpdater, $paginator, $dispatcher, $entityMgr) = $this->getArguments(true);

        $queryBuilderUpdater
            ->expects($this->never())
            ->method('addFilterConditions');

        $query = new Query($entityMgr);
        $query->setDQL('SELECT s FROM NSFilteredPaginationBundle:Payment s');

        $paginator->expects($this->never())
            ->method('paginate')
            ->with($query, 1, 10)
            ->willReturn(array());

        $formData = array(
            'date'   => '',
            'amount' => '',
            'reset'  => '',
        );

        $filteredPagination = new FilteredPagination($paginator, $this->factory, $queryBuilderUpdater,$dispatcher);
        $session            = new Session(new MockArraySessionStorage());
        $session->set(self::TEST_KEY, array('amount'=>10.00));
        $this->assertEquals(array('amount'=>10.00), $session->get(self::TEST_KEY));
        $request            = new Request();
        $request->request->set('FilteredPaginationForm', $formData);
        $this->assertEquals($formData, $request->request->get('FilteredPaginationForm'));
        $request->setSession($session);

        list($form, $pagination, $redirect) = $filteredPagination->process($request, 'NS\FilteredPaginationBundle\Tests\FilteredPaginationForm', $query, self::TEST_KEY);
        $this->assertNull($pagination);
        $this->assertNotNull($form);
        $this->assertTrue($redirect);
        $this->assertNull($session->get(self::TEST_KEY));
        $this->assertFalse($form->isSubmitted());
        $this->assertNull($form['amount']->getData());
    }

    public function testGetReset()
    {
        list($queryBuilderUpdater, $paginator, $dispatcher, $entityMgr) = $this->getArguments(true);

        $queryBuilderUpdater
            ->expects($this->never())
            ->method('addFilterConditions');

        $query = new Query($entityMgr);
        $query->setDQL('SELECT s FROM NSFilteredPaginationBundle:Payment s');

        $paginator->expects($this->any())
            ->method('paginate')
            ->with($query, 1, 10)
            ->willReturn(array());

        $formData = array(
            'date'   => '',
            'amount' => 10.00,
            'reset'  => '',
        );

        $filteredPagination = new FilteredPagination($paginator, $this->factory, $queryBuilderUpdater,$dispatcher);
        $session            = new Session(new MockArraySessionStorage());
        $session->set(self::TEST_KEY, array('amount'=>10.00));
        $this->assertEquals(array('amount'=>10.00), $session->get(self::TEST_KEY));
        $request            = new Request();
        $request->query->set('FilteredPaginationForm', $formData);
        $this->assertEquals($formData, $request->query->get('FilteredPaginationForm'));
        $request->setSession($session);

        list($form, $pagination, $redirect) = $filteredPagination->process($request, 'NS\FilteredPaginationBundle\Tests\FilteredPaginationForm', $query, self::TEST_KEY, array('method'=>'GET'));

        $this->assertNotNull($form);
        $this->assertEquals('GET',$form->getConfig()->getOption('method'));
        $this->assertEquals('GET',$form->getConfig()->getMethod());
        $this->assertNotNull($pagination);
        $this->assertFalse($redirect);
        $this->assertEmpty($session->get(self::TEST_KEY));
        $this->assertFalse($form->isSubmitted());
        $this->assertNull($form['amount']->getData());
    }

    public function testSubmit()
    {
        list($queryBuilderUpdater, $paginator, $dispatcher,$entityMgr) = $this->getArguments(true);

        $queryBuilderUpdater
            ->expects($this->once())
            ->method('addFilterConditions');

        $query = new Query($entityMgr);
        $query->setDQL('SELECT s FROM NSFilteredPaginationBundle:Payment s');

        $paginator
            ->expects($this->once())
            ->method('paginate')
            ->with($query, 1, 10)
            ->willReturn(array());

        $formData = array(
            'date'   => '',
            'amount' => '12.30',
            'filter' => '',
        );

        $filteredPagination = new FilteredPagination($paginator, $this->factory, $queryBuilderUpdater,$dispatcher);
        $session            = new Session(new MockArraySessionStorage());
        $session->set(self::TEST_KEY, 'something');
        $this->assertEquals('something', $session->get(self::TEST_KEY));
        $request            = new Request();
        $request->request->set('FilteredPaginationForm', $formData);
        $this->assertEquals($formData, $request->request->get('FilteredPaginationForm'));
        $request->setSession($session);

        list($form, $pagination, $redirect) = $filteredPagination->process($request, 'NS\FilteredPaginationBundle\Tests\FilteredPaginationForm', $query, self::TEST_KEY);

        $this->assertInstanceOf('Symfony\Component\Form\Form', $form);
        $this->assertEquals(array(), $pagination);
        $this->assertFalse($redirect);
        $this->assertEquals($formData, $request->getSession()->get(self::TEST_KEY));
    }

//    /**
//     * @param Request $request
//     * @param string $sessionKey
//     */
//    public function updatePerPage(Request $request, $sessionKey)
//    {
//        $limitSessionKey = sprintf('%s.limit',$sessionKey);
//        if ($request->request->getInt('limit')) {
//            $this->perPage = $request->request->getInt('limit');
//            $request->getSession()->set($limitSessionKey, $this->perPage);
//        } elseif ($request->getSession()->has($limitSessionKey)) {
//            $this->perPage = $request->getSession()->get($limitSessionKey,$this->perPage);
//        }
//    }

    public function testPerPage()
    {
        list($queryBuilderUpdater, $paginator, $dispatcher) = $this->getArguments(true);

        $filteredPagination = new FilteredPagination($paginator, $this->factory, $queryBuilderUpdater,$dispatcher);
        $session            = new Session(new MockArraySessionStorage());
        $request            = new Request();
        $request->setSession($session);

        $this->assertEquals(10,$filteredPagination->getPerPage());
        $this->assertFalse($session->has(self::TEST_KEY.'-limit'));

        $filteredPagination->updatePerPage($request,self::TEST_KEY);
        $this->assertEquals(10,$filteredPagination->getPerPage());
        $this->assertFalse($session->has(self::TEST_KEY.'-limit'));
    }

    public function testRequestPerPage()
    {
        list($queryBuilderUpdater, $paginator, $dispatcher) = $this->getArguments(true);

        $filteredPagination = new FilteredPagination($paginator, $this->factory, $queryBuilderUpdater, $dispatcher);
        $session            = new Session(new MockArraySessionStorage());
        $request            = new Request();
        $request->query->set('limit',25);
        $request->setSession($session);

        $this->assertEquals(10,$filteredPagination->getPerPage());
        $this->assertFalse($session->has(self::TEST_KEY.'.limit'));

        $filteredPagination->updatePerPage($request,self::TEST_KEY);
        $this->assertEquals(25,$filteredPagination->getPerPage());
        $this->assertTrue($session->has(self::TEST_KEY.'.limit'));
        $this->assertEquals(25,$session->get(self::TEST_KEY.'.limit'));
    }

    public function testSessionPerPage()
    {
        list($queryBuilderUpdater, $paginator, $dispatcher) = $this->getArguments(true);

        $limitKey = self::TEST_KEY.'.limit';

        $filteredPagination = new FilteredPagination($paginator, $this->factory, $queryBuilderUpdater, $dispatcher);
        $session            = new Session(new MockArraySessionStorage());
        $session->set($limitKey,45);

        $request            = new Request();
        $request->setSession($session);

        $this->assertEquals(10,$filteredPagination->getPerPage());
        $this->assertTrue($session->has(self::TEST_KEY.'.limit'));

        $filteredPagination->updatePerPage($request,self::TEST_KEY);
        $this->assertEquals(45,$filteredPagination->getPerPage());
        $this->assertTrue($session->has(self::TEST_KEY.'.limit'));
        $this->assertEquals(45,$session->get(self::TEST_KEY.'.limit'));
    }

    private function getArguments($simpleDispatcher = false)
    {
        $queryBuilderUpdater = $this->getMockBuilder('\Lexik\Bundle\FormFilterBundle\Filter\FilterBuilderUpdaterInterface')
            ->disableOriginalConstructor()
            ->getMock();

        $paginator = $this->getMockBuilder('\Knp\Component\Pager\Paginator')
            ->disableOriginalConstructor()
            ->getMock();

        $config = new Configuration();
        $entityMgr = $this->getMockBuilder('\Doctrine\ORM\EntityManager')
            ->disableOriginalConstructor()
            ->getMock();
        $entityMgr->expects($this->any())
            ->method('getConfiguration')
            ->willReturn($config);

        $dispatcher = $this->getMockBuilder('Symfony\Component\EventDispatcher\EventDispatcherInterface')
            ->disableOriginalConstructor()
            ->getMock();

        if($simpleDispatcher) {
            $event = $this->getMockBuilder('NS\FilteredPaginationBundle\Events\FilterEvent')
                ->disableOriginalConstructor()
                ->getMock();

            $event->expects($this->any())->method('hasQuery')->willReturn(false);

            $dispatcher->expects($this->any())
                ->method('dispatch')
                ->willReturn($event);
        }

        return array($queryBuilderUpdater,$paginator,$dispatcher, $entityMgr);
    }
}
