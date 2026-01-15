<?php
namespace MB\Support\HtmlCleaner\Selector;

use DOMNode;
use MB\Support\HtmlCleaner\Contracts\SelectorInterface;

class NotSelector implements SelectorInterface
{
    public function __construct(
        private SelectorInterface $selector
    ) {}

    public function matches(DOMNode $node): bool
    {
        return !$this->selector->matches($node);
    }
}