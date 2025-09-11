<?php

namespace MediaLounge\Storyblok\Block\Container;

use Tiptap\Core\Node;
use Tiptap\Utils\HTML;
use Psr\Log\LoggerInterface;

class HardBreakOverride extends Node
{
    private LoggerInterface $logger;
    public static $name = 'hard_break';

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
                'tag' => 'br',
            ],
        ];
    }

    public function renderHTML($node, $HTMLAttributes = [])
    {
        return ['br', HTML::mergeAttributes($this->options['HTMLAttributes'], $HTMLAttributes)];
    }
}