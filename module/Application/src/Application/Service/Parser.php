<?php
/**
 * CorpNews
 *
 * @link        https://github.com/basarevych/corpnews
 * @copyright   Copyright (c) 2015 basarevych@gmail.com
 * @license     http://choosealicense.com/licenses/mit/ MIT
 */

namespace Application\Service;

use ReflectionClass;
use Exception;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Application\Entity\Client as ClientEntity;

/**
 * Parser service
 * 
 * @category    Application
 * @package     Service
 */
class Parser implements ServiceLocatorAwareInterface
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
     * @return Parser
     */
    public function setServiceLocator(ServiceLocatorInterface $serviceLocator)
    {
        $this->serviceLocator = $serviceLocator;

        if (!$this->variables) {
            $options = $serviceLocator->get('Config');
            if (!isset($options['corpnews']['parser']['variables']))
                throw new \Exception("No 'parser/variables' section in the config");

            $this->variables = $options['corpnews']['parser']['variables'];
/*
            foreach ($this->variables as $var) {
                $class = $var['class'];
                $reflection = new ReflectionClass($class);

                if (!$reflection->implementsInterface('Application\Variable\VariableInterface'))
                    throw new Exception('All the variables must implement VariableInterface');

                if (!$reflection->implementsInterface('Zend\ServiceManager\ServiceLocatorAwareInterface'))
                    throw new Exception('All the variables must implement ServiceLocatorAwareInterface');
            }
*/
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
     * @return Parser
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
     * Get variable class
     *
     * @param string $variable
     * @return string
     */
    public function getVariableClass($variable)
    {
        if (!isset($this->variables[$variable]) || !isset($this->variables[$variable]['class']))
            return null;

        return $this->variables[$variable]['class'];
    }

    /**
     * Get variable value
     *
     * @param string $variable
     * @return string
     */
    public function getVariableValue($variable)
    {
        $class = $this->getVariableClass($variable);

        $object = new $class();
        $object->setServiceLocator($this->getServiceLocator());
        $object->setClient($this->getClient());

        return $object->getValue();
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
                if ($pos === false) {
                    $success = false;
                } else {
                    $originalChunk = substr($msg, $prevPos, $pos - $prevPos);
                    if ($htmlInput)
                        $output .= $originalChunk;
                    else
                        $output .= htmlentities($originalChunk, ENT_COMPAT | ENT_HTML401, 'UTF-8');

                    $highlight = '<span style="background: '
                        . ($error ? '#a90000' : '#00a900')
                        . '; color: #ffffff;">'
                        . htmlentities($script, ENT_COMPAT | ENT_HTML401, 'UTF-8')
                        . '</span>';
                    $output .= $highlight;
                    $prevPos = $pos + $length;
                }
            }

            $originalChunk = substr($msg, $prevPos, strlen($msg) - $prevPos);
            if ($htmlInput)
                $output .= $originalChunk;
            else
                $output .= htmlentities($originalChunk, ENT_COMPAT | ENT_HTML401, 'UTF-8');
        } else {
            $output = $htmlInput ? $msg : htmlentities($msg, ENT_COMPAT | ENT_HTML401, 'UTF-8');
        }

        return $success;
    }

    /**
     * Parse and substitute the code
     *
     * @param string $msg
     * @param string &$output
     * @param boolean $htmlInput
     * @return boolean
     */
    public function parse($msg, &$output, $htmlInput, $htmlOutput)
    {
        $callback = function ($code) {
            //foreach ($this->getVariables() as $__var)
            foreach ([ 'first_name', 'last_name' ] as $__var)
                $$__var = $this->getVariableValue($__var);

            eval($code);
        };

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
                if ($pos === false) {
                    $success = false;
                } else {
                    $originalChunk = substr($msg, $prevPos, $pos - $prevPos);
                    if ($htmlInput == $htmlOutput)
                        $output .= $originalChunk;
                    else if ($htmlInput && !$htmlOutput)
                        $output .= html_entity_decode($originalChunk);
                    else
                        $output .= htmlentities($originalChunk, ENT_COMPAT | ENT_HTML401, 'UTF-8');

                    ob_start();
                    $callback($code);
                    $result = ob_get_contents();
                    ob_end_clean();

                    if ($htmlOutput)
                        $output .= htmlentities($result, ENT_COMPAT | ENT_HTML401, 'UTF-8');
                    else
                        $output .= $result;

                    $prevPos = $pos + $length;
                }
            }

            $originalChunk = substr($msg, $prevPos, strlen($msg) - $prevPos);
            if ($htmlInput == $htmlOutput)
                $output .= $originalChunk;
            else if ($htmlInput && !$htmlOutput)
                $output .= html_entity_decode($originalChunk);
            else
                $output .= htmlentities($originalChunk, ENT_COMPAT | ENT_HTML401, 'UTF-8');
        } else {
            if ($htmlInput == $htmlOutput)
                $output .= $msg;
            else if ($htmlInput && !$htmlOutput)
                $output .= html_entity_decode($msg);
            else
                $output .= htmlentities($msg, ENT_COMPAT | ENT_HTML401, 'UTF-8');
        }

        return $success;
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
