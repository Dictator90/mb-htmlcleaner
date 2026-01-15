<?php

namespace MB\Support\HtmlCleaner\Contracts;

use DOMNode;

/**
 * Interface for defining rules to clean HTML nodes.
 *
 * Each rule should determine whether it supports a given node,
 * apply transformations to the node if supported, and define its priority
 * relative to other rules.
 *
 * @package MB\Support\HtmlCleaner
 */
interface RuleInterface
{
    /**
     * Determines if the rule supports the given node.
     *
     * @param DOMNode $node The node to check.
     * @return bool True if the rule supports the node, false otherwise.
     */
    public function supports(DOMNode $node): bool;

    /**
     * Applies the rule's transformation to the given node.
     *
     * @param DOMNode $node The node to transform.
     * @return bool The value is True if it is necessary to continue applying, otherwise the value is false.
     */
    public function apply(DOMNode $node): bool;

    /**
     * Returns the priority of the rule.
     *
     * Rules with higher priority are applied first.
     *
     * @return int The priority value.
     */
    public function priority(): int;
}