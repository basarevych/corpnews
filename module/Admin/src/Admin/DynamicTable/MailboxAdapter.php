<?php
/**
 * CorpNews
 *
 * @link        https://github.com/basarevych/corpnews
 * @copyright   Copyright (c) 2014 basarevych@gmail.com
 * @license     http://choosealicense.com/licenses/mit/ MIT
 */

namespace Admin\DynamicTable;

use DynamicTable\Table;
use DynamicTable\Adapter\ArrayAdapter;
use Application\Service\ImapClient;

/**
 * IMAP mailbox data adapter class
 *
 * @category    Admin
 * @package     DynamicTable
 */
class MailboxAdapter extends ArrayAdapter
{
    /**
     * IMAP Client service
     *
     * @var ImapClient
     */
    protected $imap = null;

    /**
     * Mailbox name
     *
     * @var string
     */
    protected $mailbox = null;

    /**
     * imap_sort() sort criteria
     *
     * @var integer
     */
    protected $sortCriteria = 0;

    /**
     * imap_sort() sort reverse flag
     *
     * @var integer
     */
    protected $sortReverse = 0;

    /**
     * imap_sort() search criteria
     *
     * @var string
     */
    protected $searchCriteria = "";

    /**
     * IMAP Client setter
     *
     * @param ImapClient $imap
     * @return MailboxAdapter
     */
    public function setImapClient(ImapClient $imap)
    {
        $this->imap = $imap;
        return $this;
    }

    /**
     * IMAP Client getter
     *
     * @return ImapClient
     */
    public function getImapClient()
    {
        return $this->imap;
    }

    /**
     * Mailbox name setter
     *
     * @param string $mailbox
     * @return MailboxAdapter
     */
    public function setMailbox($mailbox)
    {
        $this->mailbox = $mailbox;
        return $this;
    }

    /**
     * Mailbox name getter
     *
     * @return string
     */
    public function getMailbox()
    {
        return $this->mailbox;
    }

    /**
     * Filter data
     *
     * @param Table $table
     */
    public function filter(Table $table)
    {
        $this->searchCriteria = "";

        $filters = $table->getFilters();
        if (count($filters) == 0)
            return;

        if (isset($filters['date']['between'])) {
            $value = $filters['date']['between'];
            if ($value[0] != null) {
                $date = $this->getSearchDate($value[0]);
                $this->searchCriteria .= ' SINCE "' . $date . '"';
            }
            if ($value[1] != null) {
                $date = $this->getSearchDate($value[0]);
                $this->searchCriteria .= ' BEFORE "' . $date . '"';
            }
        }

        if (isset($filters['from']['like'])) {
            $value = $filters['from']['like'];
            $this->searchCriteria .= ' FROM "' . str_replace('"', '', $value) . '"';
        }

        if (isset($filters['subject']['like'])) {
            $value = $filters['subject']['like'];
            $this->searchCriteria .= ' SUBJECT "' . str_replace('"', '', $value) . '"';
        }
    }

    /**
     * Sort data
     *
     * @param Table $table
     */
    public function sort(Table $table)
    {
        $sortColumn = $table->getSortColumn();
        $sortDir = $table->getSortDir();

        $this->sortCriteria = 0;
        switch ($sortColumn) {
            case 'date':
                $this->sortCriteria = SORTDATE;
                break;
            case 'from':
                $this->sortCriteria = SORTFROM;
                break;
            case 'subject':
                $this->sortCriteria = SORTSUBJECT;
                break;
        }

        $this->sortReverse = ($sortDir == 'asc' ? 0 : 1);
    }

    /**
     * Paginate and return result
     *
     * @param Table $table
     * @return array
     */
    public function paginate(Table $table)
    {
        $messages = $this->imap->search(
            $this->mailbox,
            $this->sortCriteria,
            $this->sortReverse,
            $this->searchCriteria
        );
        $this->setData($messages);

        $result = parent::paginate($table);
        return $result;
    }

    /**
     * Convert timestamp to string
     *
     * @param integer $data
     * @return string
     */
    protected function getSearchDate($date)
    {
        $date = new \DateTime('@' . $date);
        return $date->format('d-M-Y H:i:s O');
    }
}
