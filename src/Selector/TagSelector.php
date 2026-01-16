<?php
namespace MB\Support\HtmlCleaner\Selector;

use DOMNode;
use DOMElement;
use MB\Support\HtmlCleaner\Contracts\SelectorInterface;

class TagSelector implements SelectorInterface
{
    public function __construct(protected string $tag)
    {
        $this->tag = strtolower($this->tag);
    }


    public function matches(DOMNode $node): bool
    {
        return $node instanceof DOMElement && strtolower($node->tagName) === $this->tag;
    }
}