<?php
/**
 * CorpNews
 *
 * @link        https://github.com/basarevych/corpnews
 * @copyright   Copyright (c) 2015 basarevych@gmail.com
 * @license     http://choosealicense.com/licenses/mit/ MIT
 */

namespace DataForm\ParserFunction;

use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Application\Entity\Template as TemplateEntity;
use Application\Entity\Client as ClientEntity;
use Application\Entity\Secret as SecretEntity;
use DataForm\ParserFunction\ParserFunctionInterface;

/**
 * $data_form_link variable
 *
 * @category    DataForm
 * @package     ParserFunction
 */
class DataFormLink implements ServiceLocatorAwareInterface,
                              ParserFunctionInterface
{
    /**
     * Service Locator
     *
     * @var ServiceLocatorInterface
     */
    protected $serviceLocator = null;

    /**
     * Current template
     *
     * @var TemplateEntity
     */
    protected $template = null;

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
     * @return FirstName
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
     * Set current template
     *
     * @param TemplateEntity $template
     * @return Company
     */
    public function setTemplate(TemplateEntity $template)
    {
        $this->template = $template;
        return $this;
    }

    /**
     * Get current template
     *
     * @return TemplateEntity
     */
    public function getTemplate()
    {
        return $this->template;
    }

    /**
     * Set current client
     *
     * @param ClientEntity $client
     * @return FirstName
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
     * Execute the function
     *
     * @param string $formName
     * @param string $linkText
     */
    public function execute($formName = 'profile', $linkText = 'Profile')
    {
        $sl = $this->getServiceLocator();
        $em = $sl->get('Doctrine\ORM\EntityManager');
        $dfm = $sl->get('DataFormManager');
        $config = $sl->get('Config');

        if (!isset($config['corpnews']['server']['base_url']))
            throw new \Exception('Base URL is not set');

        $baseUrl = $config['corpnews']['server']['base_url'];

        $template = $this->getTemplate();
        if (!$template)
            throw new \Exception('No template set');

        $client = $this->getClient();
        if (!$client)
            throw new \Exception('No client set');

        $campaign = $template->getCampaign();
        $secret = $em->getRepository('Application\Entity\Secret')
                     ->findOneBy([
                        'campaign' => $campaign,
                        'client' => $client,
                        'data_form' => $formName,
                     ]);
        if (!$secret) {
            $secret = new SecretEntity();
            $secret->setCampaign($campaign);
            $secret->setClient($client);
            $secret->setDataForm($formName);

            $secret->setSecretKey(SecretEntity::generateSecretKey());

            $em->persist($secret);
            $em->flush();
        }

        $url = $baseUrl . '/' . $dfm->getUrl($formName)
            . '?key=' . $secret->getSecretKey();
        $url = preg_replace('/([^:])\/{2,}/', '$1/', $url);

        echo '<a href="' . $url . '">';
        echo $linkText;
        echo '</a>';
    }
}
