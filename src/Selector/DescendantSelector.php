<?php
namespace MB\Support\HtmlCleaner\Selector;

use MB\Support\HtmlCleaner\Contracts\SelectorInterface;

final class DescendantSelector implements SelectorInterface
{
    public function __construct(
        private SelectorInterface $ancestor,
        private SelectorInterface $target
    ) {}

    public function matches(\DOMNode $node): bool
    {
        if (!$this->target->matches($node)) {
            return false;
        }

        $parent = $node->parentNode;
        while ($parent) {
            if ($this->ancestor->matches($parent)) {
                return true;
            }
            $parent = $parent->parentNode;
        }

        return false;
    }
}
