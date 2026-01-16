<?php
namespace MB\Support\HtmlCleaner\Selector\Helper;

use MB\Support\HtmlCleaner\Contracts\SelectorInterface;
use MB\Support\HtmlCleaner\Selector\AndSelector;
use MB\Support\HtmlCleaner\Selector\AnySelector;
use MB\Support\HtmlCleaner\Selector\AttributeSelector;
use MB\Support\HtmlCleaner\Selector\ChildSelector;
use MB\Support\HtmlCleaner\Selector\ClassSelector;
use MB\Support\HtmlCleaner\Selector\DescendantSelector;
use MB\Support\HtmlCleaner\Selector\HasSelector;
use MB\Support\HtmlCleaner\Selector\HasTextSelector;
use MB\Support\HtmlCleaner\Selector\NotSelector;
use MB\Support\HtmlCleaner\Selector\OrSelector;
use MB\Support\HtmlCleaner\Selector\TagSelector;

final class StringParser
{
    public static function queryParse(string $selector): SelectorInterface
    {
        // OR: div, span
        if (str_contains($selector, ',')) {
            return new OrSelector(
                ...array_map(
                    fn($s) => self::queryParse(trim($s)),
                    array_map('trim', explode(',', $selector))
                )
            );
        }

        // :not(...)
        if (preg_match('/:not\(([^()]+)\)/', $selector, $m)) {
            $base = trim(str_replace($m[0], '', $selector));
            return new AndSelector(...[self::queryParse($base), new NotSelector(self::queryParse($m[1]))]);
        }

        // :has(...)
        if (preg_match('/:has\(([^()]+)\)/', $selector, $m)) {
            $base = trim(str_replace($m[0], '', $selector));
            return new HasSelector(self::queryParse($base ?: '*'), self::queryParse(trim($m[1])));
        }

        // :empty
        //todo:
        /*
        if (str_contains($selector, ':empty')) {
            $base = trim(str_replace(':empty', '', $selector));
            return new AndSelector([
                self::queryParse($base ?: '*'),
                new EmptyTagSelector()
            ]);
        }*/

        // :has-text()
        if (preg_match('/:has-text\((["\'])(.*?)\1\)/', $selector, $m)) {
            $base = trim(str_replace($m[0], '', $selector));
            return new HasTextSelector(self::queryParse($base ?: '*'), $m[2]);
        }

        // child >
        if (preg_match('/(.+?)\s*>\s*(.+)/', $selector, $m)) {
            return new ChildSelector(self::queryParse($m[1]), self::queryParse($m[2]));
        }

        // descendant space
        if (preg_match('/(.+)\s+(.+)/', $selector, $m)) {
            return new DescendantSelector(self::queryParse($m[1]), self::queryParse($m[2]));
        }

        // div.simple-class
        return self::querySimpleParse($selector);
    }

    private static function querySimpleParse(string $selector): SelectorInterface
    {
        $selectors = [];

        if ($selector === '*') {
            return new AnySelector();
        }

        if (preg_match('/^[a-z0-9]+/i', $selector, $m)) {
            $selectors[] = new TagSelector($m[0]);
        }

        preg_match_all('/\.([a-z0-9_-]+)/i', $selector, $m);
        foreach ($m[1] as $class) {
            $selectors[] = new ClassSelector([$class]);
        }

        preg_match_all(
            '/\[([a-z0-9_-]+)([\^\$\*]?=)?"?([^"]*)"?\]/i',
            $selector,
            $m,
            PREG_SET_ORDER
        );
        foreach ($m as $attr) {
            $selectors[] = new AttributeSelector(
                $attr[1],
                $attr[2] ?? null,
                $attr[3] ?? null
            );
        }

        return count($selectors) === 1
            ? $selectors[0]
            : new AndSelector(...$selectors);
    }
}
