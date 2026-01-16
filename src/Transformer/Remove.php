<?php
namespace MB\Support\HtmlCleaner\Transformer;

use DOMElement;
use DOMNode;
use MB\Support\HtmlCleaner\Contracts\TransformerInterface;

final class Remove implements TransformerInterface
{
    public function apply(DOMNode $node): bool
    {
        if ($node instanceof \DOMCharacterData || $node instanceof DOMElement) {
            $node->remove();
        }

        return false;
    }
}
