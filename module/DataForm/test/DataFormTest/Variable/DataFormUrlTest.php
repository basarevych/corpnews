<?php

namespace DataFormTest\Variable;

use Zend\Json\Json;
use Zend\Test\PHPUnit\Controller\AbstractHttpControllerTestCase;
use Application\Entity\Campaign as CampaignEntity;
use Application\Entity\Template as TemplateEntity;
use Application\Entity\Client as ClientEntity;
use DataForm\Document\Profile as ProfileDocument;
use DataForm\Variable\DataFormUrl as DataFormUrlVariable;

class DataFormUrlTest extends AbstractHttpControllerTestCase
{
    public function setUp()
    {
        $this->setApplicationConfig(require 'config/application.config.php');

        parent::setUp();

        $this->doc = new ProfileDocument();

        $this->em = $this->getMockBuilder('Doctrine\ORM\EntityManager')
                         ->disableOriginalConstructor()
                         ->setMethods([ 'getRepository', 'persist', 'flush' ])
                         ->getMock();

        $this->repo = $this->getMockBuilder('Application\Entity\SecretRepository')
                           ->disableOriginalConstructor()
                           ->setMethods([ 'findOneBy' ])
                           ->getMock();

        $this->repo->expects($this->any())
                   ->method('findOneBy')
                   ->will($this->returnValue(null));

        $this->em->expects($this->any())
                 ->method('getRepository')
                 ->will($this->returnValue($this->repo));

        $this->dfm = $this->getMockBuilder('Application\Service\DataFormManager')
                          ->disableOriginalConstructor()
                          ->setMethods([ 'getUrl' ])
                          ->getMock();

        $this->dfm->expects($this->any())
                  ->method('getUrl')
                  ->will($this->returnValue('/data-form/foobar'));

        $this->sl = $this->getApplicationServiceLocator();
        $this->sl->setAllowOverride(true);
        $this->sl->setService('Doctrine\ORM\EntityManager', $this->em);
        $this->sl->setService('DataFormManager', $this->dfm);

        $config = $this->sl->get('Config');
        $config['corpnews']['server']['base_url'] = 'https://some.server:8000/some/path';
        $this->sl->setService('Config', $config);
    }

    public function testExecute()
    {
        $persisted = null;
        $this->em->expects($this->any())
                 ->method('persist')
                 ->will($this->returnCallback(function ($entity) use (&$persisted) {
                      $persisted = $entity;
                 }));

        $campaign = new CampaignEntity();

        $template = new TemplateEntity();
        $template->setCampaign($campaign);

        $client = new ClientEntity();

        $var = new DataFormUrlVariable();
        $var->setServiceLocator($this->sl);
        $var->setTemplate($template);
        $var->setClient($client);

        ob_start();
        $var->execute('dataform', 'link text');
        $output = ob_get_contents();
        ob_end_clean();

        $this->assertEquals($campaign, $persisted->getCampaign(), "Campaign is wrong");
        $this->assertEquals($client, $persisted->getClient(), "Client is wrong");
        $this->assertEquals('dataform', $persisted->getDataForm(), "Data form is wrong");

        $this->assertEquals('<a href="https://some.server:8000/some/path/data-form/foobar?key=' . $persisted->getSecretKey() . '">link text</a>', $output);
    }
}
