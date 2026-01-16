<?php
namespace MB\Support\HtmlCleaner\Selector;

use MB\Support\HtmlCleaner\Contracts\SelectorInterface;

class ChildSelector implements SelectorInterface
{
    public function __construct(
        protected readonly SelectorInterface $parent,
        protected readonly SelectorInterface $target
    ) {}

    public function matches(\DOMNode $node): bool
    {
        if (!$this->target->matches($node)) {
            return false;
        }

        $parentNode = $node->parentNode;

        return $parentNode instanceof \DOMElement
            && $this->parent->matches($parentNode);
    }
}