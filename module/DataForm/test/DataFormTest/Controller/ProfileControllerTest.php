<?php

namespace DataFormTest\Controller;

use Zend\Test\PHPUnit\Controller\AbstractHttpControllerTestCase;
use Zend\Http\Request as HttpRequest;
use DataForm\Document\Profile as ProfileDocument;

class ProfileControllerTest extends AbstractHttpControllerTestCase
{
    public function setUp()
    {
        \Locale::setDefault('en_US');

        $this->setApplicationConfig(require 'config/application.config.php');

        parent::setUp();

        $sl = $this->getApplicationServiceLocator();
        $session = $sl->get('Session');
        $cnt = $session->getContainer();
        $cnt->is_admin = true;

        $config = $sl->get('Config');
        $config['corpnews'] = [
            'data_forms' => [
                'profile' => [
                    'title'     => 'Profile',
                    'url'       => '/data-form/profile',
                    'document'  => 'DataForm\Document\Profile',
                    'form'      => 'DataForm\Form\Profile',
                ],
            ],
        ];

        $this->em = $this->getMockBuilder('Doctrine\ORM\EntityManager')
                         ->disableOriginalConstructor()
                         ->setMethods([ 'getRepository' ])
                         ->getMock();

        $this->clientEntityRepo = $this->getMockBuilder('Application\Entity\ClientRepository')
                                       ->disableOriginalConstructor()
                                       ->setMethods([ 'findOneByEmail' ])
                                       ->getMock();

        $this->em->expects($this->any())
                 ->method('getRepository')
                 ->will($this->returnValue($this->clientEntityRepo));

        $this->dm = $this->getMockBuilder('Doctrine\ODM\MongoDB\DocumentManager')
                         ->disableOriginalConstructor()
                         ->setMethods([ 'getRepository', 'persist', 'flush' ])
                         ->getMock();

        $this->profileDocumentRepo = $this->getMockBuilder('DataForm\Document\ProfileRepository')
                                     ->disableOriginalConstructor()
                                       ->setMethods([ 'find' ])
                                     ->getMock();

        $this->dm->expects($this->any())
                 ->method('getRepository')
                 ->will($this->returnValue($this->profileDocumentRepo));

        $this->dfm = $this->getMockBuilder('Application\Service\DataFormManager')
                          ->setMethods([ 'createClientDocuments', 'updateClientDocuments', 'deleteClientDocuments', 'deleteAllDocuments' ])
                          ->getMock();

        $this->dfm->setServiceLocator($sl);

        $sl->setAllowOverride(true);
        $sl->setService('Config', $config);
        $sl->setService('Doctrine\ORM\EntityManager', $this->em);
        $sl->setService('doctrine.documentmanager.odm_default', $this->dm);
        $sl->setService('DataFormManager', $this->dfm);
      }

    public function testIndexActionCanBeAccessed()
    {
        $this->dispatch('/data-form/profile');
        $this->assertResponseStatusCode(200);

        $this->assertModuleName('dataform');
        $this->assertControllerName('dataform\controller\profile');
        $this->assertControllerClass('ProfileController');
        $this->assertMatchedRouteName('data-form');
    }

    public function testIndexActionWorks()
    {
    }
}
