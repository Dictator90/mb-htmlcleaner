<?php
namespace MB\Support\HtmlCleaner\Selector;

use DOMNode;
use MB\Support\HtmlCleaner\Selector\ConditionSelector;

final class OrSelector extends ConditionSelector
{
    public function matches(DOMNode $node): bool
    {
        foreach ($this->selectors as $selector) {
            if ($selector->matches($node)) {
                return true;
            }
        }

        return false;
    }
}