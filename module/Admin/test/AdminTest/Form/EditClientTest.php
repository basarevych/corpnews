<?php

namespace AdminTest\Form;

use Zend\Test\PHPUnit\Controller\AbstractControllerTestCase;
use Admin\Form\EditClient as EditClientForm;

class EditClientQueryMock {
    public function getSingleScalarResult() {
        return 0;
    }
}

class EditClientTest extends AbstractControllerTestCase
{
    public function setUp()
    {
        $this->setApplicationConfig(require 'config/application.config.php');

        parent::setUp();

        $this->em = $this->getMockBuilder('Doctrine\ORM\EntityManager')
                         ->disableOriginalConstructor()
                         ->setMethods([ 'createQueryBuilder' ])
                         ->getMock();

        $this->qb = $this->getMockBuilder('Doctrine\ORM\QueryBuilder')
                         ->disableOriginalConstructor()
                         ->setMethods([ 'setParameter', 'getQuery' ])
                         ->getMock();

        $this->em->expects($this->any())
                 ->method('createQueryBuilder')
                 ->will($this->returnValue($this->qb));

        $this->qb->expects($this->any())
                 ->method('getQuery')
                 ->will($this->returnValue(new EditClientQueryMock()));

        $sl = $this->getApplicationServiceLocator();
        $sl->setAllowOverride(true);
        $sl->setService('Doctrine\ORM\EntityManager', $this->em);
    }

    public function testInvalidForm()
    {
        $form = new EditClientForm($this->em, 42);

        $input = [
        ];

        $form->setData($input);
        $valid = $form->isValid();

        $this->assertEquals(false, $valid, "Form should not be reported as valid");
        $this->assertGreaterThan(0, count($form->get('security')->getMessages()), "Security should have errors");
        $this->assertGreaterThan(0, count($form->get('id')->getMessages()), "ID should have errors");
        $this->assertGreaterThan(0, count($form->get('email')->getMessages()), "Email should have errors");
    }

    public function testValidForm()
    {
        $form = new EditClientForm($this->em, 42);

        $input = [
            'security' => $form->get('security')->getValue(),
            'id' => 42,
            'email' => ' email@example.com '
        ];

        $form->setData($input);
        $valid = $form->isValid();
        $output = $form->getData();

        $this->assertEquals(true, $valid, "Form should be reported as valid");
        $this->assertEquals('email@example.com', $output['email'], "Email should be trimmed");
    }
}
