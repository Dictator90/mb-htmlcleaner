<?php
namespace MB\Support\HtmlCleaner\Selector;

use MB\Support\HtmlCleaner\Contracts\SelectorInterface;

class HasTextSelector implements SelectorInterface
{
    public function __construct(
        private SelectorInterface $base,
        private string $text
    ) {}

    public function matches(\DOMNode $node): bool
    {
        if (!$this->base->matches($node)) {
            return false;
        }

        return $this->hasText($node);
    }

    private function hasText(\DOMNode $node): bool
    {
        if ($node->nodeType === XML_TEXT_NODE) {
            return mb_stripos($node->nodeValue, $this->text) !== false;
        }

        foreach ($node->childNodes as $child) {
            if ($this->hasText($child)) {
                return true;
            }
        }

        return false;
    }
}
