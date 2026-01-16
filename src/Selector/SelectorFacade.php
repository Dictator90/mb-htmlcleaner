<?php
namespace MB\Support\HtmlCleaner\Selector;

use MB\Support\HtmlCleaner\Contracts\SelectorInterface;
use MB\Support\HtmlCleaner\Selector\Helper\StringParser;

final class SelectorFacade
{
    public static function any(\Closure|null $callback = null): SelectorInterface
    {
        return new AnySelector($callback);
    }

    public static function comment(): SelectorInterface
    {
        return new CommentSelector();
    }

    public static function element(): SelectorInterface
    {
        return new ElementSelector();
    }

    public static function text(): SelectorInterface
    {
        return new TextSelector();
    }

    public static function tag(string $tag): SelectorInterface
    {
        return new TagSelector($tag);
    }

    public static function query(string $selector): SelectorInterface
    {
        return StringParser::queryParse($selector);
    }

    public static function emptyTag(string $tag): SelectorInterface
    {
        return new EmptyTagSelector($tag);
    }

    public static function attr(string $name, ?string $value = null, string $operator = null): SelectorInterface
    {
        return new AttributeSelector($name, $value, $operator);
    }

    public static function classList(string ...$classes): SelectorInterface
    {
        return new ClassSelector($classes);
    }

    public static function style(string $property, ?string $value = null): SelectorInterface
    {
        return new StyleSelector($property, $value);
    }

    public static function and(SelectorInterface ...$selectors): SelectorInterface
    {
        return new AndSelector(...$selectors);
    }

    public static function or(SelectorInterface ...$selectors): SelectorInterface
    {
        return new OrSelector(...$selectors);
    }

    public static function not(SelectorInterface $selector): SelectorInterface
    {
        return new NotSelector($selector);
    }
}
