<?php
namespace MB\Support\HtmlCleaner\Transformer;

use MB\Support\HtmlCleaner\Contracts\TransformerInterface;

class TransformerFacade
{
    public static function null(): NullTransformer
    {
        return new NullTransformer();
    }

    public static function batch(TransformerInterface ...$transformers): BatchTransformer
    {
        return new BatchTransformer($transformers);
    }

    public static function replace(null|string $tag = null): Replace
    {
        return new Replace($tag);
    }

    public static function remove(): Remove
    {
        return new Remove();
    }

    public static function wrap(string $tag): Wrap
    {
        return new Wrap($tag);
    }

    public static function unwrap(): Unwrap
    {
        return new Unwrap();
    }

    public static function normalizeWhitespace(): NormalizeWhitespace
    {
        return new NormalizeWhitespace();
    }

    public static function onlyText(): OnlyText
    {
        return new OnlyText();
    }

    public static function allowStyles(string ...$styles): AllowStyles
    {
        return new AllowStyles($styles);
    }

    public static function dropStyles(string ...$styles): StripStyles
    {
        return new StripStyles($styles);
    }

    public static function allowAttrs(string ...$attributes): AllowAttributes
    {
        return new AllowAttributes($attributes ?: []);
    }

    public static function changeAttr(string $attribute, string $value): ChangeAttribute
    {
        return new ChangeAttribute($attribute, $value);
    }

    public static function dropAttrs(string ...$attributes): StripAttributes
    {
        return new StripAttributes($attributes);
    }
}