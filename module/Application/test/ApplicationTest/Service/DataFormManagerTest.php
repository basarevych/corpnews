<?php

namespace ApplicationTest\Service;

use Zend\Test\PHPUnit\Controller\AbstractControllerTestCase;
use Application\Service\DataFormManager;
use Application\Entity\Client as ClientEntity;
use DataForm\Document\Profile as ProfileDocument;

class DataFormManagerTest extends AbstractControllerTestCase
{
    public function setUp()
    {
        $this->setApplicationConfig(require 'config/application.config.php');

        parent::setUp();

        $this->getApplication()->bootstrap();

        $this->dm = $this->getMockBuilder('Doctrine\ODM\MongoDB\DocumentManager')
                         ->disableOriginalConstructor()
                         ->setMethods([ 'getRepository', 'persist', 'flush', 'remove' ])
                         ->getMock();

        $this->repoProfile = $this->getMockBuilder('DataForm\Document\ProfileRepository')
                                  ->disableOriginalConstructor()
                                  ->setMethods([ 'find' ])
                                  ->getMock();

        $this->dm->expects($this->any())
                 ->method('getRepository')
                 ->will($this->returnValueMap([
                    [ 'DataForm\Document\Profile', $this->repoProfile ],
                ]));

        $sl = $this->getApplicationServiceLocator();
        $sl->setAllowOverride(true);

        $config = $sl->get('Config');
        $config['corpnews']['data_forms'] = [
            'profile' => [
                'title'     => 'Profile',
                'url'       => '/data-form/profile',
                'document'  => 'DataForm\Document\Profile',
                'form'      => 'DataForm\Form\Profile',
            ],
        ];
        $sl->setService('doctrine.documentmanager.odm_default', $this->dm);
        $sl->setService('Config', $config);
    }

    public function testServiceLocatorMethods()
    {
        $service = new DataFormManager();
        $sl = $this->getApplicationServiceLocator();
        $service->setServiceLocator($sl);

        $this->assertEquals(
            $sl,
            $service->getServiceLocator(),
            "Service Locator is wrong"
        );
    }

    public function testSimpleMethods()
    {
        $service = new DataFormManager();
        $sl = $this->getApplicationServiceLocator();
        $service->setServiceLocator($sl);

        $this->assertEquals([ 'profile' ], $service->getNames(), "Returned names are wrong");
        $this->assertEquals('Profile', $service->getTitle('profile'), "Returned title is wrong");
        $this->assertEquals('/data-form/profile', $service->getUrl('profile'), "Returned url is wrong");
        $this->assertEquals('DataForm\Document\Profile', $service->getDocumentClass('profile'), "Returned document class is wrong");
        $this->assertEquals('DataForm\Form\Profile', $service->getFormClass('profile'), "Returned form class is wrong");
    }

    public function testCreateClientDocuments()
    {
        $client = new ClientEntity();
        $client->setEmail('foobar');

        $reflection = new \ReflectionClass(get_class($client));
        $property = $reflection->getProperty('id');
        $property->setAccessible(true);
        $property->setValue($client, 42);

        $persisted = [];
        $this->dm->expects($this->any())
                 ->method('persist')
                 ->will($this->returnCallback(function ($doc) use (&$persisted) {
                    $persisted[] = $doc;
                }));

        $service = new DataFormManager();
        $sl = $this->getApplicationServiceLocator();
        $service->setServiceLocator($sl);

        $service->createClientDocuments($client);

        $this->assertEquals(1, count($persisted), "One document should have been created");
        $this->assertEquals(true, $persisted[0] instanceof \DataForm\Document\Profile, "Wrong document created");
        $this->assertEquals(42, $persisted[0]->getId(), "Wrong ID created");
        $this->assertEquals('foobar', $persisted[0]->getClientEmail(), "Wrong email created");
    }

    public function testUpdateClientDocuments()
    {
        $client = new ClientEntity();
        $client->setEmail('foobar');

        $reflection = new \ReflectionClass(get_class($client));
        $property = $reflection->getProperty('id');
        $property->setAccessible(true);
        $property->setValue($client, 42);

        $doc = new ProfileDocument();

        $checkedId = null;
        $this->repoProfile->expects($this->any())
                          ->method('find')
                          ->will($this->returnCallback(function ($id) use (&$checkedId, $doc) {
                                $checkedId = $id;
                                return $doc;
                          }));

        $service = new DataFormManager();
        $sl = $this->getApplicationServiceLocator();
        $service->setServiceLocator($sl);

        $service->updateClientDocuments($client);

        $this->assertEquals(42, $checkedId, "Searched for wrong ID");
        $this->assertEquals('foobar', $doc->getClientEmail(), "Wrong email saved");
    }

    public function testDeleteClientDocuments()
    {
        $client = new ClientEntity();
        $client->setEmail('foobar');

        $reflection = new \ReflectionClass(get_class($client));
        $property = $reflection->getProperty('id');
        $property->setAccessible(true);
        $property->setValue($client, 42);

        $doc = new ProfileDocument();

        $checkedId = null;
        $this->repoProfile->expects($this->any())
                          ->method('find')
                          ->will($this->returnCallback(function ($id) use (&$checkedId, $doc) {
                                $checkedId = $id;
                                return $doc;
                          }));

        $removed = [];
        $this->dm->expects($this->any())
                 ->method('remove')
                 ->will($this->returnCallback(function ($doc) use (&$removed) {
                    $removed[] = $doc;
                }));

        $service = new DataFormManager();
        $sl = $this->getApplicationServiceLocator();
        $service->setServiceLocator($sl);

        $service->deleteClientDocuments($client);

        $this->assertEquals(42, $checkedId, "Searched for wrong ID");
        $this->assertEquals(1, count($removed), "One doc should have been removed");
        $this->assertEquals($doc, $removed[0], "Wrong doc removed");
    }
}
