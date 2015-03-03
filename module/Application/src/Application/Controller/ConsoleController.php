<?php
/**
 * CorpNews
 *
 * @link        https://github.com/basarevych/corpnews
 * @copyright   Copyright (c) 2015 basarevych@gmail.com
 * @license     http://choosealicense.com/licenses/mit/ MIT
 */

namespace Application\Controller;

use Zend\Mvc\Controller\AbstractConsoleController;
use Application\Entity\Setting as SettingEntity;
use Application\Model\Mailbox;

/**
 * Console controller
 *
 * @category    Application
 * @package     Controller
 */
class ConsoleController extends AbstractConsoleController
{
    /**
     * @const MESSAGES_PER_CYCLE        Number of messages to process per Cron invocation
     */
    const MESSAGES_PER_CYCLE = 100;

    /**
     * Cron script action template
     */
    public function cronAction()
    {
        // Ensure there is only one cron script running at a time.
        $fpSingleton = fopen(__FILE__, "r") or die("Could not open " . __FILE__);
        if (!flock($fpSingleton, LOCK_EX | LOCK_NB)) {
            fclose($fpSingleton);
            return "Another cron job is running" . PHP_EOL;
        }

        $console = $this->getConsole();
        $request = $this->getRequest();
        $verbose = $request->getParam('verbose');
        $dryRun = $request->getParam('dry-run');

        if ($verbose)
            $console->writeLine('===> Verbose mode enabled');
        if ($verbose && $dryRun)
            $console->writeLine('===> Dry run mode enabled');
        if ($verbose)
            $console->writeLine();

        $sl = $this->getServiceLocator();
        $imap = $sl->get('ImapClient');
        $em = $sl->get('Doctrine\ORM\EntityManager');

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

        if ($verbose) {
            $console->writeLine('===> Check mailboxes are created');
            $console->writeLine();
        }

        if (!$incoming) {
            if ($verbose) {
                $console->writeLine('=> "Incoming" mailbox does not exist. Creating...');
                $console->writeLine();
            }
            if (!$dryRun)    
                $incoming = $imap->createMailbox(Mailbox::NAME_INCOMING);
        }
        if (!$replies) {
            if ($verbose) {
                $console->writeLine('=> "Replies" mailbox does not exist. Creating...');
                $console->writeLine();
            }
            if (!$dryRun)    
                $replies = $imap->createMailbox(Mailbox::NAME_REPLIES);
        }
        if (!$bounces) {
            if ($verbose) {
                $console->writeLine('=> "Bounces" mailbox does not exist. Creating...');
                $console->writeLine();
            }
            if (!$dryRun)    
                $bounces = $imap->createMailbox(Mailbox::NAME_BOUNCES);
        }

        $autodelete = $em->getRepository('Application\Entity\Setting')
                         ->getValue('MailboxAutodelete');
        if (!$autodelete)
            throw new \Exception('MailboxAutodelete setting does not exist');

        $oldDate = new \DateTime();
        $oldDate->sub(new \DateInterval('P' . $autodelete . 'D'));
        $oldDate = \Application\Tool\Date::toImapString($oldDate);

        if ($verbose) {
            $console->writeLine('===> Autodelete is set to ' . $autodelete . ' day(s)');
            $console->writeLine('=> Calculated search date: ' . $oldDate);
            $console->writeLine();
        }

        if ($verbose) {
            $console->writeLine('===> Deleting messages older than ' . $autodelete . ' day(s)');
            $console->writeLine();
        }

        foreach ($boxes as $box) {
            if ($verbose) {
                $console->writeLine('=> Searching box: ' . $box->getName());
            }

            $messages = $imap->search($box->getName(), 0, 0, 'BEFORE "' . $oldDate . '"');
            if (count($messages)) {
                $toDo = min(count($messages), self::MESSAGES_PER_CYCLE);
                if ($verbose) {
                    $console->writeLine('   Processing ' . $toDo . ' out of ' . count($messages) . ' found letter(s)');
                    $console->writeLine();
                }
                for ($i = 0; $i < $toDo; $i++) {
                    if ($verbose) {
                        $letter = $imap->getLetter($box->getName(), $messages[$i]);
                        $console->writeLine('* ' . $letter->getSubject());
                        $console->writeLine();
                    }
                    if (!$dryRun)
                        $imap->deleteLetter($box->getName(), $messages[$i]);
                }
            } else {
                if ($verbose) {
                    $console->writeLine('   Nothing found');
                    $console->writeLine();
                }
            }
        }

        if ($verbose) {
            $console->writeLine('===> Analyzing new messages');
            $console->writeLine();
        }

        foreach ($toSearch as $box) {
            if ($verbose) {
                $console->writeLine('=> Searching box: ' . $box->getName());
            }

            $messages = $imap->search($box->getName(), 0, 0, 'SINCE "' . $oldDate . '"');
            if (count($messages)) {
                $toDo = min(count($messages), self::MESSAGES_PER_CYCLE);
                if ($verbose) {
                    $console->writeLine('   Processing ' . $toDo . ' out of ' . count($messages) . ' found letter(s)');
                    $console->writeLine();
                }
                for ($i = 0; $i < $toDo; $i++) {
                    if ($verbose) {
                        $letter = $imap->getLetter($box->getName(), $messages[$i]);
                        $console->writeLine('* ' . $letter->getSubject());
                        $console->writeLine();
                    }
                    if (!$dryRun)
                        $imap->moveLetter($messages[$i], $box->getName(), Mailbox::NAME_INCOMING);
                }
            } else {
                if ($verbose) {
                    $console->writeLine('   Nothing found');
                    $console->writeLine();
                }
            }
        }

        if ($verbose)
            $console->writeLine('===> All done');
    }

    /**
     * Populate the database
     */
    public function populateDbAction()
    {
        $sl = $this->getServiceLocator();
        $em = $sl->get('Doctrine\ORM\EntityManager');

        $autodelete = $em->getRepository('Application\Entity\Setting')
                         ->findOneByName('MailboxAutodelete');
        if (!$autodelete) {
            $autodelete = new SettingEntity();
            $autodelete->setName('MailboxAutodelete');
            $autodelete->setType(SettingEntity::TYPE_INTEGER);
            $autodelete->setValueInteger(30);

            $em->persist($autodelete);
            $em->flush();
        }
    }
} 
