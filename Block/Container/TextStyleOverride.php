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
        if (!empty($mark)) {
            $markArray = is_string($mark) ? json_decode($mark, true) : $mark;
            $attrValue = $markArray->attrs;

            $styles = [];
            if (isset($attrValue) && is_array($attrValue)) {
                foreach ($attrValue as $key => $value) {
                    $styles[] = $key . ':' . $value;
                }
                if (!empty($styles)) {
                    $HTMLAttributes['style'] = implode(';', $styles);
                }            
            } 
            else if (isset($attrValue)) {                
                $vars = get_object_vars($attrValue);
                $firstKey = array_key_first($vars);
                $firstValue = $vars[$firstKey];
                $HTMLAttributes['style'] = $firstKey . ':' . $firstValue;
            }
        }
        return ['span', HTML::mergeAttributes($this->options['HTMLAttributes'], $HTMLAttributes), 0];
    }
}