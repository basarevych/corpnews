<?php

namespace AdminTest\Controller;

use Zend\Http\Request as HttpRequest;
use Zend\Dom\Query;
use Zend\Test\PHPUnit\Controller\AbstractHttpControllerTestCase;
use Application\Entity\Setting as SettingEntity;

class SettingControllerTest extends AbstractHttpControllerTestCase
{
    public function setUp()
    {
        $this->setApplicationConfig(require 'config/application.config.php');

        parent::setUp();

        $sl = $this->getApplicationServiceLocator();
        $session = $sl->get('Session');
        $cnt = $session->getContainer();
        $cnt->is_admin = true;

        $this->em = $this->getMockBuilder('Doctrine\ORM\EntityManager')
                         ->disableOriginalConstructor()
                         ->setMethods([ 'getRepository', 'persist', 'flush' ])
                         ->getMock();

        $this->repoSetting = $this->getMockBuilder('Application\Entity\SettingRepository')
                                  ->disableOriginalConstructor()
                                  ->setMethods([ 'findOneByName' ])
                                  ->getMock();

        $this->em->expects($this->any())
                 ->method('getRepository')
                 ->will($this->returnValueMap([
                    [ 'Application\Entity\Setting', $this->repoSetting ],
                ]));

        $sl->setAllowOverride(true);
        $sl->setService('Doctrine\ORM\EntityManager', $this->em);
    }


    public function testMailboxFormActionCanBeAccessed()
    {
        $setting = new SettingEntity();
        $setting->setName('MailboxAutodelete');
        $setting->setType(SettingEntity::TYPE_INTEGER);
        $setting->setValueInteger(1);

        $this->repoSetting->expects($this->any())
                          ->method('findOneByName')
                          ->will($this->returnValue($setting));

        $this->dispatch('/admin/setting/mailbox-form');
        $this->assertResponseStatusCode(200);

        $this->assertModuleName('admin');
        $this->assertControllerName('admin\controller\setting');
        $this->assertControllerClass('SettingController');
        $this->assertMatchedRouteName('admin');
    }

    public function testMailboxFormActionCreatesEntity()
    {
        $setting = new SettingEntity();
        $setting->setName('MailboxAutodelete');
        $setting->setType(SettingEntity::TYPE_INTEGER);
        $setting->setValueInteger(1);

        $this->repoSetting->expects($this->any())
                          ->method('findOneByName')
                          ->will($this->returnValue($setting));

        $this->dispatch('/admin/setting/mailbox-form');
        $this->assertResponseStatusCode(200);

        $response = $this->getResponse();
        $dom = new Query($response->getContent());
        $result = $dom->execute('input[name="security"]');
        $security = count($result) ? $result[0]->getAttribute('value') : null;

        $dt = new \DateTime();
        $postParams = [
            'security' => $security,
            'autodelete' => 123,
        ];

        $persisted = null;
        $this->em->expects($this->any())
                 ->method('persist')
                 ->will($this->returnCallback(function ($entity) use (&$persisted) {
                    $persisted = $entity;
                 }));

        $this->dispatch('/admin/setting/mailbox-form', HttpRequest::METHOD_POST, $postParams);
        $this->assertResponseStatusCode(200);

        $this->assertEquals(true, $persisted instanceof SettingEntity, "Setting entity was not created");
        $this->assertEquals('MailboxAutodelete', $persisted->getName(), "MailboxAutodelete was not created");
        $this->assertEquals(123, $persisted->getValueInteger(), "MailboxAutodelete has incorrect value");
    }
}
