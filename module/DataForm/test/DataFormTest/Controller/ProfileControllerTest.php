<?php

namespace DataFormTest\Controller;

use Zend\Test\PHPUnit\Controller\AbstractHttpControllerTestCase;
use Zend\Http\Request as HttpRequest;
use Zend\Json\Json;
use Zend\Dom\Query;
use DataForm\Document\Profile as ProfileDocument;
use Application\Entity\Client as ClientEntity;
use Application\Entity\Secret as SecretEntity;

class ProfileControllerTest extends AbstractHttpControllerTestCase
{
    use \ApplicationTest\Controller\RegexAtLeastOnceTrait;
    use \ApplicationTest\Controller\PostRedirectGetTrait;

    public function setUp()
    {
        \Locale::setDefault('en_US');

        $this->setApplicationConfig(require 'config/application.config.php');

        parent::setUp();

        $sl = $this->getApplicationServiceLocator();

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
                         ->setMethods([ 'getRepository', 'persist', 'flush' ])
                         ->getMock();

        $this->clientEntityRepo = $this->getMockBuilder('Application\Entity\ClientRepository')
                                       ->disableOriginalConstructor()
                                       ->setMethods([ 'findOneByEmail' ])
                                       ->getMock();

        $this->client1 = new ClientEntity();
        $this->client1->setEmail('test@example.com');
        $this->client2 = new ClientEntity();
        $this->client2->setEmail('new@example.com');

        $reflection = new \ReflectionClass(get_class($this->client1));
        $property = $reflection->getProperty('id');
        $property->setAccessible(true);
        $property->setValue($this->client1, 42);
        $property->setValue($this->client2, 9000);

        $this->clientEntityRepo->expects($this->any())
                               ->method('findOneByEmail')
                               ->will($this->returnCallback(function ($email) {
                                    if ($this->client1->getEmail() == $email)
                                        return $this->client1;
                                    if ($this->client2->getEmail() == $email)
                                        return $this->client2;
                               }));

        $this->secretEntityRepo = $this->getMockBuilder('Application\Entity\SecretRepository')
                                       ->disableOriginalConstructor()
                                       ->setMethods([ 'findOneBy' ])
                                       ->getMock();

        $this->secret = new SecretEntity();
        $this->secret->setDataForm('profile');

        $this->secretEntityRepo->expects($this->any())
                               ->method('findOneBy')
                               ->will($this->returnCallback(function ($params) {
                                    if ($params['secret_key'] == 'client1') {
                                        $this->secret->setClient($this->client1);
                                        return $this->secret;
                                    }
                                    if ($params['secret_key'] == 'client2') {
                                        $this->secret->setClient($this->client2);
                                        return $this->secret;
                                    }
                               }));

        $this->em->expects($this->any())
                 ->method('getRepository')
                 ->will($this->returnValueMap([
                    [ 'Application\Entity\Client', $this->clientEntityRepo ],
                    [ 'Application\Entity\Secret', $this->secretEntityRepo ],
                 ]));

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

        $this->document = new ProfileDocument();
        $this->document->setId(42);
        $this->document->setFirstName('first-name');
        $this->document->setMiddleName('middle-name');
        $this->document->setLastName('last-name');
        $this->document->setGender('male');
        $this->document->setCompany('company');
        $this->document->setPosition('position');

        $created = false;

        $this->profileDocumentRepo->expects($this->any())
                                  ->method('find')
                                  ->will($this->returnCallback(function ($id) use (&$created) {
                                        if ($id == 42)
                                            return $this->document;
                                        if ($id == 9000 && $created)
                                            return $this->document;
                                  }));

        $this->dfm = $this->getMockBuilder('Application\Service\DataFormManager')
                          ->setMethods([ 'createClientDocuments' ])
                          ->getMock();

        $this->dfm->expects($this->any())
                  ->method('createClientDocuments')
                  ->will($this->returnCallback(function ($entity) use (&$created) {
                        $created = true;
                  }));

        $this->dfm->setServiceLocator($sl);

        $sl->setAllowOverride(true);
        $sl->setService('Config', $config);
        $sl->setService('Doctrine\ORM\EntityManager', $this->em);
        $sl->setService('doctrine.documentmanager.odm_default', $this->dm);
        $sl->setService('DataFormManager', $this->dfm);
    }

