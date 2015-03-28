<?php

namespace DataFormTest\Table;

use Zend\Json\Json;
use Zend\Test\PHPUnit\Controller\AbstractHttpControllerTestCase;
use Application\Entity\Tag as TagEntity;
use DataForm\Document\Subscription as SubscriptionDocument;

class SubscriptionQueryMock {
    protected $cursor;

    public function __construct($cursor) {
        $this->cursor = $cursor;
    }

    public function execute() {
        return $this->cursor;
    }
}

class SubscriptionTest extends AbstractHttpControllerTestCase
{
    public function setUp()
    {
        $this->setApplicationConfig(require 'config/application.config.php');

        parent::setUp();

        $sl = $this->getApplicationServiceLocator();

        $config = $sl->get('Config');
        $config['corpnews'] = [
            'data_forms' => [
                'subscription' => [
                    'title'     => 'Subscription',
                    'url'       => '/data-form/subscription',
                    'document'  => 'DataForm\Document\Subscription',
                    'form'      => 'DataForm\Form\Subscription',
                    'table'     => 'DataForm\Table\Subscription',
                ],
            ],
        ];

        $this->em = $this->getMockBuilder('Doctrine\ORM\EntityManager')
                         ->disableOriginalConstructor()
                         ->setMethods([ 'getRepository' ])
                         ->getMock();

        $this->repoTags = $this->getMockBuilder('Application\Entity\TagRepository')
                               ->disableOriginalConstructor()
                               ->setMethods([ 'findBy' ])
                               ->getMock();

        $this->tag = new TagEntity();
        $this->tag->setName('tag1');
        $this->tag->setDescr('Tag description');

        $reflection = new \ReflectionClass(get_class($this->tag));
        $property = $reflection->getProperty('id');
        $property->setAccessible(true);
        $property->setValue($this->tag, 42);

        $this->repoTags->expects($this->any())
                       ->method('findBy')
                       ->will($this->returnValue([ $this->tag ]));

        $this->em->expects($this->any())
                 ->method('getRepository')
                 ->will($this->returnValueMap([
                    [ 'Application\Entity\Tag', $this->repoTags ],
                 ]));

        $this->dm = $this->getMockBuilder('Doctrine\ODM\MongoDB\DocumentManager')
                         ->disableOriginalConstructor()
                         ->setMethods([ 'createQueryBuilder', 'getDocumentCollection', 'getClassMetadata', 'getRepository' ])
                         ->getMock();

        $this->dm->expects($this->any())
                 ->method('getClassMetadata')
                 ->will($this->returnCallback(function ($name) {
                    return new \Doctrine\ODM\MongoDB\Mapping\ClassMetadata($name);
                 }));

        $this->repo = $this->getMockBuilder('DataForm\Document\SubscriptionRepository')
                           ->disableOriginalConstructor()
                           ->getMock();

        $this->dm->expects($this->any())
                 ->method('getRepository')
                 ->will($this->returnValue($this->repo));

        $this->qb = $this->getMockBuilder('Doctrine\ODM\MongoDB\Query\Builder')
                         ->setConstructorArgs([ $this->dm, 'DataForm\Document\Profile' ])
                         ->setMethods([ 'expr', 'getQuery', 'sort' ])
                         ->getMock();

        $this->dm->expects($this->any())
                 ->method('createQueryBuilder')
                 ->will($this->returnValue($this->qb));

        $this->expr = $this->getMockBuilder('Doctrine\ODM\MongoDB\Query\Expr')
                           ->setConstructorArgs([ $this->dm ])
                           ->setMethods([ 'getQuery', 'equals', 'range', 'gte', 'lte', 'exists' ])
                           ->getMock();

        $this->qb->expects($this->any())
                 ->method('expr')
                 ->will($this->returnValue($this->expr));

        $this->cursor = $this->getMockBuilder('Doctrine\ODM\MongoDB\Cursor')
                             ->disableOriginalConstructor()
                             ->setMethods([ 'valid', 'count', 'current', 'getMongoCursor', 'skip', 'limit' ])
                             ->getMock();

        $this->mongoCursor = $this->getMockBuilder('MongoCursor')
                                  ->disableOriginalConstructor()
                                  ->getMock();

        $this->cursor->expects($this->any())
                     ->method('getMongoCursor')
                     ->will($this->returnValue($this->mongoCursor));

        $this->qb->expects($this->any())
                 ->method('getQuery')
                 ->will($this->returnValue(new ProfileQueryMock($this->cursor)));

        $sl->setAllowOverride(true);
        $sl->setService('Config', $config);
        $sl->setService('doctrine.documentmanager.odm_default', $this->dm);

        $this->table = new \DataForm\Table\Subscription($sl);
    }

    public function testTableSendsDescription()
    {
        $data = $this->table->describe();

        $this->assertEquals(true, isset($data['columns']) && count($data['columns']), "No columns described");
    }

    public function testTableSendsData()
    {
        $doc = new SubscriptionDocument();
        $doc->setId(42);
        $fixture = [ $doc ];

        $i = 0; $count = count($fixture);
        $this->cursor->expects($this->any())
                     ->method('valid')
                     ->will($this->returnCallback(function () use (&$i, $count) {
                        return $i < $count;
                     }));

        $this->cursor->expects($this->any())
                     ->method('count')
                     ->will($this->returnValue($count));

        $this->cursor->expects($this->any())
                     ->method('current')
                     ->will($this->returnCallback(function () use (&$i, $fixture) {
                        return $fixture[$i++];
                     }));

        $data = $this->table->fetch();

        $this->assertEquals(true, isset($data['rows']) && count($data['rows']) == 1, "Invalid data returned");
        $this->assertEquals(42, $data['rows'][0]['id'], "Invalid ID");
    }
}
