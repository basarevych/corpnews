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
/*
        // Ensure there is only one cron script running at a time.
        $fpSingleton = fopen(__FILE__, "r") or die("Could not open " . __FILE__);
        if (!flock($fpSingleton, LOCK_EX | LOCK_NB)) {
            fclose($fpSingleton);
            return "Another cron job is running" . PHP_EOL;
        }
*/
        $sl = $this->getServiceLocator();
        $task = $sl->get('TaskDaemon');
        $task->getDaemon()->start();
        $task->getDaemon()->runTask('check_email');
        $task->getDaemon()->runTask('send_email');
    }

    /**
     * Run background task
     */
    public function runTaskAction()
    {
        $request = $this->getRequest();
        $name = $request->getParam('name');
        $data = $request->getParam('data');

        $sl = $this->getServiceLocator();
        $config = $sl->get('Config');

        $found = false;
        foreach ($config['corpnews']['task_daemon']['tasks'] as $task => $class) {
            if ($task == $name) {
                $found = true;
                break;
            }
        }

        if (!$found) {
            $console = $this->getConsole();
            $console->writeLine("Unknown task");
            return;
        }

        $task = $sl->get('TaskDaemon');
        $task->getDaemon()->start();
        $task->getDaemon()->runTask($name, $data);
    }

    /**
     * Stop the task daemon action
     */
    public function stopDaemonAction()
    {
        $sl = $this->getServiceLocator();
        $task = $sl->get('TaskDaemon');
        $task->getDaemon()->stop();
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

        $mailInterval = $em->getRepository('Application\Entity\Setting')
                           ->findOneByName('MailInterval');
        if (!$mailInterval) {
            $mailInterval = new SettingEntity();
            $mailInterval->setName('MailInterval');
            $mailInterval->setType(SettingEntity::TYPE_INTEGER);
            $mailInterval->setValueInteger(5);

            $em->persist($mailInterval);
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

    /**
     * Reset demo-version action
     */
    public function resetDemoAction()
    {
    }
} 
