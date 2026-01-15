<?php
namespace MB\Support\HtmlCleaner\Contracts;

/**
 * Interface for HTML cleaning engines.
 *
 * This interface defines the contract for classes that implement HTML cleaning functionality.
 * It allows applying rules, cleaning HTML content, and setting the output mode.
 *
 * @package MB\Support\HtmlCleaner
 */
interface EngineInterface
{
    /**
     * Applies a rule to the cleaning process.
     *
     * @param RuleInterface $rule The rule to apply.
     * @return self
     */
    public function rule(RuleInterface $rule): self;

    /**
     * Cleans the provided HTML string according to the applied rules.
     *
     * @param string $html The HTML string to clean.
     * @return string The cleaned HTML string.
     */
    public function clean(string $html): string;

    /**
     * Sets the output mode for the cleaned HTML.
     *
     * @param OutputMode $mode The output mode to use.
     * @return self
     */
    public function output(OutputMode $mode): self;
}