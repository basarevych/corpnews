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
use Application\Exception\NotFoundException;
use Application\Exception\BadRequestException;
use Application\Model\Letter as LetterModel;

/**
 * Letter controller
 *
 * @category    Admin
 * @package     Controller
 */
class LetterController extends AbstractActionController
{
    /**
     * Support for letter modal action
     *
     * @return JsonModel
     */
    public function showLetterAction()
    {
        $box = $this->params()->fromQuery('box');
        $uid = $this->params()->fromQuery('uid');
        $template = $this->params()->fromQuery('template');

        if ((!$box || !$uid) && !$template)
            throw new \Exception('No "box/uid" or "template" parameter');

        $sl = $this->getServiceLocator();
        $imap = $sl->get('ImapClient');
        $parser = $sl->get('Parser');
        $em = $sl->get('Doctrine\ORM\EntityManager');

        if ($box && $uid) {
            $letter = $imap->getLetter($box, $uid);
            if (!$letter)
                throw new NotFoundException('Letter not found');

            $params = [ 'box' => $box ];
            $analysisSuccess = $imap->loadLetter($letter, $box, $uid);
        } else if ($template) {
            $template = $em->getRepository('Application\Entity\Template')
                           ->find($template);
            if (!$template)
                throw new NotFoundException('Template not found');

            $params = [ 'template' => $template->getId() ];
            $letter = new LetterModel(null);
            $analysisSuccess = $letter->load($template->getHeaders(), $template->getBody());
        }

        $syntaxSuccess = $parser->checkSyntax($letter->getHtmlMessage(), $output, true);
        if ($syntaxSuccess)
            $syntaxSuccess = $parser->checkSyntax($letter->getTextMessage(), $output, false);

        $subject = $letter->getSubject();
        if ($syntaxSuccess) {
            $syntaxSuccess = $parser->checkSyntax($subject, $output, true);
            $subject = $output;
        }

        if (!$analysisSuccess)
            $error = 'analysis';
        else if (!$syntaxSuccess)
            $error = 'syntax';
        else
            $error = false;

        return new JsonModel([
            'error'         => $error,
            'subject'       => $subject,
            'html'          => $this->prepareHtml($letter, $params),
            'attachments'   => $this->prepareAttachments($letter, $params),
            'text'          => $this->prepareText($letter),
            'log'           => $this->prepareLog($letter),
            'source'        => $this->prepareSource($letter),
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
        $uid = $this->params()->fromQuery('uid');
        $template = $this->params()->fromQuery('template');

        if ((!$box || !$uid) && !$template)
            throw new \Exception('No "box/uid" or "template" parameter');

        $cid = $this->params()->fromQuery('cid');
        $filename = $this->params()->fromQuery('filename');

        if (!$cid && !$filename)
            throw new \Exception('No "cid" or "filename" parameter given');

        $sl = $this->getServiceLocator();
        $imap = $sl->get('ImapClient');
        $em = $sl->get('Doctrine\ORM\EntityManager');

        if ($box && $uid) {
            $letter = $imap->getLetter($box, $uid);
            if (!$letter)
                throw new NotFoundException('Letter not found');

            $success = $imap->loadLetter($letter, $box, $uid);
        } else if ($template) {
            $template = $em->getRepository('Application\Entity\Template')
                           ->find($template);
            if (!$template)
                throw new NotFoundException('Template not found');

            $letter = new LetterModel(null);
            $success = $letter->load($template->getHeaders(), $template->getBody());
        }

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
     * This action is called when requested action is not found
     */
    public function notFoundAction()
    {
        throw new NotFoundException('Action is not found');
    }

    /**
     * Prepare HTML part of the letter
     *
     * @param Letter $letter
     * @param string $params
     * @return string
     */
    protected function prepareHtml($letter, $params)
    {
        $sl = $this->getServiceLocator();
        $parser = $sl->get('Parser');
        $basePath = $sl->get('viewhelpermanager')->get('basePath');

        $config = HTMLPurifier_Config::createDefault();
        $config->set('Cache.DefinitionImpl', null);
        $config->set('URI.DisableExternalResources', true);

        $message = $letter->getHtmlMessage();
        $message = preg_replace_callback(
            '/src="cid:([^"]+)"/U',
            function ($matches) use ($basePath, $params, $letter) {
                if (isset($params['box']))
                    $query = 'box=' . urlencode($params['box']) . '&uid=' . urlencode($letter->getUid());
                else if (isset($params['template']))
                    $query = 'template=' . urlencode($params['template']);

                return 'src="'
                    . $basePath('/admin/letter/attachment')
                    . '?' . $query
                    . '&cid=' . urlencode($matches[1])
                    . '"';
            },
            $message
        );

        $parser->checkSyntax($message, $output, true);
        $message = $output;

        $purifier = new HTMLPurifier($config);
        $result = $purifier->purify($message);
        return $result;
    }

    /**
     * Prepare attachments table of the letter
     *
     * @param Letter $letter
     * @param array $params
     * @return string
     */
    protected function prepareAttachments($letter, $params)
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
            'letter'        => $letter,
            'attachments'   => $attachments,
            'params'        => $params,
        ));
        $model->setTemplate('admin/letter/letter-attachments');

        $sl = $this->getServiceLocator();
        $renderer = $sl->get('viewmanager')->getRenderer();
        return $renderer->render($model);
    }

    /**
     * Prepare text part of the letter
     *
     * @param Letter $letter
     * @return string
     */
    protected function prepareText($letter)
    {
        $sl = $this->getServiceLocator();
        $parser = $sl->get('Parser');

        $message = $letter->getTextMessage();
        $parser->checkSyntax($message, $output, false);

        $result = '<p>' . str_replace("\n", "<br>", $output) . '</p>';

        return $result;
    }

    /**
     * Prepare analysis log of the letter
     *
     * @param Letter $letter
     * @return string
     */
    protected function prepareLog($letter)
    {
        $sl = $this->getServiceLocator();
        $escapeHtml = $sl->get('viewhelpermanager')->get('escapeHtml');

        $result = '<div class="pre">' . $escapeHtml($letter->getLog()) . '</div>';

        return $result;
    }

    /**
     * Prepare raw view of the letter
     *
     * @param Letter $letter
     * @return string
     */
    protected function prepareSource($letter)
    {
        $sl = $this->getServiceLocator();
        $escapeHtml = $sl->get('viewhelpermanager')->get('escapeHtml');

        $result = '<div class="pre">' . $escapeHtml($letter->getSource()) . '</div>';

        return $result;
    }
}
