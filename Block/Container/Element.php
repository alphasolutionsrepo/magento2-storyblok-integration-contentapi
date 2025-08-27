<?php
namespace MediaLounge\Storyblok\Block\Container;

use Magento\Framework\View\Element\Template\Context;
use Tiptap\Editor;
use Storyblok\Tiptap\Extension\Storyblok;
use Psr\Log\LoggerInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\StoreManagerInterface;


class Element extends \Magento\Framework\View\Element\Template
{
     private LoggerInterface $logger;

    /**
     * @var Editor
     */
    private $editor;

    private $loglevel;

    private $imagehost;

    /** @var ScopeConfigInterface */
    protected ScopeConfigInterface $scopeConfig;

    /** @var StoreManagerInterface */
    protected StoreManagerInterface $storeManager;

    public function __construct(
        Context $context,
        LoggerInterface $logger,
        ScopeConfigInterface $scopeConfig,
        StoreManagerInterface $storeManager,
        array $data = []
    ) {
        parent::__construct($context, $data);

        $this->editor = new Editor(['extensions' => [new Storyblok(),],]);
        $this->logger = $logger;
        $this->loglevel = 'debug';

        $this->scopeConfig = $scopeConfig;
        $this->storeManager = $storeManager;

        $this->imagehost = $this->scopeConfig->getValue(
            'storyblok/general/image_host',
            ScopeInterface::SCOPE_STORE,
            $this->storeManager->getStore()->getId()
        );

        if ($this->loglevel === 'debug') {
            $this->logger->debug('MediaLounge\Storyblok\Blok\Container\Element::renderWysiwyg()::imagehost=' . $this->imagehost);
        }
        

    }

    protected function _toHtml(): string
    {
        $editable = $this->getData('_editable') ?? '';

        return $editable . parent::_toHtml();
    }

    public function renderWysiwyg(array $arrContent): string
    {
        if ($this->loglevel === 'debug') {
            $this->logger->debug('MediaLounge\Storyblok\Blok\Container\Element::renderWysiwyg()::$arrContent=' . print_r($arrContent, true));
        }
        if ($this->loglevel=== 'debug') $this->logger->debug('MediaLounge\Storyblok\Blok\Container\Element::renderWysiwyg()::Start');        
        $this->editor->setContent($arrContent);
        $html = $this->editor->getHTML();
        if ($this->loglevel=== 'debug') $this->logger->debug('MediaLounge\Storyblok\Blok\Container\Element::renderWysiwyg()::$html=' . $html);

        return $html;
    }

    public function transformImage(string $image, string $param = ''): string
    {
        if ($this->loglevel=== 'debug') { 
            $this->logger->debug('MediaLounge\Storyblok\Blok\Container\Element::transformImage()::Start');        
            $this->logger->debug('MediaLounge\Storyblok\Blok\Container\Element::transformImage()::$image=' . $image);        
            $this->logger->debug('MediaLounge\Storyblok\Blok\Container\Element::transformImage()::$param=' . $param);        
        }
        $imageService = '//a-us.storyblok.com/';
        $resource = preg_replace('/(https?:)?\/\/a-us.storyblok.com/', '', $image);

        $result = $imageService . $param . $resource;

        if ($this->loglevel === 'debug') $this->logger->debug('MediaLounge\Storyblok\Blok\Container\Element::transformImage()::$result=' . $result);

        return $result;
    }

    public function __call($method, $args)
    {
        if ($this->loglevel === 'debug') $this->logger->debug('MediaLounge\Storyblok\Blok\Container\Element::__call()::$method=' . $method);
        if ($this->loglevel === 'debug') $this->logger->debug('MediaLounge\Storyblok\Blok\Container\Element::__call()::$args=' . print_r($args, true));
        // check for minimum length of 7 ('get' and 'html')
        if (!strlen($method) > 7) {
            if ($this->loglevel === 'debug') $this->logger->debug('MediaLounge\Storyblok\Blok\Container\Element::__call():: calling parent::__call()');
            return parent::__call($method, $args);
        }
        $start = substr($method, 0, 3);
        $end = substr($method, -4);
        if ($start === 'get' && $end === 'Html') {
            $key = strtolower(substr($method, 3, -4));
            return $this->getStoryBlockChilds($key);
        }
            if ($this->loglevel === 'debug') $this->logger->debug('MediaLounge\Storyblok\Blok\Container\Element::__call()-2:: calling parent::__call()');
        return parent::__call($method, $args);
    }

    protected function getStoryBlockChilds(string $key): ?string
    {
        if ($this->loglevel === 'debug') $this->logger->debug('MediaLounge\Storyblok\Blok\Container\Element::getStoryBlockChilds()::$key=' . $key);
        $data = $this->getData($key);
        if ($this->loglevel === 'debug') $this->logger->debug('MediaLounge\Storyblok\Blok\Container\Element::getStoryBlockChilds()::$data=' . print_r($data, true));
        if ($this->loglevel === 'debug') $this->logger->debug('MediaLounge\Storyblok\Blok\Container\Element::getStoryBlockChilds()::$data=' . json_encode($data));
        if (!$data) {
            return null;
        }

        $name = $this->getNameInLayout();
        $namePrefix = substr($name, 0, strrpos($name, '_') + 1);

        if ($this->loglevel === 'debug') $this->logger->debug('MediaLounge\Storyblok\Blok\Container\Element::getStoryBlockChilds()::$name=' . $name);
        if ($this->loglevel === 'debug') $this->logger->debug('MediaLounge\Storyblok\Blok\Container\Element::getStoryBlockChilds()::$namePrefix=' . $namePrefix);

        $html = '';
        foreach ($data as $row) {
            if ($this->loglevel === 'debug') $this->logger->debug('MediaLounge\Storyblok\Blok\Container\Element::getStoryBlockChilds()::$row=' . json_encode($row));
            $html .= $this->getChildHtml($namePrefix . $row['_uid']);
        }
        if ($this->loglevel === 'debug') $this->logger->debug('MediaLounge\Storyblok\Blok\Container\Element::getStoryBlockChilds()::$html=' . $html);
        return $html;
    }
}
