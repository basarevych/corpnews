<?php
/**
 * CorpNews
 *
 * @link        https://github.com/basarevych/corpnews
 * @copyright   Copyright (c) 2015 basarevych@gmail.com
 * @license     http://choosealicense.com/licenses/mit/ MIT
 */

namespace Application\Service;

use Exception;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Application\Model\Mailbox;
use Application\Model\Letter;

/**
 * IMAP Client service
 * 
 * @category    Application
 * @package     Service
 */
class ImapClient implements ServiceLocatorAwareInterface
{
    /**
     * Service Locator
     *
     * @var ServiceLocatorInterface
     */
    protected $serviceLocator = null;

    /**
     * Set service locator
     *
     * @param ServiceLocatorInterface $serviceLocator
     * @return Acl
     */
    public function setServiceLocator(ServiceLocatorInterface $serviceLocator)
    {
        $this->serviceLocator = $serviceLocator;

        return $this;
    }

    /**
     * Get service locator
     *
     * @return ServiceLocatorInterface
     */
    public function getServiceLocator()
    {
        if (!$this->serviceLocator)
            throw new \Exception('No Service Locator configured');
        return $this->serviceLocator;
    }

    /**
     * Connects to mbox
     *
     * @param string $connString    Connection string
     * @throws Exception            When not configured
     * @return mixed                IMAP resource
     */
    public function connect($connString)
    {
        $sl = $this->getServiceLocator();
        $config = $sl->get('Config');

        if (!isset($config['corpnews']['imap']['account']))
            throw new Exception('Set IMAP account name in the config');
        $username = $config['corpnews']['imap']['account'];
        if (!isset($config['corpnews']['imap']['password']))
            throw new Exception('Set IMAP account password in the config');
        $password = $config['corpnews']['imap']['password'];

        $resource = @imap_open($connString, $username, $password);
        if (!$resource)
            throw new Exception("imap_open failed: " . imap_last_error());

        return $resource;
    }

    /**
     * Disconnects from mailbox
     *
     * @param mixed $resource
     */
    public function disconnect($resource)
    {
        imap_close($resource, CL_EXPUNGE);
    }

    /**
     * Get all the mailboxes
     *
     * @return array
     * @throws Exception    On IMAP error
     */
    public function getMailboxes()
    {
        $resource = $this->connect($this->getConnString());
        $list = @imap_getmailboxes($resource, $this->getConnString(), "*");
        if (!is_array($list))
            throw new Exception("imap_getmailboxes failed: " . imap_last_error());
        $this->disconnect($resource);

        $boxes = [];
        foreach ($list as $item) {
            $box = new Mailbox($item);
            $boxes[$box->getName()] = $box;
        }

        return $boxes;
    }

    /**
     * Create a mailbox
     *
     * @param string $boxName
     * @return ImapClient
     */
    public function createMailbox($boxName)
    {
        $box = mb_convert_encoding($boxName, "UTF7-IMAP", "UTF-8");

        $resource = $this->connect($this->getConnString());
        $result = @imap_createmailbox($resource, $this->getConnString() . $box);
        $this->disconnect($resource);

        if (!$result)
            throw new Exception("imap_createmailbox failed: " . imap_last_error());

        $boxes = $this->getMailboxes();
        return $boxes[$boxName];
    }

    /**
     * Get number of letters
     *
     * @param string $boxName
     * @return integer
     */
    public function getLetterCount($boxName)
    {
        $box = mb_convert_encoding($boxName, "UTF7-IMAP", "UTF-8");

        $resource = $this->connect($this->getConnString() . $box);
        $check = @imap_check($resource);
        $this->disconnect($resource);

        return $check->Nmsgs;
    }

    /**
     * Count new (unseen) messages
     *
     * @param string $boxName
     * @return integer
     */
    public function getUnseenLetterCount($boxName)
    {
        $box = mb_convert_encoding($boxName, "UTF7-IMAP", "UTF-8");

        $resource = $this->connect($this->getConnString() . $box);
        $check = @imap_status($resource, $this->getConnString() . $box, SA_UNSEEN);
        $this->disconnect($resource);

        return $check->unseen;
    }

    /**
     * Get all the Letters
     *
     * @param string $boxName
     * @return array
     */
    public function getLetters($boxName)
    {
        $box = mb_convert_encoding($boxName, "UTF7-IMAP", "UTF-8");

        $resource = $this->connect($this->getConnString() . $box);
        $search = @imap_search($resource, ALL, SE_UID, 'UTF-8');
        $this->disconnect($resource);

        if (!is_array($search))
            return [];

        $letters = [];
        foreach ($search as $uid)
            $letters[] = $this->getLetter($boxName, $uid);

        return $letters;
    }

