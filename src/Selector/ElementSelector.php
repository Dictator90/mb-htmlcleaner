<?php
namespace MB\Support\HtmlCleaner\Selector;

use DOMNode;
use DOMElement;
use MB\Support\HtmlCleaner\Contracts\SelectorInterface;

class ElementSelector implements SelectorInterface
{
    public function matches(DOMNode $node): bool
    {
        return $node instanceof DOMElement;
    }
}
