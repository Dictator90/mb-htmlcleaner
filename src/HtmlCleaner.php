<?php
namespace MB\Support\HtmlCleaner;

use MB\Support\{
    HtmlCleaner\Contracts\EngineInterface,
    HtmlCleaner\Contracts\SelectorInterface,
    HtmlCleaner\Contracts\OutputMode,
    HtmlCleaner\Contracts\TransformerInterface,
    HtmlCleaner\Engine\DOMDocumentEngine,
    HtmlCleaner\Rule\Rule,
    HtmlCleaner\Selector\AndSelector,
    HtmlCleaner\Selector\OrSelector,
    HtmlCleaner\Selector\SelectorFacade,
    HtmlCleaner\Transformer\AllowStyles,
    HtmlCleaner\Transformer\ChangeAttribute,
    HtmlCleaner\Transformer\NormalizeWhitespace,
    HtmlCleaner\Transformer\OnlyText,
    HtmlCleaner\Transformer\Replace,
    HtmlCleaner\Transformer\StripAttributes,
    HtmlCleaner\Transformer\StripStyles,
    HtmlCleaner\Transformer\Unwrap,
    HtmlCleaner\Transformer\Wrap
};

/**
 * Class HtmlCleaner
 *
 * A fluent interface for cleaning and transforming HTML content based on defined rules.
 * It uses an underlying engine (by default DOMDocumentEngine) to apply transformations
 * such as stripping tags, attributes, styles, or rewriting parts of the document.
 *
 * Example usage:
 * <code>
 * use MB\Support\HtmlCleaner\HtmlCleaner;
 *
 * $cleaner = new HtmlCleaner();
 * $result =
 *     $cleaner
 *         ->unwrap('p')
 *         ->clean('<p><b>Hello</b> <i>world</i>!</p>')
 *  ;
 *  //<b>Hello</b> <i>world</i>!
 * </code>
 *
 * @package MB\Support\HtmlCleaner
 */
final class HtmlCleaner
{
    private EngineInterface $engine;

    public static function make(?EngineInterface $engine = null): self
    {
        return new self($engine);
    }

    /**
     * Create a new HtmlCleaner instance.
     *
     * @param EngineInterface|null $engine The engine used for HTML manipulation. Uses DOMDocumentEngine by default.
     */
    public function __construct(?EngineInterface $engine = null)
    {
        $this->engine = $engine ?: new DOMDocumentEngine();
    }

    /**
     * Transform elements selected by the provided selectors using a TransformerInterface.
     *
     * @param SelectorInterface[]|SelectorInterface $selectors One or more selectors to match elements.
     * @param Replace $replace Transformer defining how to replace matched elements.
     * @param int $priority Rule priority.
     * @return $this
     */
    public function transform(array|SelectorInterface $selectors, TransformerInterface|\Closure $replace, $priority = 0): self
    {
        if (!is_array($selectors)) {
            $selectors = [$selectors];
        }

        foreach ((array)$selectors as $selector) {
            $this->engine->rule(Rule::when($selector)->transform($replace, $priority));
        }

        return $this;
    }

    /**
     * @param SelectorInterface[] $selectors
     */
    public function transformAnd(array $selectors, TransformerInterface|\Closure $replace, $priority = 0): self
    {
        $this->engine->rule(Rule::when(AndSelector::make(...$selectors))->transform($replace, $priority));
        return $this;
    }

    /**
     * @param SelectorInterface[] $selectors
     */
    public function transformOr(array $selectors, TransformerInterface|\Closure $replace, $priority = 0): self
    {
        $this->engine->rule(Rule::when(OrSelector::make(...$selectors))->transform($replace, $priority));
        return $this;
    }

    /**
     * Keep only text content of specified tags, removing all child elements.
     *
     * @param string|null ...$tags Tag names to process. If null, applies to all elements.
     * @return $this
     */
    public function onlyText(...$tags): self
    {
        if ($tags[0] === null) {
            $this->transform(SelectorFacade::any(), new OnlyText(), 999);
        } else {
            $selectors = array_map(fn($tag) => SelectorFacade::tag($tag), $tags);
            $this->transformOr($selectors, new OnlyText(), 999);
        }

        return $this;
    }

