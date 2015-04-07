<?php

namespace DataFormTest\Form;

use Zend\Test\PHPUnit\Controller\AbstractControllerTestCase;
use DataForm\Document\Subscription as SubscriptionDocument;

class SubscriptionDocumentTest extends AbstractControllerTestCase
{
    public function setUp()
    {
        $this->setApplicationConfig(require 'config/application.config.php');

        parent::setUp();
    }

    public function testToArray()
    {
        $dt = new \DateTime();
        $dtFormat = 'Y-m-d H:i:s P';

        $doc = new SubscriptionDocument();
        $doc->setId('id');
        $doc->setClientEmail('foo@bar');
        $doc->setWhenUpdated($dt);
        $doc->setUnsubscribed(true);
        $doc->setIgnoredTags([ 123 ]);

        $array = $doc->toArray();

        $this->assertEquals('id', $array['id'], "id is wrong");
        $this->assertEquals('foo@bar', $array['client_email'], "Email is wrong");
        $this->assertEquals($dt->format($dtFormat), $array['when_updated'], "when_updated is wrong");
        $this->assertEquals(true, $array['unsubscribed'], "unsubscribed is wrong");
        $this->assertEquals(123, $array['ignored_tags'], "ignored_tags is wrong");
    }

    public function testFromArray()
    {
        $dt = new \DateTime();
        $dtFormat = 'Y-m-d H:i:s P';

        $doc = new SubscriptionDocument();
        $doc->fromArray([
            'id'                => 'id',
            'client_email'      => 'foo@bar',
            'when_updated'      => $dt->format($dtFormat),
            'unsubscribed'      => true,
            'ignored_tags'      => 123,
        ]);

        $array = $doc->toArray();

        $this->assertEquals('id', $doc->getId(), "id is wrong");
        $this->assertEquals('foo@bar', $doc->getClientEmail(), "Email is wrong");
        $this->assertEquals($dt, $doc->getWhenUpdated(), "when_updated is wrong");
        $this->assertEquals(true, $doc->getUnsubscribed(), "unsubscribed is wrong");
        $this->assertEquals([ 123 ], $doc->getIgnoredTags(), "ignored_tags is wrong");
    }
}
