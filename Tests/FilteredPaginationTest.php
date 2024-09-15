<?php

namespace NS\FilteredPaginationBundle\Tests;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Knp\Component\Pager\PaginatorInterface;
use NS\FilteredPaginationBundle\Events\FilterEvent;
use NS\FilteredPaginationBundle\FilteredPagination;
use PHPUnit\Framework\MockObject\MockObject;
use Spiriit\Bundle\FormFilterBundle\Filter\FilterBuilderUpdaterInterface;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\PreloadedExtension;
use Symfony\Component\Form\Test\TypeTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;

class FilteredPaginationTest extends TypeTestCase
{
    const string TEST_KEY = 'filtered.pagination';

    public function testEmpty(): void
    {
        $this->queryBuilderUpdater
            ->expects(self::never())
            ->method('addFilterConditions');

        $query = new Query($this->entityMgr);
        $query->setDQL('SELECT s FROM NSFilteredPaginationBundle:Payment s');

        $this->paginator
            ->expects(self::once())
            ->method('paginate')
            ->with($query, 1, 10)
            ->willReturn($this->createMock(PaginationInterface::class));

        $filteredPagination = new FilteredPagination($this->paginator, $this->factory, $this->queryBuilderUpdater, $this->dispatcher);
        $session            = new Session(new MockArraySessionStorage());
        $request            = new Request();
        $request->setSession($session);

        $result = $filteredPagination->process($request, FilteredPaginationForm::class, $query, self::TEST_KEY);
        self::assertInstanceOf(Form::class, $result->getForm());
        self::assertInstanceOf(PaginationInterface::class, $result->getPagination());
        self::assertFalse($result->shouldRedirect());
        self::assertFalse($result->getDataWasFiltered());
    }

    public function testPostReset(): void
    {
        $this->queryBuilderUpdater
            ->expects(self::never())
            ->method('addFilterConditions');

        $query = new Query($this->entityMgr);
        $query->setDQL('SELECT s FROM NSFilteredPaginationBundle:Payment s');

        $this->paginator->expects(self::never())
            ->method('paginate')
            ->with($query, 1, 10);

        $formData = [
            'date'   => '',
            'amount' => '',
            'reset'  => '',
        ];

        $filteredPagination = new FilteredPagination($this->paginator, $this->factory, $this->queryBuilderUpdater, $this->dispatcher);
        $session            = new Session(new MockArraySessionStorage());
        $session->set(self::TEST_KEY, ['amount' => 10.00]);
        self::assertEquals(['amount' => 10.00], $session->get(self::TEST_KEY));
        $request = new Request();
        $request->request->set('FilteredPaginationForm', $formData);
        self::assertEquals($formData, $request->request->all('FilteredPaginationForm'));
        $request->setSession($session);

        $result = $filteredPagination->process($request, FilteredPaginationForm::class, $query, self::TEST_KEY);
        self::assertNull($result->getPagination());
        self::assertNotNull($result->getForm());
        self::assertTrue($result->shouldRedirect());
        self::assertFalse($result->getDataWasFiltered());
        self::assertNull($session->get(self::TEST_KEY));
        $form = $result->getForm();
        self::assertFalse($form->isSubmitted());
        self::assertNull($form['amount']->getData());
    }

    public function testGetReset(): void
    {
        $this->queryBuilderUpdater
            ->expects(self::never())
            ->method('addFilterConditions');

        $query = new Query($this->entityMgr);
        $query->setDQL('SELECT s FROM NSFilteredPaginationBundle:Payment s');

        $this->paginator
            ->method('paginate')
            ->with($query, 1, 10)
            ->willReturn($this->createMock(PaginationInterface::class));

        $formData = [
            'date'   => '',
            'amount' => 10.00,
            'reset'  => '',
        ];

        $filteredPagination = new FilteredPagination($this->paginator, $this->factory, $this->queryBuilderUpdater, $this->dispatcher);
        $session            = new Session(new MockArraySessionStorage());
        $session->set(self::TEST_KEY, ['amount' => 10.00]);
        self::assertEquals(['amount' => 10.00], $session->get(self::TEST_KEY));
        $request = new Request();
        $request->query->set('FilteredPaginationForm', $formData);
        self::assertEquals($formData, $request->query->all('FilteredPaginationForm'));
        $request->setSession($session);

        $result = $filteredPagination->process($request, FilteredPaginationForm::class, $query, self::TEST_KEY, ['method' => 'GET']);
        self::assertFalse($result->getDataWasFiltered());

        self::assertNotNull($result->getForm());
        $formConfig = $result->getForm()->getConfig();
        self::assertEquals('GET', $formConfig->getOption('method'));
        self::assertEquals('GET', $formConfig->getMethod());
        self::assertInstanceOf(PaginationInterface::class, $result->getPagination());
        self::assertFalse($result->shouldRedirect());
        self::assertEmpty($session->get(self::TEST_KEY));
        self::assertFalse($result->getForm()->isSubmitted());
        self::assertNull($result->getForm()->get('amount')->getData());
    }

