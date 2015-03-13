<?php
/**
 * CorpNews
 *
 * @link        https://github.com/basarevych/corpnews
 * @copyright   Copyright (c) 2014 basarevych@gmail.com
 * @license     http://choosealicense.com/licenses/mit/ MIT
 */

namespace Admin\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\View\Model\JsonModel;
use DynamicTable\Table;
use DynamicTable\Adapter\DoctrineORMAdapter;
use Application\Exception\NotFoundException;
use Application\Entity\Campaign as CampaignEntity;
use Application\Form\Confirm as ConfirmForm;
use Admin\Form\CreateCampaign as CreateCampaignForm;

/**
 * Campaign controller
 *
 * @category    Admin
 * @package     Controller
 */
class CampaignController extends AbstractActionController
{
    /**
     * Index action
     */
    public function indexAction()
    {
        return new ViewModel();
    }

    /**
     * Create action
     */
    public function createAction()
    {
        $box = $this->params()->fromQuery('box');
        if (!$box)
            throw new \Exception("No 'box' parameter");

        $uid = $this->params()->fromQuery('uid');
        if (!$uid)
            throw new \Exception("No 'uid' parameter");

        return new ViewModel();
    }
}
