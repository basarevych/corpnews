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
    public function run(&$exitRequested)
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
            $incoming = $imap->createMailbox(Mailbox::NAME_INCOMING);
            $logger->log(
                SyslogDocument::LEVEL_INFO,
                'INFO_MAILBOX_CREATED',
                [
                    'source_name' => get_class($incoming),
                    'source_id' => Mailbox::NAME_INCOMING,
                ]
            );
        }
        if (!$replies) {
            $replies = $imap->createMailbox(Mailbox::NAME_REPLIES);
            $logger->log(
                SyslogDocument::LEVEL_INFO,
                'INFO_MAILBOX_CREATED',
                [
                    'source_name' => get_class($replies),
                    'source_id' => Mailbox::NAME_REPLIES,
                ]
            );
        }
        if (!$bounces) {
            $bounces = $imap->createMailbox(Mailbox::NAME_BOUNCES);
            $logger->log(
                SyslogDocument::LEVEL_INFO,
                'INFO_MAILBOX_CREATED',
                [
                    'source_name' => get_class($bounces),
                    'source_id' => Mailbox::NAME_BOUNCES,
                ]
            );
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
                    if ($exitRequested)
                        break 2;

                    $letter = $imap->getLetter($box->getName(), $uid);
                    $logger->log(
                        SyslogDocument::LEVEL_INFO,
                        'INFO_LETTER_AUTODELETED',
                        [
                            'source_name' => get_class($letter),
                            'source_id' => $uid,
                        ]
                    );
                    $imap->deleteLetter($box->getName(), $uid);
                }
            }
        }
        if ($exitRequested)
            return;

        foreach ($toSearch as $box) {
            $messages = $imap->search($box->getName(), 0, 0, 'SINCE "' . $oldDate . '"');
            if (count($messages)) {
                foreach ($messages as $uid) {
                    if ($exitRequested)
                        break 2;

                    $letter = $imap->getLetter($box->getName(), $uid);
                    $logger->log(
                        SyslogDocument::LEVEL_INFO,
                        'INFO_LETTER_PROCESSED',
                        [
                            'source_name' => get_class($letter),
                            'source_id' => $uid
                        ]
                    );
                    $imap->moveLetter($uid, $box->getName(), Mailbox::NAME_INCOMING);
                }
            }
        }
        if ($exitRequested)
            return;
    }
}
