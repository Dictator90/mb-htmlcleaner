<?php

namespace MB\Support\HtmlCleaner\Contracts;

use DOMNode;

/**
 * Interface for transforming DOM nodes.
 *
 * Implementations of this interface are responsible for applying transformations
 * to a given DOM node. The return value indicates whether further processing
 * (propagation) of the transformation chain should continue.
 *
 * @package MB\Support\HtmlCleaner
 */
interface TransformerInterface
{
    /**
     * Applies transformation logic to the given DOM node.
     *
     * @param DOMNode $node The DOM node to transform.
     * @return bool Returns false to stop further propagation; true to continue.
     */
    public function apply(DOMNode $node): bool;
}