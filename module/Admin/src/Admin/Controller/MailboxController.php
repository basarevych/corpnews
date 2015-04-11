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
use Application\Exception\NotFoundException;
use Application\Model\Mailbox;
use Application\Entity\Campaign as CampaignEntity;
use Application\Entity\Template as TemplateEntity;
use Admin\DynamicTable\MailboxAdapter;
use Admin\Form\MailConfirm as MailConfirmForm;

/**
 * Mailbox controller
 *
 * @category    Admin
 * @package     Controller
 */
class MailboxController extends AbstractActionController
{
    /**
     * Index action
     *
     * @return ViewModel
     */
    public function indexAction()
    {
        $sl = $this->getServiceLocator();
        $config = $sl->get('Config');

        $boxes = [
            [
                'name'  => Mailbox::NAME_INCOMING,
                'id'    => preg_replace('/[^A-Za-z0-9]/', '', Mailbox::NAME_INCOMING),
            ],
            [
                'name'  => Mailbox::NAME_REPLIES,
                'id'    => preg_replace('/[^A-Za-z0-9]/', '', Mailbox::NAME_REPLIES),
            ],
            [
                'name'  => Mailbox::NAME_BOUNCES,
                'id'    => preg_replace('/[^A-Za-z0-9]/', '', Mailbox::NAME_BOUNCES),
            ],
        ];

        return new ViewModel([
            'mailboxes' => $boxes,
            'ourEmail' => $config['corpnews']['server']['address'],
        ]);
    }

    /**
     * Table data retrieving action
     *
     * @return JsonModel
     */
    public function letterTableAction()
    {
        $boxName = $this->params()->fromQuery('box');
        if (!$boxName)
            throw new \Exception('No "box" parameter');

        $table = $this->createTable();
        $this->connectTableData($boxName, $table);

        $query = $this->params()->fromQuery('query');
        switch ($query) {
        case 'describe':
            $data = $table->describe();
            break;
        case 'data':
            $data = $table->setPageParams($_GET)->fetch();
            break;
        default:
            throw new \Exception('Unknown query type: ' . $query);
        }

        $data['success'] = true;
        return new JsonModel($data);
    }

    /**
     * Count unseen email action
     *
     * @return JsonModel
     */
    public function countNewAction()
    {
        $boxName = $this->params()->fromQuery('box');
        if (!$boxName)
            throw new \Exception('No "box" parameter');

        $sl = $this->getServiceLocator();
        $imap = $sl->get('ImapClient');

        return new JsonModel([
            'num' => $imap->getUnseenLetterCount($boxName),
        ]);
    }

    /**
     * Create campaign form action
     *
     * @return ViewModel
     */
    public function createCampaignAction()
    {
        $box = $this->params()->fromQuery('box');
        if (!$box)
            $box = $this->params()->fromPost('box');
        if (!$box)
            throw new \Exception('No "box" parameter');

        $uid = $this->params()->fromQuery('uid');
        if (!$uid)
            $uid = $this->params()->fromPost('uid');
        if (!$uid)
            throw new \Exception('No "uid" parameter');

        $sl = $this->getServiceLocator();
        $imap = $sl->get('ImapClient');
        $parser = $sl->get('Parser');
        $em = $sl->get('Doctrine\ORM\EntityManager');
        $basePath = $sl->get('viewhelpermanager')->get('basePath');
        $translate = $sl->get('viewhelpermanager')->get('translate');

        $letters = [];
        if ($uid == '_all') {
            foreach ($imap->getLetters($box) as $letter)
                $letters[] = $letter;
        } else {
            foreach (explode(',', $uid) as $item) {
                $letter = $imap->getLetter($box, $item);
                if ($letter)
                    $letters[] = $letter;
            }
        }

        if (count($letters) == 0)
            throw new NotFoundException('No letters found');

        $parseError = false;
        foreach ($letters as $letter) {
            $analysisSuccess = $imap->loadLetter($letter, $box, $uid);
            $syntaxSuccess = $parser->checkSyntax($letter->getHtmlMessage(), $output, true);
            if ($syntaxSuccess)
                $syntaxSuccess = $parser->checkSyntax($letter->getTextMessage(), $output, false);
            if ($syntaxSuccess)
                $syntaxSuccess = $parser->checkSyntax($letter->getSubject(), $output, true);

            if (!$analysisSuccess || !$syntaxSuccess) {
                $parseError = true;
                break;
            }
        }

        $script = null;
        $form = new MailConfirmForm();
        $messages = [];

        $request = $this->getRequest();
        if ($request->isPost()) {
            $form->setData($request->getPost());

            if ($form->isValid()) {
                if (!$parseError) {
                    $subject = $letters[0]->getSubject();

                    $campaign = new CampaignEntity();
                    $campaign->setName(strlen($subject) ? $subject : $translate("(No subject)"));
                    $campaign->setStatus(CampaignEntity::STATUS_CREATED);
                    $campaign->setWhenCreated(new \DateTime());
                    $em->persist($campaign);

                    foreach ($letters as $letter) {
                        $template = new TemplateEntity();
                        $template->setSubject($letter->getSubject());
                        $template->setHeaders($letter->getRawHeaders());
                        $template->setBody($letter->getRawBody());
                        $em->persist($template);

                        $campaign->addTemplate($template);
                        $template->setCampaign($campaign);
                    }

                    $em->flush();

                    $script = "$('#modal-form').modal('hide'); "
                        . "window.location = '"
                        . $basePath('/admin/campaign')
                        . "'";
                } else {
                    $script = "$('#modal-form').modal('hide'); reloadTables()";
                }
            }
        } else {
            $form->setData([
                'box' => $box,
                'uid' => $uid
            ]);
        }

        $model = new ViewModel([
            'script'        => $script,
            'form'          => $form,
            'messages'      => $messages,
            'parseError'    => $parseError,
        ]);
        $model->setTerminal(true);
        return $model;
    }

