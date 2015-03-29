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
use Application\Entity\Campaign as CampaignEntity;

/**
 * AllQueuedCampaigns task
 * 
 * @category    Application
 * @package     TaskDaemon
 */
class AllQueuedCampaigns extends ZfTask
{
    /**
     * Do the job
     */
    public function run(&$exitRequested)
    {
        $daemon = $this->getDaemon();
        $sl = $this->getServiceLocator();
        $em = $sl->get('Doctrine\ORM\EntityManager');

        $em->getConnection()->close();
        $em->getConnection()->connect();

        $campaigns = $em->getRepository('Application\Entity\Campaign')
                        ->findBy([ 'status' => CampaignEntity::STATUS_QUEUED ], []);

        foreach ($campaigns as $campaign)
            $daemon->runTask('queued_campaign', $campaign->getId());
    }
}
