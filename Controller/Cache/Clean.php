<?php

namespace MediaLounge\Storyblok\Controller\Cache;

use Magento\Store\Model\ScopeInterface;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\CacheInterface;
use Magento\Framework\App\Cache\TypeListInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\PageCache\Model\Cache\Type as CacheType;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Psr\Log\LoggerInterface;
use Magento\Framework\App\Cache\Frontend\Pool;

class Clean extends Action implements HttpPostActionInterface
{
    private LoggerInterface $logger;

    /**
     * @var JsonFactory
     */
    private $resultJsonFactory;

    /**
     * @var CacheInterface
     */
    private $cacheInterface;

    /**
     * @var CacheType
     */
    private $cacheType;

    /**
     * @var Json
     */
    private $json;

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var TypeListInterface
     */
    private $cacheTypeList;

    /**
     * @var Pool
     */
    protected $cacheFrontendPool;

     /** @var ScopeConfigInterface */

     /**
     * @var loglevel
     */
    private $loglevel;

    public function __construct(
        Context $context,
        JsonFactory $resultJsonFactory,
        CacheInterface $cacheInterface,
        CacheType $cacheType,
        Json $json,
        ScopeConfigInterface $scopeConfig,
        StoreManagerInterface $storeManager,
        TypeListInterface $cacheTypeList,
        Pool $cacheFrontendPool,
        LoggerInterface $logger
    ) {
        parent::__construct($context);
        $this->logger = $logger;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->cacheInterface = $cacheInterface;
        $this->cacheType = $cacheType;
        $this->json = $json;
        $this->scopeConfig = $scopeConfig;
        $this->storeManager = $storeManager;
        $this->cacheTypeList = $cacheTypeList;
        $this->cacheFrontendPool = $cacheFrontendPool;

        $loglevel = $this->scopeConfig->getValue(
            'storyblok/general/log_level',
            ScopeInterface::SCOPE_STORE,
            $this->storeManager->getStore()->getId()
        );

        $this->loglevel = $loglevel;
    }

    public function execute(): ResultInterface
    {
        if ($this->loglevel=== 'debug') $this->logger->debug('MediaLounge\Storyblok\Controller\Cache::Clean::execute()::Start');        
        $success = false;
        if ($this->getRequest()->getParam('clearall') === 'true') {
            $this->cleanPageCache();
            $success = true;
            if ($this->loglevel === 'debug') {
                $this->logger->debug('MediaLounge\Storyblok\Controller\Cache::Clean::execute()::All cache cleared via clearall param');
            }
            $result = $this->resultJsonFactory->create();
            $result->setData(['success' => $success]);
            return $result;
        }
        $postContent = $this->json->unserialize($this->getRequest()->getContent());
        if ($this->loglevel=== 'debug') $this->logger->debug('MediaLounge\Storyblok\Controller\Cache::Clean::execute()::$postContent: ' . json_encode($postContent));

        if ($this->isSignatureValid($this->getRequest())) {
            if (isset($postContent['story_id'])) {
                if ($this->loglevel=== 'debug') $this->logger->debug('MediaLounge\Storyblok\Controller\Cache::Clean::execute()::$postContent: ' . json_encode($postContent));
                preg_match('#\((.*?)\)#', $postContent['text'], $slug);
                if ($this->loglevel === 'debug') $this->logger->debug('MediaLounge\Storyblok\Controller\Cache::Clean::execute()::$slug: ' . print_r($slug, true));
                $tags = [];
                if (isset($slug[1])) {
                    $tags[] = "storyblok_slug_{$slug[1]}";
                }
                $tags[] = "storyblok_{$postContent['story_id']}";

                if ($this->loglevel=== 'debug') $this->logger->debug('MediaLounge\Storyblok\Controller\Cache::Clean::execute()::$slug: ' . print_r($tags, true));
                $this->cacheInterface->clean($tags);
                $this->cacheType->clean(\Zend_Cache::CLEANING_MODE_MATCHING_ANY_TAG, $tags);

                $success = true;
                if ($this->loglevel=== 'debug') $this->logger->debug('MediaLounge\Storyblok\Controller\Cache::Clean::execute()::$success: ' . $success);
            } elseif (
                isset($postContent['action']) &&
                $postContent['action'] === 'release_merged'
            ) {
                $this->cleanPageCache();
                $success = true;
            }
        }

        $result = $this->resultJsonFactory->create();
        $result->setData(['success' => $success]);

        return $result;
    }

    /**
     * Clean page cache
     */ 
    private function cleanPageCache()
    {
        $types = ['layout', 'full_page', 'block_html'];

        foreach ($this->cacheTypeList->getTypes() as $typeCode => $type) {
            if ($this->loglevel === 'debug') {
                $this->logger->debug("CacheTypeList element: {$typeCode} => " . get_class($type));
                $this->cacheTypeList->cleanType("{$typeCode}");
            }
        }

        foreach ($types as $type) {
            $this->cacheTypeList->cleanType($type);
        }

        foreach ($this->cacheFrontendPool as $cacheFrontend) {
            $cacheFrontend->getBackend()->clean();
        }
    }

    /**
     * Verify that the request is actually coming from Storyblok
     */
    private function isSignatureValid(RequestInterface $request): bool
    {        
        if ($this->loglevel=== 'debug') $this->logger->debug('MediaLounge\Storyblok\Controller\Cache::Clean::isSignatureValid()::Start');
        $webhookSecret = $this->scopeConfig->getValue(
            'storyblok/general/webhook_secret',
            ScopeInterface::SCOPE_STORE,
            $this->storeManager->getStore()->getId()
        );
        $signature = hash_hmac('sha1', $request->getContent(), $webhookSecret);
        if ($this->loglevel=== 'debug') $this->logger->debug('MediaLounge\Storyblok\Controller\Cache::Clean::isSignatureValid()::Start:webhookSecret=' . $webhookSecret );
        if ($this->loglevel=== 'debug') $this->logger->debug('MediaLounge\Storyblok\Controller\Cache::Clean::isSignatureValid()::Start:signature    =' . $signature );

        $webhookSignature = $request
            ->getHeaders()
            ->get('Webhook-Signature')
            ->getFieldValue();

        if ($this->loglevel=== 'debug') $this->logger->debug('MediaLounge\Storyblok\Controller\Cache::Clean::isSignatureValid()::Start:webhookSignature    =' . $webhookSignature );

        return $signature === $webhookSignature;
    }
}