    /**
     * Delete letter form action
     *
     * @return ViewModel
     */
    public function deleteLetterAction()
    {
        $box = $this->params()->fromQuery('box');
        if (!$box)
            $box = $this->params()->fromPost('box');
        if (!$box)
            throw new \Exception('No "box" parameter');

        $uid = $this->params()->fromQuery('uid');
        if (!$uid)
            $uid = $this->params()->fromPost('uid');
        if (!$uid)
            throw new \Exception('No "uid" parameter');

        $sl = $this->getServiceLocator();
        $imap = $sl->get('ImapClient');

        $script = null;
        $form = new MailConfirmForm();
        $messages = [];

        $request = $this->getRequest();
        if ($request->isPost()) {
            $form->setData($request->getPost());

            if ($form->isValid()) {
                if ($uid == '_all') {
                    foreach ($imap->getLetters($box) as $letter)
                        $imap->deleteLetter($box, $letter->getUid());
                } else {
                    foreach (explode(',', $uid) as $item) {
                        $letter = $imap->getLetter($box, $item);
                        if ($letter)
                            $imap->deleteLetter($box, $item);
                    }
                }

                $script = "$('#modal-form').modal('hide'); reloadTables()";
            }
        } else {
            $form->setData([
                'box' => $box,
                'uid' => $uid
            ]);
        }

        $model = new ViewModel([
            'script'    => $script,
            'form'      => $form,
            'messages'  => $messages,
        ]);
        $model->setTerminal(true);
        return $model;
    }

    /**
     * Reanalyze letter form action
     *
     * @return ViewModel
     */
    public function reanalyzeLetterAction()
    {
        $box = $this->params()->fromQuery('box');
        if (!$box)
            $box = $this->params()->fromPost('box');
        if (!$box)
            throw new \Exception('No "box" parameter');

        $uid = $this->params()->fromQuery('uid');
        if (!$uid)
            $uid = $this->params()->fromPost('uid');
        if (!$uid)
            throw new \Exception('No "uid" parameter');

        $sl = $this->getServiceLocator();
        $imap = $sl->get('ImapClient');

        $script = null;
        $form = new MailConfirmForm();
        $messages = [];

        $request = $this->getRequest();
        if ($request->isPost()) {
            $form->setData($request->getPost());

            if ($form->isValid()) {
                if ($uid == '_all') {
                    foreach ($imap->getLetters($box) as $letter)
                        $imap->moveLetter($letter->getUid(), $box, Mailbox::NAME_INBOX);
                } else {
                    foreach (explode(',', $uid) as $item) {
                        $letter = $imap->getLetter($box, $item);
                        if ($letter)
                            $imap->moveLetter($item, $box, Mailbox::NAME_INBOX);
                    }
                }

                $script = "$('#modal-form').modal('hide'); reloadTables()";
            }
        } else {
            $form->setData([
                'box' => $box,
                'uid' => $uid
            ]);
        }

        $model = new ViewModel([
            'script'    => $script,
            'form'      => $form,
            'messages'  => $messages,
        ]);
        $model->setTerminal(true);
        return $model;
    }

    /**
     * Create Table object
     *
     * @return Table
     */
    protected function createTable()
    {
        $sl = $this->getServiceLocator();
        $translate = $sl->get('viewhelpermanager')->get('translate');

        $table = new Table();

        $table->setColumns([
            'uid' => [
                'title'     => $translate('UID'),
                'type'      => Table::TYPE_INTEGER,
                'filters'   => [],
                'sortable'  => false,
                'visible'   => false,
            ],
            'date' => [
                'title'     => $translate('Date'),
                'type'      => Table::TYPE_DATETIME,
                'filters'   => [ Table::FILTER_BETWEEN ],
                'sortable'  => true,
                'visible'   => true,
            ],
            'from' => [
                'title'     => $translate('From'),
                'type'      => Table::TYPE_STRING,
                'filters'   => [ Table::FILTER_LIKE ],
                'sortable'  => true,
                'visible'   => true,
            ],
            'subject' => [
                'title'     => $translate('Subject'),
                'type'      => Table::TYPE_STRING,
                'filters'   => [ Table::FILTER_LIKE ],
                'sortable'  => true,
                'visible'   => true,
            ],
        ]);

        return $table;
    }

    /**
     * Create adapter and mapper
     *
     * @param string $boxName
     * @param Table $table
     */
    protected function connectTableData($boxName, $table)
    {
        $sl = $this->getServiceLocator();
        $imap = $sl->get('ImapClient');
        $escapeHtml = $sl->get('viewhelpermanager')->get('escapeHtml');

        $adapter = new MailboxAdapter();
        $adapter->setImapClient($imap);
        $adapter->setMailbox($boxName);

        $mapper = function ($uid) use ($boxName, $imap, $escapeHtml) {
            $letter = $imap->getLetter($boxName, $uid);

            $date = $letter->getDate();
            if ($date !== null)
                $date = $date->getTimestamp();

            return [
                'seen'      => $imap->isLetterSeen($boxName, $uid),
                'uid'       => $uid,
                'date'      => $date,
                'from'      => $escapeHtml($letter->getFrom()),
                'subject'   => $escapeHtml($letter->getSubject()),
            ];
        };

        $table->setAdapter($adapter);
        $table->setMapper($mapper);
    }
}
