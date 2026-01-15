<?php
namespace MB\Support\HtmlCleaner\Selector;

use DOMNode;
use MB\Support\HtmlCleaner\Contracts\SelectorInterface;

/**
 * A selector that matches any DOM node.
 *
 * This class implements the SelectorInterface and can be used to select
 * all nodes in a DOM tree, as its matches() method always returns true.
 *
 * @package MB\Support\HtmlCleaner
 */
final class AnySelector implements SelectorInterface
{
    public function matches(DOMNode $node): bool
    {
        return true;
    }
}