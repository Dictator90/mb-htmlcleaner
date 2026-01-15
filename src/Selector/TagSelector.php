<?php
namespace MB\Support\HtmlCleaner\Selector;

use DOMNode;
use DOMElement;
use MB\Support\HtmlCleaner\Contracts\SelectorInterface;

class TagSelector implements SelectorInterface
{
    public function __construct(private string $tag)
    {
        $this->tag = strtolower($this->tag);
    }

    public function matches(DOMNode $node): bool
    {
        if ($this->tag === 'body' || $this->tag === 'html') {
            print_r([
                strtolower($node->tagName),
                $this->tag,
                strtolower($node->tagName) === $this->tag,
            ]);
            print_r(PHP_EOL);
        }

        return $node instanceof DOMElement && strtolower($node->tagName) === $this->tag;
    }
}