<?php

namespace NS\FilteredPaginationBundle\Tests\Pagination;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query;
use Knp\Component\Pager\Paginator;
use Lexik\Bundle\FormFilterBundle\Filter\FilterBuilderUpdaterInterface;
use NS\FilteredPaginationBundle\Events\FilterEvent;
use NS\FilteredPaginationBundle\FilteredPagination;
use NS\FilteredPaginationBundle\Tests\FilteredPaginationForm;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Form\PreloadedExtension;
use Symfony\Component\Form\Test\TypeTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;
use NS\FilteredPaginationBundle\Tests\Configuration;

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
        $this->queryBuilderUpdater
            ->expects($this->never())
            ->method('addFilterConditions');

        $query = new Query($this->entityMgr);
        $query->setDQL('SELECT s FROM NSFilteredPaginationBundle:Payment s');

        $this->paginator
            ->expects($this->once())
            ->method('paginate')
            ->with($query, 1, 10)
            ->willReturn(array());

        $filteredPagination = new FilteredPagination($this->paginator, $this->factory, $this->queryBuilderUpdater, $this->dispatcher);
        $session            = new Session(new MockArraySessionStorage());
        $request            = new Request();
        $request->setSession($session);

        list($form, $pagination, $redirect) = $filteredPagination->process($request, FilteredPaginationForm::class, $query, self::TEST_KEY);
        $this->assertInstanceOf('Symfony\Component\Form\Form', $form);
        $this->assertEquals(array(), $pagination);
        $this->assertFalse($redirect);
    }

    public function testPostReset()
    {
        $this->queryBuilderUpdater
            ->expects($this->never())
            ->method('addFilterConditions');

        $query = new Query($this->entityMgr);
        $query->setDQL('SELECT s FROM NSFilteredPaginationBundle:Payment s');

        $this->paginator->expects($this->never())
            ->method('paginate')
            ->with($query, 1, 10)
            ->willReturn(array());

        $formData = array(
            'date'   => '',
            'amount' => '',
            'reset'  => '',
        );

        $filteredPagination = new FilteredPagination($this->paginator, $this->factory, $this->queryBuilderUpdater, $this->dispatcher);
        $session            = new Session(new MockArraySessionStorage());
        $session->set(self::TEST_KEY, array('amount'=>10.00));
        $this->assertEquals(array('amount'=>10.00), $session->get(self::TEST_KEY));
        $request            = new Request();
        $request->request->set('FilteredPaginationForm', $formData);
        $this->assertEquals($formData, $request->request->get('FilteredPaginationForm'));
        $request->setSession($session);

        list($form, $pagination, $redirect) = $filteredPagination->process($request, FilteredPaginationForm::class, $query, self::TEST_KEY);
        $this->assertNull($pagination);
        $this->assertNotNull($form);
        $this->assertTrue($redirect);
        $this->assertNull($session->get(self::TEST_KEY));
        $this->assertFalse($form->isSubmitted());
        $this->assertNull($form['amount']->getData());
    }

    public function testGetReset()
    {
        $this->queryBuilderUpdater
            ->expects($this->never())
            ->method('addFilterConditions');

        $query = new Query($this->entityMgr);
        $query->setDQL('SELECT s FROM NSFilteredPaginationBundle:Payment s');

        $this->paginator->expects($this->any())
            ->method('paginate')
            ->with($query, 1, 10)
            ->willReturn(array());

        $formData = array(
            'date'   => '',
            'amount' => 10.00,
            'reset'  => '',
        );

        $filteredPagination = new FilteredPagination($this->paginator, $this->factory, $this->queryBuilderUpdater, $this->dispatcher);
        $session            = new Session(new MockArraySessionStorage());
        $session->set(self::TEST_KEY, array('amount'=>10.00));
        $this->assertEquals(array('amount'=>10.00), $session->get(self::TEST_KEY));
        $request            = new Request();
        $request->query->set('FilteredPaginationForm', $formData);
        $this->assertEquals($formData, $request->query->get('FilteredPaginationForm'));
        $request->setSession($session);

        list($form, $pagination, $redirect) = $filteredPagination->process($request, FilteredPaginationForm::class, $query, self::TEST_KEY, array('method'=>'GET'));

        $this->assertNotNull($form);
        $this->assertEquals('GET',$form->getConfig()->getOption('method'));
        $this->assertEquals('GET',$form->getConfig()->getMethod());
        $this->assertNotNull($pagination);
        $this->assertFalse($redirect);
        $this->assertEmpty($session->get(self::TEST_KEY));
        $this->assertFalse($form->isSubmitted());
        $this->assertNull($form['amount']->getData());
    }

    /**
     * @group submit
     */
    public function testSubmit()
    {
        $this->queryBuilderUpdater
            ->expects($this->once())
            ->method('addFilterConditions');

        $query = new Query($this->entityMgr);
        $query->setDQL('SELECT s FROM NSFilteredPaginationBundle:Payment s');

        $this->paginator
            ->expects($this->once())
            ->method('paginate')
            ->with($query, 1, 10)
            ->willReturn(array());

        $formData = array(
            'date'   => '',
            'amount' => '12.30',
            'filter' => '',
        );

        $filteredPagination = new FilteredPagination($this->paginator, $this->factory, $this->queryBuilderUpdater, $this->dispatcher);
        $session            = new Session(new MockArraySessionStorage());
        $session->set(self::TEST_KEY, 'something');
        $this->assertEquals('something', $session->get(self::TEST_KEY));
        $request            = new Request([], ['FilteredPaginationForm' => $formData]);
        $this->assertEquals($formData, $request->request->get('FilteredPaginationForm'));
        $request->setSession($session);

        list($form, $pagination, $redirect) = $filteredPagination->process($request, FilteredPaginationForm::class, $query, self::TEST_KEY);

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
        $filteredPagination = new FilteredPagination($this->paginator, $this->factory, $this->queryBuilderUpdater, $this->dispatcher);
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
        $filteredPagination = new FilteredPagination($this->paginator, $this->factory, $this->queryBuilderUpdater, $this->dispatcher);
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
        $limitKey = self::TEST_KEY.'.limit';

        $filteredPagination = new FilteredPagination($this->paginator, $this->factory, $this->queryBuilderUpdater, $this->dispatcher);
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

    private $queryBuilderUpdater;
    private $paginator;
    private $entityMgr;
    private $event;

    protected function setUp()
    {
        parent::setUp();

        $this->queryBuilderUpdater = $this->createMock(FilterBuilderUpdaterInterface::class);
        $this->paginator = $this->createMock(Paginator::class);

        $config = new Configuration();
        $this->entityMgr = $this->createMock(EntityManagerInterface::class);

        $this->entityMgr->expects($this->any())
            ->method('getConfiguration')
            ->willReturn($config);

        $this->event = $this->createMock(FilterEvent::class);

        $this->event->expects($this->any())->method('hasNewQuery')->willReturn(false);

        $this->dispatcher->expects($this->any())
            ->method('dispatch')
            ->willReturn($this->event);
    }

    protected function getExtensions()
    {
        $type = new FilteredPaginationForm();
        return [new PreloadedExtension([$type],[])];
    }
}
