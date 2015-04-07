<?php

namespace AdminTest\Controller;

use Zend\Http\Request as HttpRequest;
use Zend\Dom\Query;
use Zend\Json\Json;
use Zend\Test\PHPUnit\Controller\AbstractHttpControllerTestCase;
use Webfactory\Doctrine\ORMTestInfrastructure\ORMInfrastructure;
use Application\Entity\Client as ClientEntity;
use Application\Entity\Group as GroupEntity;
use DataForm\Document\Profile as ProfileDocument;

class ImportExportControllerTest extends AbstractHttpControllerTestCase
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
                         ->setMethods([ 'getRepository' ])
                         ->getMock();

        $this->repoGroups = $this->getMockBuilder('Application\Entity\ClientRepository')
                                 ->disableOriginalConstructor()
                                 ->setMethods([ 'findBy' ])
                                 ->getMock();

        $this->group = new GroupEntity();
        $this->group->setName('a');

        $reflection = new \ReflectionClass(get_class($this->group));
        $property = $reflection->getProperty('id');
        $property->setAccessible(true);
        $property->setValue($this->group, 9000);

        $this->repoGroups->expects($this->any())
                         ->method('findBy')
                         ->will($this->returnValue([ $this->group ]));

        $this->repoClients = $this->getMockBuilder('Application\Entity\ClientRepository')
                                  ->disableOriginalConstructor()
                                  ->setMethods([ 'findByGroupName' ])
                                  ->getMock();

        $this->client = new ClientEntity();
        $this->client->setEmail('foo@bar');

        $reflection = new \ReflectionClass(get_class($this->client));
        $property = $reflection->getProperty('id');
        $property->setAccessible(true);
        $property->setValue($this->client, 42);

        $this->repoClients->expects($this->any())
                          ->method('findByGroupName')
                          ->will($this->returnValue([ $this->client ]));

        $this->em->expects($this->any())
                 ->method('getRepository')
                 ->will($this->returnValueMap([
                    [ 'Application\Entity\Client', $this->repoClients ],
                    [ 'Application\Entity\Group', $this->repoGroups ],
                 ]));

        $this->dm = $this->getMockBuilder('Doctrine\ODM\MongoDB\DocumentManager')
                         ->disableOriginalConstructor()
                         ->setMethods([ 'getRepository' ])
                         ->getMock();

        $this->repoProfiles = $this->getMockBuilder('DataForm\Document\ProfileRepository')
                                   ->disableOriginalConstructor()
                                   ->setMethods([ 'find' ])
                                   ->getMock();

        $this->profile = new ProfileDocument();
        $this->profile->setLastName('Lastname');

        $this->repoProfiles->expects($this->any())
                           ->method('find')
                           ->will($this->returnValue($this->profile));

        $this->dm->expects($this->any())
                 ->method('getRepository')
                 ->will($this->returnValue($this->repoProfiles));

        $sl->setAllowOverride(true);
        $sl->setService('Doctrine\ORM\EntityManager', $this->em);
        $sl->setService('doctrine.documentmanager.odm_default', $this->dm);
    }
/*
    public function testIndexActionCanBeAccessed()
    {
        $this->dispatch('/admin/import-export');
        $this->assertResponseStatusCode(200);

        $this->assertModuleName('admin');
        $this->assertControllerName('admin\controller\importexport');
        $this->assertControllerClass('ImportExportController');
        $this->assertMatchedRouteName('admin');
    }

    public function testDownloadActionCanBeAccessed()
    {
        $this->dispatch('/admin/import-export/download');
        $this->assertResponseStatusCode(200);

        $this->assertModuleName('admin');
        $this->assertControllerName('admin\controller\importexport');
        $this->assertControllerClass('ImportExportController');
        $this->assertMatchedRouteName('admin');
    }

    public function testDownloadActionGeneratesFile()
    {
        $params = [
            'groups'    => 9000,
            'fields'    => 'email,profile-last_name'
        ];
        $this->dispatch('/admin/import-export/download', HttpRequest::METHOD_GET, $params);
        $this->assertResponseStatusCode(200);

        $file = $this->getResponse()->getContent();
        $lines = explode("\n", $file);

        $this->assertEquals(3, count($lines), "Two lines should be generated");
        $this->assertEquals('"email","profile / last_name"', $lines[0], "Header is wrong");
        $this->assertEquals('"foo@bar","Lastname"', $lines[1], "Data is wrong");
    }
*/
}
