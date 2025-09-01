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
        parent::__construct($options);
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
        return ['span', HTML::mergeAttributes($this->options['HTMLAttributes'], $HTMLAttributes), 0];
    }
}