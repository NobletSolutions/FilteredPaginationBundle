<?php

namespace NS\FilteredPaginationBundle\Tests\Pagination;

use \Doctrine\ORM\Query;
use \NS\FilteredPaginationBundle\FilteredPagination;
use \NS\FilteredPaginationBundle\Tests\FilteredPaginationForm;
use \Symfony\Component\Form\Test\TypeTestCase;
use \Symfony\Component\HttpFoundation\Request;
use \Symfony\Component\HttpFoundation\Session\Session;
use \Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;

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
        $queryBuilderUpdater = $this->getMockBuilder('\Lexik\Bundle\FormFilterBundle\Filter\FilterBuilderUpdaterInterface')
            ->disableOriginalConstructor()
            ->getMock();

        $queryBuilderUpdater->expects($this->never())
            ->method('addFilterConditions');

        $router = $this->getMockBuilder('\Symfony\Component\Routing\RouterInterface')
            ->disableOriginalConstructor()
            ->getMock();

        $config = new \NS\FilteredPaginationBundle\Tests\Configuration();
        $entityMgr = $this->getMockBuilder('\Doctrine\ORM\EntityManager')
            ->disableOriginalConstructor()
            ->getMock();
        $entityMgr->expects($this->any())
            ->method('getConfiguration')
            ->willReturn($config);

        $query = new Query($entityMgr);
        $query->setDQL('SELECT s FROM NSFilteredPaginationBundle:Payment s');

        $paginator = $this->getMockBuilder('\Knp\Component\Pager\Paginator')
            ->disableOriginalConstructor()
            ->getMock();

        $paginator->expects($this->once())
            ->method('paginate')
            ->with($query, 1, 10)
            ->willReturn(array());

        $filteredPagination = new FilteredPagination($paginator, $this->factory, $queryBuilderUpdater, $router);
        $session            = new Session(new MockArraySessionStorage());
        $request            = new Request();
        $request->setSession($session);
        $formType           = new FilteredPaginationForm();

        list($form, $pagination, $redirect) = $filteredPagination->process($request, $formType, $query, self::TEST_KEY);
        $this->assertInstanceOf('Symfony\Component\Form\Form', $form);
        $this->assertEquals(array(), $pagination);
        $this->assertFalse($redirect);
    }

    public function testReset()
    {
        $queryBuilderUpdater = $this->getMockBuilder('\Lexik\Bundle\FormFilterBundle\Filter\FilterBuilderUpdaterInterface')
            ->disableOriginalConstructor()
            ->getMock();

        $queryBuilderUpdater->expects($this->never())
            ->method('addFilterConditions');

        $router = $this->getMockBuilder('\Symfony\Component\Routing\RouterInterface')
            ->disableOriginalConstructor()
            ->getMock();

        $config = new \NS\FilteredPaginationBundle\Tests\Configuration();
        $entityMgr = $this->getMockBuilder('\Doctrine\ORM\EntityManager')
            ->disableOriginalConstructor()
            ->getMock();
        $entityMgr->expects($this->any())
            ->method('getConfiguration')
            ->willReturn($config);

        $query = new Query($entityMgr);
        $query->setDQL('SELECT s FROM NSFilteredPaginationBundle:Payment s');

        $paginator = $this->getMockBuilder('\Knp\Component\Pager\Paginator')
            ->disableOriginalConstructor()
            ->getMock();

        $paginator->expects($this->never())
            ->method('paginate')
            ->with($query, 1, 10)
            ->willReturn(array());

        $formData = array(
            'date'   => '',
            'amount' => '',
            'reset'  => '',
        );

        $filteredPagination = new FilteredPagination($paginator, $this->factory, $queryBuilderUpdater, $router);
        $session            = new Session(new MockArraySessionStorage());
        $session->set(self::TEST_KEY, 'something');
        $this->assertEquals('something', $session->get(self::TEST_KEY));
        $request            = new Request();
        $request->request->set('FilteredPaginationForm', $formData);
        $this->assertEquals($formData, $request->request->get('FilteredPaginationForm'));
        $request->setSession($session);
        $formType           = new FilteredPaginationForm();
        $formOne            = $this->factory->create($formType);
        $this->assertEquals('FilteredPaginationForm', $formOne->getName());

        list($form, $pagination, $redirect) = $filteredPagination->process($request, $formType, $query, self::TEST_KEY);
        $this->assertNull($pagination);
        $this->assertNull($form);
        $this->assertTrue($redirect);
        $this->assertNull($session->get(self::TEST_KEY));
    }

    public function testSubmit()
    {
        $queryBuilderUpdater = $this->getMockBuilder('\Lexik\Bundle\FormFilterBundle\Filter\FilterBuilderUpdaterInterface')
            ->disableOriginalConstructor()
            ->getMock();

        $queryBuilderUpdater->expects($this->once())
            ->method('addFilterConditions');

        $router = $this->getMockBuilder('\Symfony\Component\Routing\RouterInterface')
            ->disableOriginalConstructor()
            ->getMock();

        $config = new \NS\FilteredPaginationBundle\Tests\Configuration();
        $entityMgr = $this->getMockBuilder('\Doctrine\ORM\EntityManager')
            ->disableOriginalConstructor()
            ->getMock();
        $entityMgr->expects($this->any())
            ->method('getConfiguration')
            ->willReturn($config);

        $query = new Query($entityMgr);
        $query->setDQL('SELECT s FROM NSFilteredPaginationBundle:Payment s');

        $paginator = $this->getMockBuilder('\Knp\Component\Pager\Paginator')
            ->disableOriginalConstructor()
            ->getMock();

        $paginator->expects($this->once())
            ->method('paginate')
            ->with($query, 1, 10)
            ->willReturn(array());

        $formData = array(
            'date'   => '',
            'amount' => '12.30',
            'filter' => '',
        );

        $filteredPagination = new FilteredPagination($paginator, $this->factory, $queryBuilderUpdater, $router);
        $session            = new Session(new MockArraySessionStorage());
        $session->set(self::TEST_KEY, 'something');
        $this->assertEquals('something', $session->get(self::TEST_KEY));
        $request            = new Request();
        $request->request->set('FilteredPaginationForm', $formData);
        $this->assertEquals($formData, $request->request->get('FilteredPaginationForm'));
        $request->setSession($session);
        $formType           = new FilteredPaginationForm();
        $formOne            = $this->factory->create($formType);
        $this->assertEquals('FilteredPaginationForm', $formOne->getName());

        list($form, $pagination, $redirect) = $filteredPagination->process($request, $formType, $query, self::TEST_KEY);
        $this->assertInstanceOf('Symfony\Component\Form\Form', $form);
        $this->assertEquals(array(), $pagination);
        $this->assertFalse($redirect);
        $this->assertEquals($formData, $request->getSession()->get(self::TEST_KEY));
    }
}
