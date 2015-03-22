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
use Application\Entity\Group as GroupEntity;
use Application\Entity\Campaign as CampaignEntity;
use Application\Document\Syslog as SyslogDocument;
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
     * @const CRON_DURATION
     */
    const CRON_DURATION = 50;

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

        $startTime = time();
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
        $mail = $sl->get('Mail');
        $logger = $sl->get('Logger');
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
        $oldDate->setTimezone(new \DateTimeZone('GMT'));
        $oldDate->sub(new \DateInterval('P' . $autodelete . 'D'));
        $oldDate->setTime(0, 0, 0);
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
            if ($verbose)
                $console->writeLine('=> Searching box: ' . $box->getName());

            $messages = $imap->search($box->getName(), 0, 0, 'BEFORE "' . $oldDate . '"');
            if (count($messages)) {
                foreach ($messages as $uid) {
                    if ($verbose) {
                        $letter = $imap->getLetter($box->getName(), $uid);
                        $console->writeLine('* ' . $letter->getSubject());
                        $console->writeLine();
                    }
                    if (!$dryRun)
                        $imap->deleteLetter($box->getName(), $uid);

                    if (time() - $startTime >= self::CRON_DURATION) {
                        if ($verbose)
                            $console->writeLine('===> Time limit reached - exiting');
                        return;
                    }
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
            if ($verbose)
                $console->writeLine('=> Searching box: ' . $box->getName());

            $messages = $imap->search($box->getName(), 0, 0, 'SINCE "' . $oldDate . '"');
            if (count($messages)) {
                foreach ($messages as $uid) {
                    if ($verbose) {
                        $letter = $imap->getLetter($box->getName(), $uid);
                        $console->writeLine('* ' . $letter->getSubject());
                        $console->writeLine();
                    }
                    if (!$dryRun)
                        $imap->moveLetter($uid, $box->getName(), Mailbox::NAME_INCOMING);

                    if (time() - $startTime >= self::CRON_DURATION) {
                        if ($verbose)
                            $console->writeLine('===> Time limit reached - exiting');
                        return;
                    }
                }
            } else {
                if ($verbose) {
                    $console->writeLine('   Nothing found');
                    $console->writeLine();
                }
            }
        }

        if ($verbose) {
            $console->writeLine('===> Creating letters');
            $console->writeLine();
        }

        $campaigns = $em->getRepository('Application\Entity\Campaign')
                        ->findBy([ 'status' => CampaignEntity::STATUS_QUEUED ]);

        if (count($campaigns) > 0) {
            foreach ($campaigns as $campaign) {
                if ($verbose) {
                    $console->writeLine('=> Processing campaign: ' . $campaign->getName());
                    $console->writeLine();
                }
                foreach ($campaign->getTemplates() as $template) {
                    if ($verbose)
                        $console->writeLine('=> Processing template: ' . $template->getSubject());

                    $clients = $em->getRepository('Application\Entity\Client')
                                  ->findWithoutLetters($template);

                    foreach ($clients as $client) {
                        if ($verbose)
                            $console->writeLine('*  No letter for: ' . $client->getEmail() . ' - creating');

                        if (!$dryRun) {
                            $letter = $mail->createFromTemplate($template, $client);
                            if ($letter === false) {
                                if ($verbose)
                                    $console->writeLine('*  createFromTemplate() failed for: ' . $client->getEmail());

                                $campaign->setStatus(CampaignEntity::STATUS_PAUSED);
                                $em->persist($campaign);
                                $em->flush();

                                $logger->log(
                                    SyslogDocument::LEVEL_CRITICAL,
                                    'ERROR_CAMPAIGN_PAUSED',
                                    [
                                        'source_name' => get_class($campaign),
                                        'source_id' => $campaign->getId()
                                    ]
                                );

                                break 2;
                            }

                            $em->persist($letter);
                            $em->flush();
                        }

                        if (time() - $startTime >= self::CRON_DURATION) {
                            if ($verbose) {
                                $console->writeLine();
                                $console->writeLine('===> Time limit reached - exiting');
                            }
                            return;
                        }
                    }

                    $clients = $em->getRepository('Application\Entity\Client')
                                  ->findWithFailedLetters($template);

                    foreach ($clients as $client) {
                        if ($verbose)
                            $console->writeLine('*  Restarting failed letter for: ' . $client->getEmail() . ' - creating');

                        if (!$dryRun) {
                            $letter = $mail->createFromTemplate($template, $client);
                            if ($letter === false) {
                                if ($verbose)
                                    $console->writeLine('*  createFromTemplate() failed for: ' . $client->getEmail());

                                $campaign->setStatus(CampaignEntity::STATUS_PAUSED);
                                $em->persist($campaign);
                                $em->flush();

                                $logger->log(
                                    SyslogDocument::LEVEL_CRITICAL,
                                    'ERROR_CAMPAIGN_PAUSED',
                                    [
                                        'source_name' => get_class($campaign),
                                        'source_id' => $campaign->getId()
                                    ]
                                );

                                break 2;
                            }

                            $em->persist($letter);
                            $em->flush();
                        }

                        if (time() - $startTime >= self::CRON_DURATION) {
                            if ($verbose) {
                                $console->writeLine();
                                $console->writeLine('===> Time limit reached - exiting');
                            }
                            return;
                        }
                    }

                    if ($verbose)
                        $console->writeLine();
                }
            }
        } else {
            if ($verbose) {
                $console->writeLine('   Nothing found');
                $console->writeLine();
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

        foreach (GroupEntity::getSystemNames() as $name) {
            $group = $em->getRepository('Application\Entity\Group')
                        ->findOneByName($name);
            if (!$group) {
                $group = new GroupEntity();
                $group->setName($name);

                $em->persist($group);
                $em->flush();
            }
        }
    }

    /**
     * Check and repair database action
     */
    public function checkDbAction()
    {
        $console = $this->getConsole();
        $request = $this->getRequest();
        $repair = $request->getParam('repair') != null;

        $sl = $this->getServiceLocator();
        $dfm = $sl->get('DataFormManager');
        $em = $sl->get('Doctrine\ORM\EntityManager');
        $dm = $this->getServiceLocator()->get('doctrine.documentmanager.odm_default');

        $clients = $em->getRepository('Application\Entity\Client')
                      ->findAll();
        foreach ($clients as $client) {
            foreach ($dfm->getNames() as $name) {
                $class = $dfm->getDocumentClass($name);
                $doc = $dm->getRepository($class)
                          ->find($client->getId());
                if (!$doc) {
                    $console->writeLine();
                    $console->writeLine('===> Error: missing document');
                    $console->writeLine('* Class: ' . $class);
                    $console->writeLine('* ID: ' . $client->getId());
                    $console->writeLine('* Email: ' . $client->getEmail());
                    if ($repair) {
                        $console->writeLine('=> Creating...');
                        $doc = new $class();
                        $doc->setId($client->getId());
                        $doc->setClientEmail($client->getEmail());
                        $dm->persist($doc);
                        $dm->flush();
                    }
                } else if ($client->getEmail() != $doc->getClientEmail()) {
                    $console->writeLine();
                    $console->writeLine('===> Error: document email does not match client email');
                    $console->writeLine('* Class: ' . $class);
                    $console->writeLine('* ID: ' . $client->getId());
                    $console->writeLine('* Client Email: ' . $client->getEmail());
                    $console->writeLine('* Document Email: ' . $doc->getClientEmail());
                    if ($repair) {
                        $console->writeLine('=> Fixing...');
                        $doc->setClientEmail($client->getEmail());
                        $dm->persist($doc);
                        $dm->flush();
                    }
                }
            }
        }

        foreach ($dfm->getNames() as $name) {
            $class = $dfm->getDocumentClass($name);
            $docs = $dm->getRepository($class)
                       ->findAll();
            foreach ($docs as $doc) {
                $client = $em->getRepository('Application\Entity\Client')
                             ->find($doc->getId());
                if (!$client) {
                    $console->writeLine();
                    $console->writeLine('===> Error: orphan document');
                    $console->writeLine('* Class: ' . $class);
                    $console->writeLine('* ID: ' . $doc->getId());
                    $console->writeLine('* Email: ' . $doc->getClientEmail());
                    if ($repair) {
                        $console->writeLine('=> Deleting...');
                        $dm->remove($doc);
                        $dm->flush();
                    }
                }
            }
        }
    }
} 
