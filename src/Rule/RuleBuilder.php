<?php

namespace MB\Support\HtmlCleaner\Rule;

use MB\Support\HtmlCleaner\Contracts\SelectorInterface;
use MB\Support\HtmlCleaner\Contracts\TransformerInterface;

/**
 * RuleBuilder is responsible for creating a Rule instance based on a selector and a transformer.
 *
 * This class provides a simple interface to define transformation rules for HTML cleaning,
 * where a selector determines which elements are targeted, and a transformer defines how
 * those elements should be transformed or modified.
 *
 * @package MB\Support\HtmlCleaner
 */
final class RuleBuilder
{
    /**
     * Create a new RuleBuilder instance.
     *
     * @param SelectorInterface $selector The selector defining which HTML elements the rule applies to.
     */
    public function __construct(private SelectorInterface $selector) {}

    /**
     * Create and return a new Rule with the given transformer and priority.
     *
     * @param TransformerInterface|\Closure $t The transformer to apply when the rule is executed.
     * @param int $priority The priority of the rule (higher numbers indicate higher priority).
     * @return Rule A new Rule instance configured with the selector, transformer, and priority.
     */
    public function transform(TransformerInterface|\Closure $t, int $priority = 0): Rule
    {
        return new Rule($this->selector, $t, $priority);
    }
}