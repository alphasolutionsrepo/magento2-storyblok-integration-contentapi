<?php
namespace MediaLounge\Storyblok\App\Cache;

use Magento\Framework\App\DeploymentConfig;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\App\DeploymentConfig\Writer;
use Psr\Log\LoggerInterface;

class State extends \Magento\Framework\App\Cache\State
{
    private LoggerInterface $logger;

    /**
     * @var Json
     */
    private $json;

    /**
     * @var RequestInterface
     */
    private $request;

    public function __construct(
        Json $json,
        RequestInterface $request,
        DeploymentConfig $config,
        Writer $writer,
        LoggerInterface $logger,
        $banAll = false
    ) {
        parent::__construct($config, $writer, $banAll);
        $this->logger = $logger;
        $this->json = $json;
        $this->request = $request;
    }

    public function isEnabled($cacheType): bool
    {
        $this->logger->debug('MediaLounge\Storyblok\App\Cache\State::isEnabled($cacheType): ' . $cacheType);
        $postContent = [];

        if ($this->isJsonPostRequest($this->request)) {
            $postContent = $this->json->unserialize($this->request->getContent());
        }

        if (
            in_array($cacheType, ['block_html', 'full_page']) &&
            ($this->request->getParam('_storyblok') || !empty($postContent['_storyblok']))
        ) {
            return false;
        }

        return parent::isEnabled($cacheType);
    }

    private function isJsonPostRequest(RequestInterface $request): bool
    {
        return $request->getContent() && $request->getHeader('Content-Type') === 'application/json';
    }
}
