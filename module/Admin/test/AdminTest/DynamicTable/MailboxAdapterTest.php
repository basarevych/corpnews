<?php

namespace AdminTest\DynamicTable;

use Zend\Test\PHPUnit\Controller\AbstractControllerTestCase;
use DynamicTable\Table;
use Admin\DynamicTable\MailboxAdapter;

class MailboxAdapterTest extends AbstractControllerTestCase
{
    public function setUp()
    {
        $this->setApplicationConfig(require 'config/application.config.php');

        parent::setUp();

        $this->imap = $this->getMockBuilder('Application\Service\ImapClient')
                           ->setMethods([ 'search' ])
                           ->getMock();

        $this->table = new Table();

        $this->table->setColumns([
            'uid' => [
                'title'     => 'UID',
                'type'      => Table::TYPE_INTEGER,
                'filters'   => [],
                'sortable'  => false,
                'visible'   => false,
            ],
            'date' => [
                'title'     => 'Date',
                'type'      => Table::TYPE_DATETIME,
                'filters'   => [ Table::FILTER_BETWEEN ],
                'sortable'  => true,
                'visible'   => true,
            ],
            'from' => [
                'title'     => 'From',
                'type'      => Table::TYPE_STRING,
                'filters'   => [ Table::FILTER_LIKE ],
                'sortable'  => true,
                'visible'   => true,
            ],
            'subject' => [
                'title'     => 'Subject',
                'type'      => Table::TYPE_STRING,
                'filters'   => [ Table::FILTER_LIKE ],
                'sortable'  => true,
                'visible'   => true,
            ],
        ]);

        $this->dt1 = new \DateTime();
        $this->dt2 = clone $this->dt1;
        $this->dt2->add(new \DateInterval('P1D'));

        $this->table->setFilters([
            'date' => [
                'between' => [ $this->dt1->getTimestamp(), $this->dt2->getTimestamp() ],
            ],
            'from' => [
                'like' => 'from',
            ],
            'subject' => [
                'like' => 'subject'
            ],
        ]);

        $this->table->setSortColumn('from');
        $this->table->setSortDir('desc');

        $this->adapter = new MailboxAdapter();
        $this->adapter->setImapClient($this->imap);
        $this->adapter->setMailbox('foobar');
    }

    public function testAdapterSearches()
    {
        $callback = function ($mailbox, $sortCriteria, $sortReverse, $searchCriteria) use (&$searchedBox, &$searchedSortCriteria, &$searchedSortReverse, &$searchedSearchCriteria) {
            $searchedBox = $mailbox;
            $searchedSortCriteria = $sortCriteria;
            $searchedSortReverse = $sortReverse;
            $searchedSearchCriteria = $searchCriteria;
            return [ 1, 2, 3 ];
        };

        $searchedBox = null;
        $searchedSortCriteria = null;
        $searchedSortReverse = null;
        $searchedSearchCriteria = null;
        $this->imap->expects($this->any())
                   ->method('search')
                   ->will($this->returnCallback($callback));

        $this->adapter->filter($this->table);
        $this->adapter->sort($this->table);
        $result = $this->adapter->paginate($this->table);

        $this->assertEquals([ 1, 2, 3 ], $result, "Wrong result returned");
        $this->assertEquals('foobar', $searchedBox, "Wrong mailbox searched");
        $this->assertEquals(SORTFROM, $searchedSortCriteria, "Wrong sort criteria");
        $this->assertEquals(1, $searchedSortReverse, "Wrong sort reverse flag");

        $since = $this->dt1->format('d-M-Y H:i:s O');
        $this->assertNotEquals(
            -1,
            strpos($searchedSearchCriteria, 'SINCE "' . $since . '"'),
            "Wrong SINCE search criteria"
        );

        $before = $this->dt2->format('d-M-Y H:i:s O');
        $this->assertNotEquals(
            -1,
            strpos($searchedSearchCriteria, 'BEFORE "' . $before . '"'),
            "Wrong BEFORE search criteria"
        );

        $this->assertNotEquals(
            -1,
            strpos($searchedSearchCriteria, 'FROM "from"'),
            "Wrong FROM search criteria"
        );

        $this->assertNotEquals(
            -1,
            strpos($searchedSearchCriteria, 'SUBJECT "subject"'),
            "Wrong SUBJECT search criteria"
        );
    }
}
