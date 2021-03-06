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
use Application\Entity\Campaign as CampaignEntity;
use Application\Entity\Letter as LetterEntity;

/**
 * SendEmail task
 * 
 * @category    Application
 * @package     TaskDaemon
 */
class SendEmail extends ZfTask
{
    /**
     * Do the job
     */
    public function run(&$exitRequested)
    {
        $sl = $this->getServiceLocator();
        $em = $sl->get('Doctrine\ORM\EntityManager');
        $dm = $sl->get('doctrine.documentmanager.odm_default');
        $mail = $sl->get('Mail');
        $logger = $sl->get('Logger');
        $dfm = $sl->get('DataFormManager');

        $em->getConnection()->close();
        $em->getConnection()->connect();

        $campaigns = $em->getRepository('Application\Entity\Campaign')
                       ->findByStatus(CampaignEntity::STATUS_STARTED);
        if (count($campaigns) == 0)
            return;

        $mailInterval = $em->getRepository('Application\Entity\Setting')
                           ->getValue('MailInterval');
        if (!$mailInterval)
            return;

        foreach ($campaigns as $campaign) {
            if ($exitRequested)
                break;

            $deadline = $campaign->getWhenDeadline();
            $now = new \DateTime();

            if ($deadline && $now > $deadline) {
                $campaign->setStatus(CampaignEntity::STATUS_ARCHIVED);
                $campaign->setWhenFinished(new \DateTime());
                $em->persist($campaign);
                $em->flush();

                $logger->log(
                    SyslogDocument::LEVEL_INFO,
                    'INFO_CAMPAIGN_PASSED_DEADLINE',
                    [
                        'source_name' => get_class($campaign),
                        'source_id' => $campaign->getId()
                    ]
                );
                continue;
            }

            if (count($campaign->getTemplates()) == 0) {
                $campaign->setStatus(CampaignEntity::STATUS_PAUSED);
                $em->persist($campaign);

                $logger->log(
                    SyslogDocument::LEVEL_CRITICAL,
                    'ERROR_CAMPAIGN_PAUSED',
                    [
                        'source_name' => get_class($campaign),
                        'source_id' => $campaign->getId()
                    ]
                );

                continue;
            }

            while (!$exitRequested) {
                $totalPending = 0;

                foreach ($campaign->getTemplates() as $template) {
                    if ($exitRequested)
                        break 3;

                    $letters = $em->getRepository('Application\Entity\Letter')
                                  ->findPending($template, 100);
                    $totalPending += count($letters);

                    foreach ($letters as $letter) {
                        if ($exitRequested)
                            break 4;

                        $skip = false;
                        if ($letter->getClient()->getBounced()) {
                            $skip = true;
                        } else {
                            $class = $dfm->getDocumentClass('subscription');
                            if ($class) {
                                $repo = $dm->getRepository($class);
                                if ($repo) {
                                    $doc = $repo->find($letter->getClient()->getId());
                                    if ($doc) {
                                        if ($doc->getUnsubscribed() === true) {
                                            $skip = true;
                                        } else if (count($campaign->getTags()) > 0 && is_array($doc->getIgnoredTags())) {
                                            $someNotIgnored = false;
                                            foreach ($campaign->getTags() as $tag) {
                                                if (!in_array($tag->getId(), $doc->getIgnoredTags())) {
                                                    $someNotIgnored = true;
                                                    break;
                                                }
                                            }
                                            $skip = !$someNotIgnored;
                                        }
                                    }
                                }
                            }
                        }

                        if ($skip) {
                            $letter->setStatus(LetterEntity::STATUS_SKIPPED);
                            $em->persist($letter);
                            $em->flush();
                            continue;
                        }

                        if (!$mail->sendLetter($letter)) {
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

                            break 3;
                        }

                        sleep($mailInterval);

                        $test = $em->getRepository('Application\Entity\Campaign')
                                   ->find($campaign->getId());
                        if (!$test || $test->getStatus() != CampaignEntity::STATUS_STARTED)
                            break 3;
                    }
                }

                if ($totalPending == 0) {
                    $campaign->setStatus(CampaignEntity::STATUS_FINISHED);
                    $campaign->setWhenFinished(new \DateTime());
                    $em->persist($campaign);
                    $em->flush();

                    $logger->log(
                        SyslogDocument::LEVEL_INFO,
                        'INFO_CAMPAIGN_DONE',
                        [
                            'source_name' => get_class($campaign),
                            'source_id' => $campaign->getId()
                        ]
                    );

                    break;
                }
            }
        }
    }
}
