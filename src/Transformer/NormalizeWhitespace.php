<?php
namespace MB\Support\HtmlCleaner\Transformer;

use DOMElement;
use DOMNode;
use MB\Support\HtmlCleaner\Contracts\TransformerInterface;

final class NormalizeWhitespace implements TransformerInterface
{
    public function apply(DOMNode $node): bool
    {
        foreach ($node->childNodes as $child) {
            if ($child->nodeType === XML_TEXT_NODE) {
                $child->nodeValue = preg_replace('/\s+/u', ' ', $child->nodeValue);
            }
        }

        return true;
    }
}
