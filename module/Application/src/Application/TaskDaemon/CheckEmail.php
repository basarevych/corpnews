<?php
/**
 * CorpNews
 *
 * @link        https://github.com/basarevych/corpnews
 * @copyright   Copyright (c) 2015 basarevych@gmail.com
 * @license     http://choosealicense.com/licenses/mit/ MIT
 */

namespace Application\TaskDaemon;

use Exception;
use Application\Document\Syslog as SyslogDocument;
use Application\Model\Mailbox;

/**
 * CheckEmail task
 * 
 * @category    Application
 * @package     TaskDaemon
 */
class CheckEmail extends ZfTask
{
    /**
     * Do the job
     */
    public function run()
    {
        $sl = $this->getServiceLocator();
        $imap = $sl->get('ImapClient');
        $logger = $sl->get('Logger');
        $em = $sl->get('Doctrine\ORM\EntityManager');

        $em->getConnection()->close();
        $em->getConnection()->connect();

        $boxes = $imap->getMailboxes();
        $incoming = null;
        $replies = null;
        $bounces = null;
        $toSearch = [];
        foreach ($boxes as $box) {
            switch ($box->getName()) {
                case Mailbox::NAME_INCOMING:
                    $incoming = $box;
                    break;
                case Mailbox::NAME_REPLIES:
                    $replies = $box;
                    break;
                case Mailbox::NAME_BOUNCES:
                    $bounces = $box;
                    break;
                default:
                    $toSearch[] = $box;
            }
        }

        if (!$incoming) {
            $logger->log(
                SyslogDocument::LEVEL_INFO,
                'INFO_MAILBOX_CREATED',
                [
                    'source_name' => Mailbox::NAME_INCOMING,
                ]
            );
            $incoming = $imap->createMailbox(Mailbox::NAME_INCOMING);
        }
        if (!$replies) {
            $logger->log(
                SyslogDocument::LEVEL_INFO,
                'INFO_MAILBOX_CREATED',
                [
                    'source_name' => Mailbox::NAME_REPLIES,
                ]
            );
            $replies = $imap->createMailbox(Mailbox::NAME_REPLIES);
        }
        if (!$bounces) {
            $logger->log(
                SyslogDocument::LEVEL_INFO,
                'INFO_MAILBOX_CREATED',
                [
                    'source_name' => Mailbox::NAME_BOUNCES,
                ]
            );
            $bounces = $imap->createMailbox(Mailbox::NAME_BOUNCES);
        }

        $autodelete = $em->getRepository('Application\Entity\Setting')
                         ->getValue('MailboxAutodelete');
        if (!$autodelete)
            return;

        $oldDate = new \DateTime();
        $oldDate->setTimezone(new \DateTimeZone('GMT'));
        $oldDate->sub(new \DateInterval('P' . $autodelete . 'D'));
        $oldDate->setTime(0, 0, 0);
        $oldDate = \Application\Tool\Date::toImapString($oldDate);

        foreach ($boxes as $box) {
            $messages = $imap->search($box->getName(), 0, 0, 'BEFORE "' . $oldDate . '"');
            if (count($messages)) {
                foreach ($messages as $uid) {
                    $letter = $imap->getLetter($box->getName(), $uid);
                    $logger->log(
                        SyslogDocument::LEVEL_INFO,
                        'INFO_LETTER_AUTODELETED',
                        [
                            'source_id' => $uid,
                        ]
                    );
                    $imap->deleteLetter($box->getName(), $uid);
                }
            }
        }

        foreach ($toSearch as $box) {
            $messages = $imap->search($box->getName(), 0, 0, 'SINCE "' . $oldDate . '"');
            if (count($messages)) {
                foreach ($messages as $uid) {
                    $letter = $imap->getLetter($box->getName(), $uid);
                    $logger->log(
                        SyslogDocument::LEVEL_INFO,
                        'INFO_LETTER_PROCESSED',
                        [
                            'source_id' => $uid
                        ]
                    );
                    $imap->moveLetter($uid, $box->getName(), Mailbox::NAME_INCOMING);
                }
            }
        }
    }
}
