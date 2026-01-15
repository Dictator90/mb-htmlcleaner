<?php
namespace MB\Support\HtmlCleaner\Transformer;

use DOMElement;
use DOMNode;
use DOMText;
use MB\Support\HtmlCleaner\Contracts\TransformerInterface;

final class OnlyText implements TransformerInterface
{
    public function apply(DOMNode $node): bool
    {
        $doc = $node->ownerDocument;
        $parent = $node->parentNode;
        if (!$doc || !$parent) {
            return false;
        }

        $text = $node->textContent ?? '';
        $parent->replaceChild($doc->createTextNode($text), $node);

        return false;
    }
}
