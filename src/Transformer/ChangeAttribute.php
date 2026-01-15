<?php
namespace MB\Support\HtmlCleaner\Transformer;

use DOMNode;
use MB\Support\HtmlCleaner\Contracts\TransformerInterface;

final class ChangeAttribute implements TransformerInterface
{
    public static function attr(string $attribute, string $value): self
    {
        return new self($attribute, $value);
    }

    public function __construct(
        private string $attribute,
        private string $value,
    ) {}

    public function apply(DOMNode $node): bool
    {
        if ($node->nodeType !== XML_ELEMENT_NODE) {
            return true;
        }

        if ($node->hasAttribute($this->attribute)) {
            $node->setAttribute($this->attribute, $this->value);
        }

        return true;
    }
}
