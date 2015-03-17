<?php
/**
 * CorpNews
 *
 * @link        https://github.com/basarevych/corpnews
 * @copyright   Copyright (c) 2015 basarevych@gmail.com
 * @license     http://choosealicense.com/licenses/mit/ MIT
 */

namespace Application\Service;

use Exception;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\Mail\Transport;
use Zend\Mime\Mime;
use Zend\Mail\Message;
use Zend\Mail\Headers;
use Zend\Mime\Message as MimeMessage;
use Zend\Mime\Part as MimePart;
use Application\Model\Letter as LetterModel;
use Application\Entity\Template as TemplateEntity;
use Application\Entity\Client as ClientEntity;
use Application\Entity\Letter as LetterEntity;

/**
 * Mail service
 * 
 * @category    Application
 * @package     Service
 */
class Mail implements ServiceLocatorAwareInterface
{
    /**
     * Service Locator
     *
     * @var ServiceLocatorInterface
     */
    protected $serviceLocator = null;

    /**
     * Mail transport
     *
     * @var mixed
     */
    protected $transport = null;

    /**
     * Set service locator
     *
     * @param ServiceLocatorInterface $serviceLocator
     * @return Mail
     */
    public function setServiceLocator(ServiceLocatorInterface $serviceLocator)
    {
        $this->serviceLocator = $serviceLocator;

        $options = $serviceLocator->get('Config');
        if (isset($options['mail']['transport']))
            $transport = $options['mail']['transport'];
        else
            $transport = 'sendmail';

        if ($transport == 'sendmail') {
            $this->transport = new Transport\Sendmail();
        } else if ($transport == 'smtp') {
            if (!isset($options['mail']['host']))
                throw new Exception("Set SMTP 'host' in 'mail' config!");
            if (!isset($options['mail']['port']))
                throw new Exception("Set SMTP 'port' in 'mail' config!");
            $cfg = new Transport\SmtpOptions();
            $cfg->setHost($options['mail']['host']);
            $cfg->setPort($options['mail']['port']);
            $this->transport = new Transport\Smtp($cfg);
        } else {
            throw new Exception("Unknown mail transport: $transport");
        }

        return $this;
    }

    /**
     * Get service locator
     *
     * @return ServiceLocatorInterface
     */
    public function getServiceLocator()
    {
        if (!$this->serviceLocator)
            throw new \Exception('No Service Locator configured');
        return $this->serviceLocator;
    }

    /**
     * Get mail transport
     *
     * @return mixed
     * @throws Exception    When badly configured
     * @return Transport\TransportInterface
     */
    public function getTransport()
    {
        if ($this->transport)
            return $this->transport;

        return $transport;
    }

    /**
     * Create UTF-8 HTML message
     *
     * @param string htmlBody
     * @return Message
     */
    public function createHtmlMessage($htmlBody)
    {
        $html = new MimePart($htmlBody);
        $html->type = "text/html; charset=UTF-8";
        $html->encoding = Mime::ENCODING_QUOTEDPRINTABLE;

        $body = new MimeMessage();
        $body->setParts(array($html));

        $msg = new Message();
        $msg->setBody($body);
        $msg->setEncoding('UTF-8');

        return $msg;
    }

    /**
     * Create parsed message as LetterEntity (not persisted)
     *
     * @param TemplateEntity $template
     * @param ClientEntity $client
     * @return LetterEntity|false
     */
    public function createFromTemplate(TemplateEntity $template, ClientEntity $client)
    {
        $sl = $this->getServiceLocator();
        $config = $sl->get('Config');

        $parser = $sl->get('Parser');
        $parser->setClient($client);

        if (!$parser->parse($template->getSubject(), $subject, true, true))
            return false;

        $model = new LetterModel(null);
        $model->setMid('<' . $template->getMessageId() . '>');
        $model->setSubject($subject);
        $model->setFrom(@$config['corpnews']['server']['address']);
        $model->setTo($client->getEmail());

        if (!$model->load($template->getHeaders(), $template->getBody(), $parser))
            return false;

        $letter = new LetterEntity();
        $letter->setSecretKey(LetterEntity::generateSecretKey());
        $letter->setFromAddress($model->getFrom());
        $letter->setToAddress($model->getTo());
        $letter->setSubject($model->getSubject());
        $letter->setHeaders($model->getParsedHeaders());
        $letter->setBody($model->getParsedBody());
        $letter->setTemplate($template);
        $letter->setClient($client);

        return $letter;
    }

    /**
     * Send the letter
     *
     * @param LetterEntity $letter
     * @return boolean
     */
    public function sendLetter(LetterEntity $letter)
    {
        $sl = $this->getServiceLocator();
        $em = $sl->get('Doctrine\ORM\EntityManager');

        try {
            $msg = Message::fromString($letter->getHeaders() . "\n\n" . $letter->getBody());
            $this->getTransport()->send($msg);
            $letter->setError(null);
        } catch (\Exception $e) {
            $letter->setError($e->getMessage());
        }

        $letter->setWhenSent(new \DateTime());
        $em->persist($letter);
        $em->flush();

        return ($letter->getError() === null);
    }
}
