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
use Application\Entity\Client as ClientEntity;

/**
 * MailParser service
 * 
 * @category    Application
 * @package     Service
 */
class MailParser implements ServiceLocatorAwareInterface
{
    /**
     * Service Locator
     *
     * @var ServiceLocatorInterface
     */
    protected $serviceLocator = null;

    /**
     * Variables
     *
     * @var mixed
     */
    protected $variables = null;

    /**
     * Current client
     *
     * @var ClientEntity
     */
    protected $client = null;

    /**
     * Set service locator
     *
     * @param ServiceLocatorInterface $serviceLocator
     * @return MailParser
     */
    public function setServiceLocator(ServiceLocatorInterface $serviceLocator)
    {
        $this->serviceLocator = $serviceLocator;

        if (!$this->variables) {
            $options = $serviceLocator->get('Config');
            if (!isset($options['corpnews']['parser']['variables']))
                throw new \Exception("No 'parser/variables' section in the config");

            $this->variables = $options['corpnews']['parser']['variables'];
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
     * Set current client
     *
     * @param ClientEntity $client
     * @return MailParser
     */
    public function setClient(ClientEntity $client)
    {
        $this->client = $client;
        return $this;
    }

    /**
     * Get current client
     *
     * @return ClientEntity
     */
    public function getClient()
    {
        return $this->client;
    }

    /**
     * Get parser variables
     *
     * @return array
     */
    public function getVariables()
    {
        return array_keys($this->variables);
    }

    /**
     * Get variable description
     *
     * @param string $variable
     * @return string
     */
    public function getVariableDescr($variable)
    {
        if (!isset($this->variables[$variable]) || !isset($this->variables[$variable]['descr']))
            return null;

        return $this->variables[$variable]['descr'];
    }

    /**
     * Check syntax (parsed $output is HTML)
     *
     * @param string $msg
     * @param string &$output
     * @param boolean $htmlInput
     * @return boolean
     */
    public function checkSyntax($msg, &$output, $htmlInput)
    {
        $sl = $this->getServiceLocator();
        $escapeHtml = $sl->get('viewhelpermanager')->get('escapeHtml');

        $scripts = [];
        $bracketCounter = 0;
        $buffer = "";
        for ($i = 0; $i < strlen($msg); $i++) {
            if ($msg[$i] == '{')
                $bracketCounter++;

            if ($bracketCounter > 0) {
                $buffer .= $msg[$i];
            } else if (strlen($buffer) > 0) {
                $scripts[] = $buffer;
                $buffer = "";
            }

            if ($msg[$i] == '}')
                $bracketCounter--;
        }
        if (strlen($buffer) > 0)
            $scripts[] = $buffer;

        $success = true;
        $output = "";
        if (count($scripts) > 0) {
            $prevPos = 0;
            foreach ($scripts as $script) {
                $error = false;

                $length = strlen($script);
                if ($script[0] != '{' || $script[1] != '{')
                    continue;
                if ($script[$length-1] != '}' || $script[$length-2] != '}')
                    $error = true;

                if (!$error) {
                    $code = substr($script, 2, $length-4) . ';';
                    if ($htmlInput)
                        $code = html_entity_decode($code);

                    if (!$this->testCode($code))
                        $error = true;
                }

                if ($error)
                    $success = false;

                $pos = strpos($msg, $script, $prevPos);
                if ($pos !== false) {
                    $originalChunk = substr($msg, $prevPos, $pos - $prevPos);
                    if ($htmlInput) {
                        $output .= $originalChunk;
                    } else {
                        $escapedChunk = $escapeHtml($originalChunk);
                        $output .= $escapedChunk;
                    }

                    $highlight = '<span style="background: '
                        . ($error ? '#a90000' : '#00a900')
                        . '; color: #ffffff;">'
                        . $escapeHtml($script)
                        . '</span>';
                    $output .= $highlight;
                    $prevPos = $pos + $length;
                }

                $originalChunk = substr($msg, $prevPos, strlen($msg) - $prevPos);
                if ($htmlInput) {
                    $output .= $originalChunk;
                } else {
                    $escapedChunk = $escapeHtml($originalChunk);
                    $output .= $escapedChunk;
                }
            }
        } else {
            $output = $htmlInput ? $msg : $escapeHtml($msg);
        }

        return $success;
    }

    /**
     * Parse and run the code
     *
     * @param string $msg
     * @param string &$output
     * @param boolean $htmlInput
     * @return boolean
     */
    public function parse($msg, &$output, $htmlInput)
    {
        $sl = $this->getServiceLocator();
        $escapeHtml = $sl->get('viewhelpermanager')->get('escapeHtml');
    }

    /**
     * Test PHP syntax is OK
     *
     * @param string $code
     * @return boolean
     */
    protected function testCode($code)
    {
        $oldReporting = error_reporting(0);
        $success = @eval('return true;' . $code . ';');
        error_reporting($oldReporting);
        return $success === true;
    }
}
