<?php
namespace MB\Support\HtmlCleaner\Selector;

use DOMNode;
use DOMElement;
use MB\Support\HtmlCleaner\Contracts\SelectorInterface;

class StyleSelector implements SelectorInterface
{
    public function __construct(
        private string $property,
        private ?string $value = null
    ) {}

    public function matches(DOMNode $node): bool
    {
        if (!$node instanceof DOMElement || !$node->hasAttribute('style')) {
            return false;
        }

        $styles = [];

        foreach (explode(';', $node->getAttribute('style')) as $chunk) {
            if (str_contains($chunk, ':')) {
                [$k, $v] = explode(':', $chunk, 2);
                $styles[trim($k)] = trim($v);
            }
        }

        if (!array_key_exists($this->property, $styles)) {
            return false;
        }

        return $this->value === null || $styles[$this->property] === $this->value;
    }
}