    public function setUpAdminAccess()
    {
        $sl = $this->getApplicationServiceLocator();
        $session = $sl->get('Session');
        $cnt = $session->getContainer();
        $cnt->is_admin = true;
    }

    public function testIndexActionCanBeAccessed()
    {
        $this->setUpAdminAccess();

        $this->dispatch('/data-form/profile', HttpRequest::METHOD_GET, [ 'email' => 'test@example.com' ]);
        $this->assertResponseStatusCode(200);

        $this->assertModuleName('dataform');
        $this->assertControllerName('dataform\controller\profile');
        $this->assertControllerClass('ProfileController');
        $this->assertMatchedRouteName('data-form');
    }

    public function testIndexActionClientAccess()
    {
        $this->dispatch('/data-form/profile', HttpRequest::METHOD_GET, [ 'key' => 'client1' ]);
        $this->assertResponseStatusCode(200);
    }

    public function testIndexActionValidates()
    {
        $getParams = [
            'key' => 'client1',
            'query' => 'validate',
            'field' => 'security',
            'form' => [ 'security' => 'foobar' ]
        ];

        $this->dispatch('/data-form/profile', HttpRequest::METHOD_GET, $getParams);
        $this->assertResponseStatusCode(200);

        $response = $this->getResponse()->getContent();
        $data = Json::decode($response, Json::TYPE_ARRAY);

        $this->assertEquals(true, isset($data['valid']) && isset($data['messages']));
    }

    public function testIndexActionCreatesMissingDocuments()
    {
        $this->dispatch('/data-form/profile', HttpRequest::METHOD_GET, [ 'key' => 'client2' ]);
        $this->assertResponseStatusCode(200);
    }

    public function testIndexActionPrintsFields()
    {
        $this->dispatch('/data-form/profile', HttpRequest::METHOD_GET, [ 'key' => 'client1' ]);
        $this->assertResponseStatusCode(200);

        $this->assertQueryContentRegexAtLeastOnce('input[name="first_name"][value="first-name"]', '/^$/m');
        $this->assertQueryContentRegexAtLeastOnce('input[name="middle_name"][value="middle-name"]', '/^$/m');
        $this->assertQueryContentRegexAtLeastOnce('input[name="last_name"][value="last-name"]', '/^$/m');
        $this->assertQueryContentRegexAtLeastOnce('input[name="gender"][value="male"]', '/^$/m');
        $this->assertQueryContentRegexAtLeastOnce('input[name="company"][value="company"]', '/^$/m');
        $this->assertQueryContentRegexAtLeastOnce('input[name="position"][value="position"]', '/^$/m');
    }

    public function testIndexActionUpdatesDocument()
    {
        $this->dispatch('/data-form/profile', HttpRequest::METHOD_GET, [ 'key' => 'client1' ]);

        $this->assertNotEquals(null, $this->secret->getWhenOpened(), "WhenOpened must be set");

        $response = $this->getResponse();
        $dom = new Query($response->getContent());
        $result = $dom->execute('input[name="security"]');
        $security = count($result) ? $result[0]->getAttribute('value') : null;

        $this->reset();

        $this->setUp();

        $this->prg('/data-form/profile?key=client1', [
            'security'  => $security,
            'first_name' => 'new first name',
            'middle_name' => 'new middle name',
            'last_name' => 'new last name',
            'gender' => 'female',
            'company' => 'new company',
            'position' => 'new position',
        ]);

        $this->assertNotEquals(null, $this->secret->getWhenSaved(), "WhenSaved must be set");

        $this->assertEquals('new first name', $this->document->getFirstName(), "First name is wrong");
        $this->assertEquals('new middle name', $this->document->getMiddleName(), "Middle name is wrong");
        $this->assertEquals('new last name', $this->document->getLastName(), "Last name is wrong");
        $this->assertEquals('female', $this->document->getGender(), "Gender is wrong");
        $this->assertEquals('new company', $this->document->getCompany(), "Company is wrong");
        $this->assertEquals('new position', $this->document->getPosition(), "Position is wrong");
    }
}
