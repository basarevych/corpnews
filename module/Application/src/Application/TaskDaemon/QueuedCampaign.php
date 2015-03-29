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

/**
 * QueuedCampaign task
 * 
 * @category    Application
 * @package     TaskDaemon
 */
class QueuedCampaign extends ZfTask
{
    /**
     * Do the job
     */
    public function run(&$exitRequested)
    {
        $data = $this->getData();
        $sl = $this->getServiceLocator();
        $em = $sl->get('Doctrine\ORM\EntityManager');
        $mail = $sl->get('Mail');
        $logger = $sl->get('Logger');

        $em->getConnection()->close();
        $em->getConnection()->connect();

        $campaign = $em->getRepository('Application\Entity\Campaign')
                       ->find($data);
        if (!$campaign)
            return;

        if ($campaign->getStatus() != CampaignEntity::STATUS_QUEUED)
            return;

        $logger->log(
            SyslogDocument::LEVEL_INFO,
            'INFO_CAMPAIGN_BEING_PROCESSED',
            [
                'source_name' => get_class($campaign),
                'source_id' => $campaign->getId()
            ]
        );

        $error = false;
        $noNewLetters = false;
        $noFailedLetters = false;
        foreach ($campaign->getTemplates() as $template) {
            while (!$exitRequested) {
                $clients = $em->getRepository('Application\Entity\Client')
                              ->findWithoutLetters($template, 100);
                if (count($clients) == 0) {
                    $noNewLetters = true;
                    break;
                }

                foreach ($clients as $client) {
                    if ($exitRequested)
                        break 3;

                    $letter = $mail->createFromTemplate($template, $client);
                    if ($letter === false) {
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

                        $error = true;
                        break 2;
                    }

                    $em->persist($letter);
                    $em->flush();
                }
            }

            while (!$exitRequested) {
                $clients = $em->getRepository('Application\Entity\Client')
                              ->findWithFailedLetters($template, 100);
                if (count($clients) == 0) {
                    $noFailedLetters = true;
                    break;
                }

                foreach ($clients as $client) {
                    if ($exitRequested)
                        break 3;

                    $letter = $mail->createFromTemplate($template, $client);
                    if ($letter === false) {
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

                        $error = true;
                        break 2;
                    }

                    $em->persist($letter);
                    $em->flush();
                }
            }
        }

        if (!$error && $noNewLetters && $noFailedLetters) {
            $campaign->setStatus(CampaignEntity::STATUS_STARTED);
            $campaign->setWhenStarted(new \DateTime());
            $em->persist($campaign);
            $em->flush();

            $logger->log(
                SyslogDocument::LEVEL_INFO,
                'INFO_CAMPAIGN_STARTED',
                [
                    'source_name' => get_class($campaign),
                    'source_id' => $campaign->getId()
                ]
            );
        }
    }
}
