<?php
namespace MB\Support\HtmlCleaner\Selector;

use DOMNode;

/**
 * Represents a logical AND combination of multiple selectors.
 *
 * This selector matches a node only if all of its child selectors match the node.
 * It is typically used to combine multiple conditions that must all be satisfied
 * for a node to be selected.
 *
 * @package MB\Support\HtmlCleaner
 */
final class AndSelector extends ConditionSelector
{
    public function matches(DOMNode $node): bool
    {
        foreach ($this->selectors as $selector) {
            if (!$selector->matches($node)) {
                return false;
            }
        }

        return true;
    }
}