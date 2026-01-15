<?php
namespace MB\Support\HtmlCleaner\Rule;

use DOMNode;
use MB\Support\HtmlCleaner\Contracts\RuleInterface;
use MB\Support\HtmlCleaner\Contracts\SelectorInterface;
use MB\Support\HtmlCleaner\Contracts\TransformerInterface;

/**
 * Class Rule
 *
 * Represents a rule for HTML cleaning process.
 * A rule consists of a selector and a transformer, along with a priority level.
 * The selector determines whether the rule applies to a given node,
 * and the transformer performs modifications on the node if the rule applies.
 *
 * @package MB\Support\HtmlCleaner
 */
final class Rule implements RuleInterface
{
    /**
     * Rule constructor.
     *
     * @param SelectorInterface $selector The selector used to match nodes.
     * @param TransformerInterface|callable $transformer The transformer applied to matched nodes.
     * @param int $priority The priority of the rule (higher numbers indicate higher priority).
     */
    public function __construct(
        private SelectorInterface $selector,
        private TransformerInterface|\Closure $transformer,
        private int $priority = 0
    ) {}

    /**
     * Creates a new RuleBuilder instance to define a rule starting with a selector.
     *
     * @param SelectorInterface $selector The selector to use for matching nodes.
     * @return RuleBuilder A new RuleBuilder instance.
     */
    public static function when(SelectorInterface $selector): RuleBuilder
    {
        return new RuleBuilder($selector);
    }

    /**
     * @inheritdoc
     */
    public function supports(DOMNode $node): bool
    {
        return $this->selector->matches($node);
    }

    /**
     * @inheritdoc
     */
    public function apply(DOMNode $node): bool
    {
        if ($this->transformer instanceof \Closure) {
            return ($this->transformer)($node);
        }

        return $this->transformer->apply($node);
    }

    /**
     * @inheritdoc
     */
    public function priority(): int
    {
        return $this->priority;
    }
}