    /**
     * @group submit
     */
    public function testSubmit(): void
    {
        $this->queryBuilderUpdater
            ->expects(self::once())
            ->method('addFilterConditions');

        $query = new Query($this->entityMgr);
        $query->setDQL('SELECT s FROM NSFilteredPaginationBundle:Payment s');

        $this->paginator
            ->expects(self::once())
            ->method('paginate')
            ->with($query, 1, 10)
            ->willReturn($this->createMock(PaginationInterface::class));

        $formData = [
            'date'   => '',
            'amount' => '12.30',
            'filter' => '',
        ];

        $filteredPagination = new FilteredPagination($this->paginator, $this->factory, $this->queryBuilderUpdater, $this->dispatcher);
        $session            = new Session(new MockArraySessionStorage());
        $session->set(self::TEST_KEY, 'something');
        self::assertEquals('something', $session->get(self::TEST_KEY));
        $request = new Request([], ['FilteredPaginationForm' => $formData]);
        self::assertEquals($formData, $request->request->all('FilteredPaginationForm'));
        $request->setSession($session);

        $result = $filteredPagination->process($request, FilteredPaginationForm::class, $query, self::TEST_KEY);
        self::assertTrue($result->getDataWasFiltered());

        self::assertInstanceOf(PaginationInterface::class, $result->getPagination());
        self::assertFalse($result->shouldRedirect());
        self::assertEquals($formData, $request->getSession()->get(self::TEST_KEY));
    }

    /**
     * @group submit
     */
    public function testSubmitPastPage1WillRedirect(): void
    {
        $this->queryBuilderUpdater
            ->expects(self::once())
            ->method('addFilterConditions');

        $query = new Query($this->entityMgr);
        $query->setDQL('SELECT s FROM NSFilteredPaginationBundle:Payment s');

        $this->paginator
            ->expects(self::never())
            ->method('paginate');

        $formData = [
            'date'   => '',
            'amount' => '12.30',
            'filter' => '',
        ];

        $filteredPagination = new FilteredPagination($this->paginator, $this->factory, $this->queryBuilderUpdater, $this->dispatcher);
        $session            = new Session(new MockArraySessionStorage());
        $session->set(self::TEST_KEY, 'something');
        self::assertEquals('something', $session->get(self::TEST_KEY));
        $request = new Request(['page' => 3], ['FilteredPaginationForm' => $formData]);
        self::assertEquals($formData, $request->request->all('FilteredPaginationForm'));
        $request->setSession($session);

        $result = $filteredPagination->process($request, FilteredPaginationForm::class, $query, self::TEST_KEY);
        self::assertTrue($result->getDataWasFiltered());
        self::assertTrue($result->shouldRedirect());

        self::assertNull($result->getPagination());
        self::asserttrue($result->shouldRedirect());
        self::assertEquals($formData, $request->getSession()->get(self::TEST_KEY));
    }

    public function testPerPage(): void
    {
        $filteredPagination = new FilteredPagination($this->paginator, $this->factory, $this->queryBuilderUpdater, $this->dispatcher);
        $session            = new Session(new MockArraySessionStorage());
        $request            = new Request();
        $request->setSession($session);

        self::assertEquals(10, $filteredPagination->getPerPage());
        self::assertFalse($session->has(self::TEST_KEY . '-limit'));

        $filteredPagination->updatePerPage($request, self::TEST_KEY);
        self::assertEquals(10, $filteredPagination->getPerPage());
        self::assertFalse($session->has(self::TEST_KEY . '-limit'));
    }

    public function testRequestPerPage(): void
    {
        $filteredPagination = new FilteredPagination($this->paginator, $this->factory, $this->queryBuilderUpdater, $this->dispatcher);
        $session            = new Session(new MockArraySessionStorage());
        $request            = new Request();
        $request->query->set('limit', 25);
        $request->setSession($session);

        self::assertEquals(10, $filteredPagination->getPerPage());
        self::assertFalse($session->has(self::TEST_KEY . '.limit'));

        $filteredPagination->updatePerPage($request, self::TEST_KEY);
        self::assertEquals(25, $filteredPagination->getPerPage());
        self::assertTrue($session->has(self::TEST_KEY . '.limit'));
        self::assertEquals(25, $session->get(self::TEST_KEY . '.limit'));
    }

    public function testSessionPerPage(): void
    {
        $limitKey = self::TEST_KEY . '.limit';

        $filteredPagination = new FilteredPagination($this->paginator, $this->factory, $this->queryBuilderUpdater, $this->dispatcher);
        $session            = new Session(new MockArraySessionStorage());
        $session->set($limitKey, 45);

        $request = new Request();
        $request->setSession($session);

        self::assertEquals(10, $filteredPagination->getPerPage());
        self::assertTrue($session->has(self::TEST_KEY . '.limit'));

        $filteredPagination->updatePerPage($request, self::TEST_KEY);
        self::assertEquals(45, $filteredPagination->getPerPage());
        self::assertTrue($session->has(self::TEST_KEY . '.limit'));
        self::assertEquals(45, $session->get(self::TEST_KEY . '.limit'));
    }

    private FilterBuilderUpdaterInterface|MockObject $queryBuilderUpdater;
    private PaginationInterface|MockObject $paginator;
    private EntityManagerInterface|MockObject $entityMgr;
    private FilterEvent|MockObject $event;

    protected function setUp(): void
    {
        parent::setUp();

        $this->queryBuilderUpdater = $this->createMock(FilterBuilderUpdaterInterface::class);
        $this->paginator           = $this->createMock(PaginatorInterface::class);

        $config          = new Configuration();
        $this->entityMgr = $this->createMock(EntityManagerInterface::class);

        $this->entityMgr
            ->method('getConfiguration')
            ->willReturn($config);

        $this->event = $this->createMock(FilterEvent::class);

        $this->event->method('hasNewQuery')->willReturn(false);

        $this->dispatcher
            ->method('dispatch')
            ->willReturn($this->event);
    }

    protected function getExtensions(): array
    {
        $type = new FilteredPaginationForm();
        return [new PreloadedExtension([$type], [])];
    }
}