    /**
     * Get the letter
     *
     * @param string $boxName
     * @param integer $uid
     * @return array
     */
    public function getLetter($boxName, $uid)
    {
        $box = mb_convert_encoding($boxName, "UTF7-IMAP", "UTF-8");

        $resource = $this->connect($this->getConnString() . $box);
        $header = @imap_fetchheader($resource, $uid, FT_UID);
        $this->disconnect($resource);
        if (!$header)
            return null;

        $header = imap_rfc822_parse_headers($header);
        $letter = new Letter($uid, $header);
        return $letter;
    }

    /**
     * Load the letter
     *
     * @param Letter $letter
     * @param string $boxName
     * @param integer $uid
     * @return boolean          True on successful parsing
     */
    public function loadLetter(Letter $letter, $boxName, $uid)
    {
        $box = mb_convert_encoding($boxName, "UTF7-IMAP", "UTF-8");
        $resource = $this->connect($this->getConnString() . $box);

        $rawHeaders = @imap_fetchheader($resource, $uid, FT_UID);
        $rawBody = @imap_body($resource, $uid, FT_UID | FT_PEEK);

        return $letter->load($rawHeaders, $rawBody);
    }

    /**
     * Move letter to another mailbox
     *
     * @param integer $uid
     * @param string $fromBoxName
     * @param string $toBoxName
     * @return ImapClient
     */
    public function moveLetter($uid, $fromBoxName, $toBoxName)
    {
        $from = mb_convert_encoding($fromBoxName, "UTF7-IMAP", "UTF-8");
        $to = mb_convert_encoding($toBoxName, "UTF7-IMAP", "UTF-8");

        $resource = $this->connect($this->getConnString() . $from);
        $result = @imap_mail_move($resource, $uid, $to, CP_UID);
        $this->disconnect($resource);

        if (!$result)
            throw new Exception("imap_mail_move failed: " . imap_last_error());

        return $this;
    }

    /**
     * Delete letter
     *
     * @param string $boxName
     * @param integer $uid
     * @return ImapClient
     */
    public function deleteLetter($boxName, $uid)
    {
        $box = mb_convert_encoding($boxName, "UTF7-IMAP", "UTF-8");

        $resource = $this->connect($this->getConnString() . $box);
        @imap_delete($resource, $uid, FT_UID);
        $this->disconnect($resource);

        return $this;
    }

    /**
     * Is letter seen?
     *
     * @param string $boxName
     * @param integer $uid
     * @return boolean
     */
    public function isLetterSeen($boxName, $uid)
    {
        $box = mb_convert_encoding($boxName, "UTF7-IMAP", "UTF-8");

        $resource = $this->connect($this->getConnString() . $box);
        $overview = @imap_fetch_overview($resource, $uid, FT_UID);
        $this->disconnect($resource);

        return $overview && count($overview) && $overview[0]->seen;
    }

    /**
     * Mark letter as seen
     *
     * @param string $boxName
     * @param integer $uid
     * @return ImapClient
     */
    public function markLetterSeen($boxName, $uid)
    {
        $box = mb_convert_encoding($boxName, "UTF7-IMAP", "UTF-8");

        $resource = $this->connect($this->getConnString() . $box);
        @imap_setflag_full($resource, $uid, "\\Seen", ST_UID);
        $this->disconnect($resource);

        return $this;
    }

    /**
     * Search the mailbox
     *
     * @param string $boxName
     * @param integer $sortCriteria
     * @param integer $sortReverse
     * @param string $searchCriteri
     * @return array
     */
    public function search($boxName, $sortCriteria, $sortReverse, $searchCriteria)
    {
        $box = mb_convert_encoding($boxName, "UTF7-IMAP", "UTF-8");
        $resource = $this->connect($this->getConnString() . $box);

        $messages = imap_sort(
            $resource,
            $sortCriteria,
            $sortReverse,
            SE_UID | SE_NOPREFETCH,
            $searchCriteria,
            'UTF-8'
        );

        $this->disconnect($resource);
        return $messages;
    }

    /**
     * Builds connection string
     *
     * @return string
     */
    protected function getConnString()
    {
        $sl = $this->getServiceLocator();
        $config = $sl->get('Config');

        if (!isset($config['corpnews']['imap']['server']))
            throw new Exception('Set IMAP server address in the config');
        if (!isset($config['corpnews']['imap']['port']))
            throw new Exception('Set IMAP server port in the config');

        $params = '/novalidate-cert';
        if (@$config['corpnews']['imap']['ssl'] === true)
            $params .= '/ssl';

        $connString = '{' . $config['corpnews']['imap']['server']
            . ':' . $config['corpnews']['imap']['port'] . $params . '}';
        return $connString;
    }
}
