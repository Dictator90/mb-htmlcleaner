<?php
namespace MB\Support\HtmlCleaner\Selector;

use DOMNode;
use DOMElement;
use MB\Support\HtmlCleaner\Contracts\SelectorInterface;

class EmptyTagSelector extends TagSelector
{
    public function matches(DOMNode $node): bool
    {
        return parent::matches($node) && !trim($node->nodeValue);
    }
}