<?php
namespace MB\Support\HtmlCleaner\Transformer;

use DOMNode;
use MB\Support\HtmlCleaner\Contracts\TransformerInterface;

final class Unwrap implements TransformerInterface
{
    public function apply(DOMNode $node): bool
    {
        $parent = $node->parentNode;

        if ($this->isRootElement($node)) {
            return true;
        }

        if (!$parent) {
            return false;
        }

        while ($node->firstChild) {
            $parent->insertBefore($node->firstChild, $node);
        }

        $parent->removeChild($node);

        return true;
    }

    protected function isRootElement(DOMNode $node): bool
    {
        return $node->nodeName === 'html' || $node->nodeName === 'body';
    }
}
