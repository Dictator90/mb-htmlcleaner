<?php
namespace MB\Support\HtmlCleaner\Selector;

use DOMNode;
use DOMElement;
use MB\Support\HtmlCleaner\Contracts\SelectorInterface;

class AttributeSelector implements SelectorInterface
{
    public const OP_EQUALS = '=';
    public const OP_NOT_EQ = '!=';
    public const OP_CONTAINS = '*=';

    public function __construct(
        private string $attribute,
        private ?string $operator = null,
        private ?string $value = null,
    ) {}

    public function matches(DOMNode $node): bool
    {
        if (!$node instanceof DOMElement) {
            return false;
        }

        if (!$node->hasAttribute($this->attribute)) {
            return false;
        }

        if (!$this->operator) {
            return true;
        }

        $attrValue = $node->getAttribute($this->attribute);

        return match ($this->operator) {
            self::OP_EQUALS => $attrValue === $this->value,
            self::OP_NOT_EQ => $attrValue !== $this->value,
            self::OP_CONTAINS => str_contains($attrValue, $this->value),
            '^=' => str_starts_with($attrValue, $this->value),
            '$=' => str_ends_with($attrValue, $this->value),
            default => false
        };
    }
}