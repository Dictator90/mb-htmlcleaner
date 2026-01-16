<?php
namespace MB\Support\HtmlCleaner\Selector;

use MB\Support\HtmlCleaner\Contracts\SelectorInterface;

final class HasSelector implements SelectorInterface
{
    public function __construct(
        protected SelectorInterface $parent,
        protected SelectorInterface $inner
    ) {}

    public function matches(\DOMNode $node): bool
    {
        if (!$this->parent->matches($node)) {
            return false;
        }

        return $this->hasMatch($node);
    }

    private function hasMatch(\DOMNode $node): bool
    {
        foreach ($node->childNodes as $child) {
            if ($this->inner->matches($child)) {
                return true;
            }

            if ($this->hasMatch($child)) {
                return true;
            }
        }

        return false;
    }
}
