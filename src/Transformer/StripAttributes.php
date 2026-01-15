<?php
namespace MB\Support\HtmlCleaner\Transformer;

use DOMNode;
use MB\Support\HtmlCleaner\Contracts\TransformerInterface;

final class StripAttributes implements TransformerInterface
{
    public function __construct(
        private array $attributes
    ) {}

    public function apply(DOMNode $node): bool
    {
        if ($node->nodeType !== XML_ELEMENT_NODE) {
            return true;
        }

        foreach ($this->attributes as $attr) {
            $node->removeAttribute($attr);
        }

        return true;
    }
}
