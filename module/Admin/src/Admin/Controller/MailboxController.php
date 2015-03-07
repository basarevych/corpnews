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
use Zend\View\Renderer\PhpRenderer;
use Zend\Http\PhpEnvironment\Response;
use HTMLPurifier;
use HTMLPurifier_Config;
use DynamicTable\Table;
use Application\Exception\NotFoundException;
use Application\Exception\BadRequestException;
use Application\Model\Mailbox;
use Admin\DynamicTable\MailboxAdapter;
use Admin\Form\MailConfirmForm;

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
     */
    public function indexAction()
    {
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
        ]);
    }

    /**
     * Table data retrieving action
     */
    public function letterTableAction()
    {
        $boxName = $this->params()->fromQuery('box');

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
     * Support for letter modal action
     *
     * @return JsonModel
     */
    public function letterAction()
    {
        $box = $this->params()->fromQuery('box');
        if (!$box)
            throw new \Exception('No "box" parameter');

        $uid = $this->params()->fromQuery('uid');
        if (!$uid)
            throw new \Exception('No "uid" parameter');

        $sl = $this->getServiceLocator();
        $imap = $sl->get('ImapClient');

        $letter = $imap->getLetter($box, $uid);
        if (!$letter)
            throw new NotFoundException('Letter not found');

        $success = $imap->loadLetter($letter, $box, $uid);

        return new JsonModel([
            'success'       => $success,
            'subject'       => $letter->getSubject(),
            'html'          => $this->prepareHtml($box, $letter),
            'text'          => $this->prepareText($box, $letter),
            'attachments'   => $this->prepareAttachments($box, $letter),
            'log'           => $this->prepareLog($box, $letter),
            'source'        => $this->prepareSource($box, $letter),
        ]);
    }

    /**
     * Download an attachment
     *
     * @return Response
     */
    public function attachmentAction()
    {
        $box = $this->params()->fromQuery('box');
        if (!$box)
            throw new \Exception('No "box" parameter given');

        $uid = $this->params()->fromQuery('uid');
        if (!$uid)
            throw new \Exception('No "uid" parameter given');

        $cid = $this->params()->fromQuery('cid');
        $filename = $this->params()->fromQuery('filename');
        if (!$cid && !$filename)
            throw new \Exception('No "cid" or "filename" parameter given');

        $sl = $this->getServiceLocator();
        $imap = $sl->get('ImapClient');

        $letter = $imap->getLetter($box, $uid);
        if (!$letter)
            throw new NotFoundException('Letter not found');

        $success = $imap->loadLetter($letter, $box, $uid);
        if (!$success)
            throw new NotFoundException('Attachment not found');

        $att = null;
        $type = 'application/octet-stream';
        foreach ($letter->getAttachments() as $item) {
            if (($cid && $item['cid'] == "<$cid>") || ($filename && $item['name'] == $filename)) {
                $att = $item['data'];
                $type = $item['type'];
                break;
            }
        }
        if (!$att)
            throw new NotFoundException('Attachment not found');

        $result = $att;
        $preview = $this->params()->fromQuery('preview', false);
        if ($preview) {
            $maxWidth = 150;
            $maxHeight = 80;
            $resource = @imagecreatefromstring($att);
            if ($resource === false)
                throw new BadRequestException("Not an image");
            $width = imagesx($resource);
            $height = imagesy($resource);
            $result = $att;

            if ($width > $maxWidth || $height > $maxHeight) {
                if ($width > $maxWidth && $height > $maxHeight) {
                    if ($width > $height) {
                        $newWidth = $maxWidth;
                        $newHeight = $height * $maxWidth / $width;
                    } else {
                        $newHeight = $maxHeight;
                        $newWidth = $width * $maxHeight / $height;
                    }
                } else if ($width > $maxWidth) {
                    $newWidth = $maxWidth;
                    $newHeight = $height * $maxWidth / $width;
                } else if ($height > $maxHeight) {
                    $newHeight = $maxHeight;
                    $newWidth = $width * $maxHeight / $height;
                }
                
                $newImage = imagecreatetruecolor($newWidth, $newHeight);
                imagealphablending($newImage, false);
                imagesavealpha($newImage,true);
                $transparent = imagecolorallocatealpha($newImage, 255, 255, 255, 127);
                imagefilledrectangle($newImage, 0, 0, $newWidth, $newHeight, $transparent);
                imagecopyresampled($newImage, $resource, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
                $resource = $newImage;
                
                ob_start();
                imagepng($resource);
                $result = ob_get_contents();
                ob_end_clean();
            }
        }

        $response = $this->getResponse();
        $response->getHeaders()->addHeaders([
            'Content-Type' => $type,
            'Content-Transfer-Encoding' => 'binary'
        ]);
        $response->setContent($result);
        return $response;
    }

    /**
     * Delete letter form action
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
     * This action is called when requested action is not found
     */
    public function notFoundAction()
    {
        throw new NotFoundException('Action is not found');
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
                'uid'       => $uid,
                'date'      => $date,
                'from'      => $escapeHtml($letter->getFrom()),
                'subject'   => $escapeHtml($letter->getSubject()),
            ];
        };

        $table->setAdapter($adapter);
        $table->setMapper($mapper);
    }

    /**
     * Prepare HTML part of the letter
     *
     * @param string $box
     * @param Letter $letter
     * @return string
     */
    protected function prepareHtml($box, $letter)
    {
        $sl = $this->getServiceLocator();
        $basePath = $sl->get('viewhelpermanager')->get('basePath');

        $config = HTMLPurifier_Config::createDefault();
        $config->set('Cache.DefinitionImpl', null);
        $config->set('URI.DisableExternalResources', true);

        $message = $letter->getHtmlMessage();
        $message = preg_replace_callback(
            '/src="cid:([^"]+)"/U',
            function ($matches) use ($basePath, $box, $letter) {
                return 'src="'
                    . $basePath('/admin/mailbox/attachment')
                    . '?box=' . urlencode($box)
                    . '&uid=' . urlencode($letter->getUid())
                    . '&cid=' . urlencode($matches[1])
                    . '"';
            },
            $message
        );

        $purifier = new HTMLPurifier($config);
        $result = $purifier->purify($message);
        return $result;
    }

    /**
     * Prepare text part of the letter
     *
     * @param string $box
     * @param Letter $letter
     * @return string
     */
    protected function prepareText($box, $letter)
    {
        $sl = $this->getServiceLocator();
        $escapeHtml = $sl->get('viewhelpermanager')->get('escapeHtml');

        $message = $letter->getTextMessage();
        $result = '<p>' . str_replace("\n", "<br>", $escapeHtml($message)) . '</p>';

        return $result;
    }

    /**
     * Prepare attachments table of the letter
     *
     * @param string $box
     * @param Letter $letter
     * @return string
     */
    protected function prepareAttachments($box, $letter)
    {
        $attachments = [];
        foreach ($letter->getAttachments() as $item) {
            $resource = @imagecreatefromstring($item['data']);
            $cid = $item['cid'];
            if (strlen($cid) > 1)
                $cid = substr($cid, 1, strlen($cid) - 2);

            $attachments[] = [
                'is_image'  => ($resource !== false),
                'name'      => $item['name'],
                'type'      => $item['type'],
                'cid'       => $cid,
                'size'      => \Application\Tool\Text::sizeToStr(strlen($item['data'])),
            ];
        }

        $model = new ViewModel(array(
            'box'           => $box,
            'letter'        => $letter,
            'attachments'   => $attachments
        ));
        $model->setTemplate('admin/mailbox/letter-attachments');

        $sl = $this->getServiceLocator();
        $renderer = $sl->get('viewmanager')->getRenderer();
        return $renderer->render($model);
    }

    /**
     * Prepare analysis log of the letter
     *
     * @param string $box
     * @param Letter $letter
     * @return string
     */
    protected function prepareLog($box, $letter)
    {
        $sl = $this->getServiceLocator();
        $escapeHtml = $sl->get('viewhelpermanager')->get('escapeHtml');

        $result = '<div class="pre">' . $escapeHtml($letter->getLog()) . '</div>';

        return $result;
    }

    /**
     * Prepare raw view of the letter
     *
     * @param string $box
     * @param Letter $letter
     * @return string
     */
    protected function prepareSource($box, $letter)
    {
        $sl = $this->getServiceLocator();
        $escapeHtml = $sl->get('viewhelpermanager')->get('escapeHtml');

        $result = '<div class="pre">' . $escapeHtml($letter->getSource()) . '</div>';

        return $result;
    }
}
