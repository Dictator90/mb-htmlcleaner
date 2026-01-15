<?php
namespace MB\Support\HtmlCleaner\Selector;

use MB\Support\HtmlCleaner\Contracts\SelectorInterface;

/**
 * Abstract class ConditionSelector
 *
 * Represents a selector that applies a logical condition over multiple selectors.
 * This class serves as a base for implementing composite selection logic,
 * such as "AND", "OR" conditions between individual selectors.
 *
 * @package MB\Support\HtmlCleaner
 */
abstract class ConditionSelector implements SelectorInterface
{
    /** @var SelectorInterface[] */
    protected array $selectors;

    /**
     * Create a new ConditionSelector instance with the given selectors.
     *
     * @param SelectorInterface ...$selectors One or more selectors to combine.
     */
    public function __construct(SelectorInterface ...$selectors)
    {
        $this->selectors = $selectors;
    }

    /**
     * Static factory method to create a new instance of the condition selector.
     *
     * @param SelectorInterface ...$selectors One or more selectors to combine.
     * @return static
     */
    public static function make(SelectorInterface ...$selectors): static
    {
        return new static(...$selectors);
    }
}