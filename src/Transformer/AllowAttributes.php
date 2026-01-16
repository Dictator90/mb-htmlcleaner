<?php
namespace MB\Support\HtmlCleaner\Transformer;

use DOMNode;
use MB\Support\HtmlCleaner\Contracts\TransformerInterface;

class AllowAttributes implements TransformerInterface
{
    public function __construct(
        protected array $attributes = []
    ) {}

    public function apply(DOMNode $node): bool
    {
        if ($node->nodeType !== XML_ELEMENT_NODE) {
            return true;
        }

        foreach ($node->attributes as $attrName => $attrNode) {
            if (!in_array($attrName, $this->attributes)) {
                $node->removeAttribute($attrName);
            }
        }

        return true;
    }
}
