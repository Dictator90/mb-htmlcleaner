<?php
namespace MB\Support\HtmlCleaner\Selector;

use DOMComment;
use DOMNode;
use MB\Support\HtmlCleaner\Contracts\SelectorInterface;

class CommentSelector implements SelectorInterface
{
    public function matches(DOMNode $node): bool
    {
        return $node instanceof DOMComment;
    }
}