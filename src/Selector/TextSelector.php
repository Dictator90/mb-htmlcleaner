<?php
namespace MB\Support\HtmlCleaner\Selector;

use DOMNode;
use MB\Support\HtmlCleaner\Contracts\SelectorInterface;

class TextSelector implements SelectorInterface
{
    public function matches(DOMNode $node): bool
    {
        return $node->nodeType === XML_TEXT_NODE;
    }
}