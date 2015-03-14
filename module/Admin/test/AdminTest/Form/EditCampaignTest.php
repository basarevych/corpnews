<?php

namespace AdminTest\Form;

use Zend\Test\PHPUnit\Controller\AbstractControllerTestCase;
use Admin\Form\EditCampaign as EditCampaignForm;
use Application\Entity\Campaign as CampaignEntity;
use Application\Entity\Group as GroupEntity;

class EditCampaignQueryMock {
    public function getSingleScalarResult() {
        return 0;
    }
}

class EditCampaignTest extends AbstractControllerTestCase
{
    public function setUp()
    {
        $this->setApplicationConfig(require 'config/application.config.php');

        parent::setUp();

        $this->em = $this->getMockBuilder('Doctrine\ORM\EntityManager')
                         ->disableOriginalConstructor()
                         ->setMethods([ 'getRepository', 'createQueryBuilder' ])
                         ->getMock();

        $this->repoGroups = $this->getMockBuilder('Application\Entity\GroupRepository')
                                 ->disableOriginalConstructor()
                                 ->setMethods([ 'findBy' ])
                                 ->getMock();

        $this->group = new GroupEntity();
        $this->group->setName('group');

        $reflection = new \ReflectionClass(get_class($this->group));
        $property = $reflection->getProperty('id');
        $property->setAccessible(true);
        $property->setValue($this->group, 9000);

        $this->repoGroups->expects($this->any())
                         ->method('findBy')
                         ->will($this->returnValue([ $this->group ]));

        $this->em->expects($this->any())
                 ->method('getRepository')
                 ->will($this->returnValueMap([
                    [ 'Application\Entity\Group', $this->repoGroups ],
                 ]));

        $this->qb = $this->getMockBuilder('Doctrine\ORM\QueryBuilder')
                         ->disableOriginalConstructor()
                         ->setMethods([ 'setParameter', 'getQuery' ])
                         ->getMock();

        $this->em->expects($this->any())
                 ->method('createQueryBuilder')
                 ->will($this->returnValue($this->qb));

        $this->qb->expects($this->any())
                 ->method('getQuery')
                 ->will($this->returnValue(new EditCampaignQueryMock()));

        $sl = $this->getApplicationServiceLocator();
        $sl->setAllowOverride(true);
        $sl->setService('Doctrine\ORM\EntityManager', $this->em);
    }

    public function testInvalidForm()
    {
        $form = new EditCampaignForm($this->em);

        $input = [
        ];

        $form->setData($input);
        $valid = $form->isValid();

        $this->assertEquals(false, $valid, "Form should not be reported as valid");
        $this->assertGreaterThan(0, count($form->get('security')->getMessages()), "Security should have errors");
        $this->assertGreaterThan(0, count($form->get('name')->getMessages()), "Name should have errors");
        $this->assertGreaterThan(0, count($form->get('groups')->getMessages()), "Groups should have errors");
    }

    public function testValidForm()
    {
        $form = new EditCampaignForm($this->em);
        $dt = new \DateTime();
        $format = $form->get('when_deadline')->getFormat();

        $input = [
            'security' => $form->get('security')->getValue(),
            'name' => ' example ',
            'when_deadline' => " " . $dt->format($format),
            'groups' => [ 9000 ],
        ];

        $form->setData($input);
        $valid = $form->isValid();
        $output = $form->getData();

        $this->assertEquals(true, $valid, "Form should be reported as valid");
        $this->assertEquals('example', $output['name'], "Name should be trimmed");
        $this->assertEquals($dt->format($format), $output['when_deadline'], "Deadline should be trimmed and converted");
        $this->assertEquals([ 9000 ], $output['groups'], "Name should be trimmed");
    }
}
