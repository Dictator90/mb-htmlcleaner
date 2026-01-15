<?php

namespace MB\Support\HtmlCleaner\Contracts;

use DOMElement;

/**
 * Interface for defining a selector that can match DOM elements.
 *
 * @package MB\Support\HtmlCleaner
 */
interface SelectorInterface
{
    /**
     * Determines whether the given DOM element matches the selector criteria.
     *
     * @param DOMElement $node The DOM element to check.
     * @return bool True if the element matches, false otherwise.
     */
    public function matches(DOMElement $node): bool;
}