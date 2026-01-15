<?php
namespace MB\Support\HtmlCleaner\Transformer;

use DOMElement;
use DOMNode;
use MB\Support\HtmlCleaner\Contracts\TransformerInterface;

final class Wrap implements TransformerInterface
{
    public function __construct(private string $tag) {}

    public static function tag(string $tag): self
    {
        return new self($tag);
    }

    public function apply(DOMNode $node): bool
    {
        $doc = $node->ownerDocument;
        $parent = $node->parentNode;
        if (!$doc || !$parent) {
            return false;
        }

        $wrapper = $doc->createElement($this->tag);
        $parent->replaceChild($wrapper, $node);
        $wrapper->appendChild($node);

        return true;
    }
}
