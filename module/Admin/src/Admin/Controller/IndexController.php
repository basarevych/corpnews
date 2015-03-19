<?php
/**
 * CorpNews
 *
 * @link        https://github.com/basarevych/corpnews
 * @copyright   Copyright (c) 2015 basarevych@gmail.com
 * @license     http://choosealicense.com/licenses/mit/ MIT
 */

namespace Admin\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\View\Model\JsonModel;
use Application\Exception\NotFoundException;
use Application\Entity\Campaign as CampaignEntity;
use Application\Document\Syslog as SyslogDocument;

/**
 * Index controller
 *
 * @category    Admin
 * @package     Controller
 */
class IndexController extends AbstractActionController
{
    /**
     * Index action
     */
    public function indexAction()
    {
        $sl = $this->getServiceLocator();
        $em = $sl->get('Doctrine\ORM\EntityManager');
        $repoCampaign = $em->getRepository('Application\Entity\Campaign');
        $dm = $this->getServiceLocator()->get('doctrine.documentmanager.odm_default');
        $repoSyslog = $dm->getRepository('Application\Document\Syslog');

        $statuses = [];
        foreach (CampaignEntity::getStatuses() as $status)
            $statuses[$status] = $repoCampaign->getStatusCount($status);

        $docs = $repoSyslog->findAllByLevel(SyslogDocument::LEVEL_ERROR, 5);

        return new ViewModel([
            'statuses'  => $statuses,
            'log'       => $docs,
        ]);
    }

    /**
     * This action is called when requested action is not found
     */
    public function notFoundAction()
    {
        throw new NotFoundException('Action is not found');
    }
}
