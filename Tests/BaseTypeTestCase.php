<?php
/**
 * Created by PhpStorm.
 * User: gnat
 * Date: 17/02/17
 * Time: 9:45 AM
 */

namespace NS\FilteredPaginationBundle\Tests;

use Symfony\Component\Form\Test\TypeTestCase;

class BaseTypeTestCase extends TypeTestCase
{
    protected function createMock($originalClassName)
    {
        if (method_exists(TypeTestCase::class, 'createMock')) {
            return parent::createMock($originalClassName);
        }

        return $this->getMockBuilder($originalClassName)
            ->disableOriginalConstructor()
            ->disableOriginalClone()
            ->disableArgumentCloning()
            ->getMock();
    }

}
