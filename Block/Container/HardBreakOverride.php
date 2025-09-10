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
        $this->logger->debug('MediaLounge\Storyblok\Blok\Container::HardBreakOverride::__construct');
    } 

    public function addOptions()
    {
        return [
            'HTMLAttributes' => [],
        ];
    }

    public function parseHTML()
    {
        $this->logger->debug('MediaLounge\Storyblok\Blok\Container::HardBreakOverride::parseHTML');
        return [
            [
                'tag' => 'br',
            ],
        ];
    }

    public function renderHTML($node, $HTMLAttributes = [])
    {
        $this->logger->debug('MediaLounge\Storyblok\Blok\Container::HardBreakOverride::renderHTML::info=' . json_encode($node));
        return ['br', HTML::mergeAttributes($this->options['HTMLAttributes'], $HTMLAttributes)];
    }
}