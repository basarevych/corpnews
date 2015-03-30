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
        $mail = $sl->get('Mail');
        $logger = $sl->get('Logger');

        $em->getConnection()->close();
        $em->getConnection()->connect();

        $campaigns = $em->getRepository('Application\Entity\Campaign')
                       ->findByStatus(CampaignEntity::STATUS_STARTED);
        if (count($campaigns) == 0)
            return;

        foreach ($campaigns as $campaign) {
            if ($exitRequested)
                break;

            $deadline = $campaign->getWhenDeadline();
            $now = new \DateTime();

            if ($deadline && $now > $deadline) {
                $campaign->setStatus(CampaignEntity::STATUS_FINISHED);
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
        }
    }
}
