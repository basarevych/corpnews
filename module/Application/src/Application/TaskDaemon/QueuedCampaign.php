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

        $error = false;
        foreach ($campaign->getTemplates() as $template) {
            $clients = $em->getRepository('Application\Entity\Client')
                          ->findWithoutLetters($template, 100);

            foreach ($clients as $client) {
                if ($exitRequested)
                    break 2;

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

            $clients = $em->getRepository('Application\Entity\Client')
                          ->findWithFailedLetters($template, 100);

            foreach ($clients as $client) {
                if ($exitRequested)
                    break 2;

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

        if (!$error) {
            $campaign->setStatus(CampaignEntity::STATUS_STARTED);
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
