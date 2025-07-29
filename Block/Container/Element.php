<?php
namespace MediaLounge\Storyblok\Block\Container;

use Magento\Framework\View\Element\Template\Context;
use Tiptap\Editor;
use Storyblok\Tiptap\Extension\Storyblok;
use Psr\Log\LoggerInterface;


class Element extends \Magento\Framework\View\Element\Template
{
     private LoggerInterface $logger;

    /**
     * @var Editor
     */
    private $editor;

    private $loglevel;

    public function __construct(
        Context $context,
        LoggerInterface $logger,
        array $data = []
    ) {
        parent::__construct($context, $data);

        $this->editor = new Editor(['extensions' => [new Storyblok(),],]);
        $this->logger = $logger;

        $this->loglevel = 'debug';
    }

    protected function _toHtml(): string
    {
        $editable = $this->getData('_editable') ?? '';

        return $editable . parent::_toHtml();
    }

    public function renderWysiwyg(array $arrContent): string
    {
        if ($this->loglevel=== 'debug') $this->logger->debug('MediaLounge\Storyblok\Blok\Container\Element::renderWysiwyg()::Start');        
        if ($this->loglevel=== 'debug') $this->logger->debug('MediaLounge\Storyblok\Blok\Container\Element::renderWysiwyg()::arrContent=' .  print_r($arrContent, true));        
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
        $imageService = '//img2.storyblok.com/';
        $resource = preg_replace('/(https?:)?\/\/a.storyblok.com/', '', $image);

        $result = $imageService . $param . $resource;

        if ($this->loglevel === 'debug') $this->logger->debug('MediaLounge\Storyblok\Blok\Container\Element::transformImage()::$result=' . $result);

        return $result;
    }

    public function __call($method, $args)
    {
        // check for minimum length of 7 ('get' and 'html')
        if (!strlen($method) > 7) {
            return parent::__call($method, $args);
        }
        $start = substr($method, 0, 3);
        $end = substr($method, -4);
        if ($start === 'get' && $end === 'Html') {
            $key = strtolower(substr($method, 3, -4));
            return $this->getStoryBlockChilds($key);
        }
        return parent::__call($method, $args);
    }

    protected function getStoryBlockChilds(string $key): ?string
    {
        $data = $this->getData($key);
        if (!$data) {
            return null;
        }

        $name = $this->getNameInLayout();
        $namePrefix = substr($name, 0, strrpos($name, '_') + 1);

        $html = '';
        foreach ($data as $row) {
            $html .= $this->getChildHtml($namePrefix . $row['_uid']);
        }
        return $html;
    }
}
