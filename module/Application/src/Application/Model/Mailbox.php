<?php
/**
 * CorpNews
 *
 * @link        https://github.com/basarevych/corpnews
 * @copyright   Copyright (c) 2015 basarevych@gmail.com
 * @license     http://choosealicense.com/licenses/mit/ MIT
 */

namespace Application\Model;

/**
 * IMAP mailbox model
 * 
 * @category    Application
 * @package     Model
 */
class Mailbox
{
    /**
     * @const NAME_INBOX
     * @const NAME_INCOMING
     * @const NAME_REPLIES
     * @const NAME_BOUNCES
     */
    const NAME_INBOX = 'INBOX';
    const NAME_INCOMING = 'Incoming';
    const NAME_REPLIES = 'Replies';
    const NAME_BOUNCES = 'Bounces';

    /**
     * Mailbox info as returned by imap_getmailboxes()
     *
     * @var \StdClass
     */
    protected $info = null;

    /**
     * Constructor
     *
     * @param string $info
     */
    public function __construct($info)
    {
        $this->info = $info;
    }

    /**
     * Get connection string
     *
     * @return string
     */
    public function getConnString()
    {
        return $this->info->name;
    }

    /**
     * Get readable name
     *
     * @return string
     */
    public function getName()
    {
        $name = mb_convert_encoding($this->info->name, "UTF-8", "UTF7-IMAP");
        if (preg_match('/^{.+}(.+)$/', $name, $matches))
            $name = $matches[1];
        return $name;
    }

    public function isContainer()
    {
        return ($this->info->attributes & LATT_NOSELECT);
    }
}
