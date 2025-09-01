<?php

namespace MediaLounge\Storyblok\Block\Container;

use Tiptap\Core\Mark;
use Tiptap\Utils\HTML;
use Psr\Log\LoggerInterface;

class TextStyleOverride extends Mark
{
    private LoggerInterface $logger;
    public static $name = 'textStyle';

    public function __construct(
        LoggerInterface $logger,
    ) {
        parent::__construct();
        $this->logger = $logger;
    } 

    public function addOptions()
    {
        return [
            'HTMLAttributes' => [],
        ];
    }

    public function parseHTML()
    {
        $this->logger->debug('TextStyleOverride::parseHTML');
        return [
            [
                'tag' => 'span',
                'getAttrs' => function ($DOMNode) {
                    return $DOMNode->hasAttribute('style') ? ['style' => $DOMNode->getAttribute('style')] : [];
                },
            ],
        ];
    }

    public function renderHTML($mark, $HTMLAttributes = [])
    {
        $this->logger->debug('TextStyleOverride::renderHTML: mark=' . json_encode($mark));
        $this->logger->debug('TextStyleOverride::renderHTML: HTMLAttributes=' . json_encode($HTMLAttributes));
        $this->logger->debug('TextStyleOverride::renderHTML: empty($mark)=' . empty($mark));

        if (!empty($mark)) {
            $this->logger->debug('TextStyleOverride::renderHTML: ARRAY');      
            $markArray = is_string($mark) ? json_decode($mark, true) : $mark;
            $this->logger->debug('TextStyleOverride::renderHTML: markArray=' . json_encode($markArray));      
            $this->logger->debug('TextStyleOverride::renderHTML: is_string - markArray=' . is_string($markArray));      
            $this->logger->debug('TextStyleOverride::renderHTML: is_array - markArray=' . is_array($markArray));      

            $this->logger->debug('TextStyleOverride::renderHTML: $markArray->attrs=' . json_encode($markArray->attrs));
            
            $styles = [];
            if (isset($markArray['attrs']) && is_array($markArray['attrs'])) {
                foreach ($markArray['attrs'] as $key => $value) {
                    $styles[] = $key . ':' . $value;
                }
                if (!empty($styles)) {
                    $HTMLAttributes['style'] = implode(';', $styles);
                }
            }
        } else if (isset($mark['attrs'])) {
            $this->logger->debug('TextStyleOverride::renderHTML: NOT ARRAY');
            $firstKey = array_key_first($mark['attrs']);
            $firstValue = $mark['attrs'][$firstKey];
            $HTMLAttributes['style'] = $firstKey . ':' . $firstValue;
        }
        //$HTMLAttributes = array_merge($HTMLAttributes, ['style' => 'color:#459450']);
        $this->logger->debug('TextStyleOverride::renderHTML: HTMLAttributes=' . json_encode($HTMLAttributes));
        return ['span', HTML::mergeAttributes($this->options['HTMLAttributes'], $HTMLAttributes), 0];
    }
}