    /**
     * Remove entire elements matching the given tag names.
     *
     * @param string ...$tags Tag names to remove from HTML.
     * @return $this
     */
    public function drop(...$tags): self
    {
        $selectors = array_map(fn($tag) => SelectorFacade::tag($tag), $tags);
        $this->transformOr($selectors, new Replace(null), 100);

        return $this;
    }

    /**
     * Unwrap elements (remove the tag but keep its children) matching the given tag names.
     *
     * @param string ...$tags Tag names to unwrap.
     * @return $this
     */
    public function unwrap(...$tags): self
    {
        $selectors = array_map(fn($tag) => SelectorFacade::tag($tag), $tags);
        $this->transformOr($selectors, new Unwrap(), -100);

        return $this;
    }

    public function wrap(array $tags, string $newTag): self
    {
        $selectors = array_map(fn($tag) => SelectorFacade::tag($tag), $tags);
        $this->transformOr($selectors, new Wrap($newTag), 90);

        return $this;
    }

    /**
     * Allow only specific inline styles on elements.
     *
     * @param string ...$styles List of allowed style properties (e.g., 'color', 'font-weight').
     * @return $this
     */
    public function allowStyles(...$styles)
    {
        $this->transform(SelectorFacade::any(), new AllowStyles($styles));
        return $this;
    }

    /**
     * Strip specific inline styles from all elements.
     *
     * @param string ...$styles List of style properties to remove (e.g., 'background', 'margin').
     * @return $this
     */
    public function stripStyles(...$styles)
    {
        $this->transform(SelectorFacade::any(), new StripStyles($styles));
        return $this;
    }

    /**
     * Remove specified attributes from all elements.
     *
     * @param string ...$attributes Attribute names to strip (e.g., 'class', 'id', 'onclick').
     * @return $this
     */
    public function stripAttributes(...$attributes): self
    {
        $this->transform(SelectorFacade::any(), new StripAttributes($attributes));
        return $this;
    }

    /**
     * @param string[] $tags
     * @param string $attr
     * @param string|int $value
     * @return $this
     */
    public function changeAttr(array $tags, string $attr, string|int $value): self
    {
        $selectors = array_map(fn($tag) => SelectorFacade::tag($tag), $tags);
        $this->transform($selectors, new ChangeAttribute($attr, $value));
        return $this;
    }

    /**
     * @param array<int, string> $tags
     * @param string $tag
     * @return self
     */
    public function replaceTag(array $tags, string $tag): self
    {
        $selectors = array_map(fn($tag) => SelectorFacade::tag($tag), $tags);
        $replace = (new Replace($tag))->copyStyles()->copyClassList()->copyAttrs();
        $this->transformOr($selectors, $replace, 100);
        return $this;
    }

    /**
     * Normalize whitespace within text nodes (collapse multiple spaces, trim).
     *
     * @return $this
     */
    public function normalizeWhitespace(): self
    {
        $this->transform(SelectorFacade::any(), new NormalizeWhitespace());
        return $this;
    }

    /**
     * Set output mode to return only an HTML fragment (no <html>, <body> etc.).
     *
     * @return $this
     */
    public function outputFragment(): self
    {
        $this->engine->output(OutputMode::FRAGMENT);
        return $this;
    }

    /**
     * Set output mode to return a full HTML document.
     *
     * @return $this
     */
    public function outputDocument(): self
    {
        $this->engine->output(OutputMode::DOCUMENT);
        return $this;
    }

    /**
     * Clean the provided HTML string according to the configured rules.
     *
     * @param string $html The input HTML to clean.
     * @return string The cleaned HTML.
     */
    public function clean(string $html): string
    {
        return $this->engine->clean($html);
    }
}