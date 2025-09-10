<?php

namespace MediaLounge\Storyblok\Block\Container;

use Tiptap\Core\Node;
use Tiptap\Utils\HTML;
use Psr\Log\LoggerInterface;

class HardBreak extends Node
{
    private LoggerInterface $logger;
    public static $name = 'hardBreak';

    public function __construct(
        LoggerInterface $logger,
    ) {
        parent::__construct();
        $this->logger = $logger;
        $this->logger->debug('MediaLounge\Storyblok\Blok\Container::HardBreak::__construct');
    } 

    public function addOptions()
    {
        $this->logger->debug('MediaLounge\Storyblok\Blok\Container::HardBreak::addOptions');
        return [
            'HTMLAttributes' => [],
        ];
    }

    public function parseHTML()
    {
        $this->logger->debug('MediaLounge\Storyblok\Blok\Container::HardBreak::parseHTML');
        return [
            [
                'tag' => 'br',
            ],
        ];
    }

    public function renderHTML($node, $HTMLAttributes = [])
    {
        $this->logger->debug('MediaLounge\Storyblok\Blok\Container::HardBreak::renderHTML::info=' . json_encode($node));
        return ['br', HTML::mergeAttributes($this->options['HTMLAttributes'], $HTMLAttributes)];
    }
}