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
use Application\Document\Syslog as SyslogDocument;

/**
 * Logger service
 * 
 * @category    Application
 * @package     Service
 */
class Logger implements ServiceLocatorAwareInterface
{
    /**
     * Service Locator
     *
     * @var ServiceLocatorInterface
     */
    protected $serviceLocator = null;

    /**
     * Set service locator
     *
     * @param ServiceLocatorInterface $serviceLocator
     * @return Mail
     */
    public function setServiceLocator(ServiceLocatorInterface $serviceLocator)
    {
        $this->serviceLocator = $serviceLocator;

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
     * Adds message to the log
     *
     * @param string $level
     * @param string $message
     * @param array $params
     */
    public function log($level, $message, $params)
    {
        $log = new SyslogDocument();
        $log->setWhenHappened(new \DateTime());
        $log->setLevel($level);
        $log->setMessage($message);

        foreach ($params as $key => $value) {
            $setter = 'set' . \Application\Tool\Text::toCamelCase($key);
            $log->$setter($value);
        }

        $dm = $this->getServiceLocator()->get('doctrine.documentmanager.odm_default');
        $dm->persist($log);
        $dm->flush();
    }

    /**
     * Substitute variables in message string
     *
     * @param SyslogDocument $log
     * @return string
     */
    public function prepareMessage(SyslogDocument $log)
    {
        $sl = $this->getServiceLocator();
        $translate = $sl->get('viewhelpermanager')->get('translate');

        $msg = $translate($log->getMessage());
        $msg = str_replace('%exception%', $log->getException(), $msg);
        $msg = str_replace('%source_name%', $log->getSourceName(), $msg);
        $msg = str_replace('%source_id%', $log->getSourceId(), $msg);

        return $msg;
    }

    /**
     * Clear the log
     *
     * @return Logger
     */
    public function clear()
    {
        $dm = $this->getServiceLocator()->get('doctrine.documentmanager.odm_default');
        $repo = $dm->getRepository('Application\Document\Syslog');

        $repo->removeAll();
    }
}
