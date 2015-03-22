<?php

namespace AdminTest\Form;

use Zend\Test\PHPUnit\Controller\AbstractControllerTestCase;
use Admin\Form\TestCampaign as TestCampaignForm;
use Application\Entity\Client as ClientEntity;

class TestCampaignTest extends AbstractControllerTestCase
{
    public function setUp()
    {
        $this->setApplicationConfig(require 'config/application.config.php');

        parent::setUp();

        $this->em = $this->getMockBuilder('Doctrine\ORM\EntityManager')
                         ->disableOriginalConstructor()
                         ->setMethods([ 'getRepository', 'createQueryBuilder' ])
                         ->getMock();

        $this->repoClients = $this->getMockBuilder('Application\Entity\GroupRepository')
                                  ->disableOriginalConstructor()
                                  ->setMethods([ 'findByGroupName' ])
                                  ->getMock();

        $this->client = new ClientEntity();
        $this->client->setEmail('foo@bar');

        $this->repoClients->expects($this->any())
                          ->method('findByGroupName')
                          ->will($this->returnValue([ $this->client ]));

        $this->em->expects($this->any())
                 ->method('getRepository')
                 ->will($this->returnValueMap([
                    [ 'Application\Entity\Client', $this->repoClients ],
                 ]));

        $this->sl = $this->getApplicationServiceLocator();
        $this->sl->setAllowOverride(true);
        $this->sl->setService('Doctrine\ORM\EntityManager', $this->em);
    }

    public function testInvalidForm()
    {
        $form = new TestCampaignForm($this->sl);

        $input = [
        ];

        $form->setData($input);
        $valid = $form->isValid();

        $this->assertEquals(false, $valid, "Form should not be reported as valid");
        $this->assertGreaterThan(0, count($form->get('security')->getMessages()), "Security should have errors");
        $this->assertGreaterThan(0, count($form->get('id')->getMessages()), "ID should have errors");
        $this->assertGreaterThan(0, count($form->get('tester')->getMessages()), "Tester should have errors");
        $this->assertGreaterThan(0, count($form->get('send_to')->getMessages()), "SendTo should have errors");
    }

    public function testValidForm()
    {
        $form = new TestCampaignForm($this->sl);

        $input = [
            'security' => $form->get('security')->getValue(),
            'id' => 42,
            'tester' => 'foo@bar',
            'send_to' => ' foo@bar ',
        ];

        $form->setData($input);
        $valid = $form->isValid();
        $output = $form->getData();

        $this->assertEquals(true, $valid, "Form should be reported as valid");
        $this->assertEquals(42, $output['id'], "Incorrect ID");
        $this->assertEquals('foo@bar', $output['send_to'], "Name should be trimmed");
    }
}
