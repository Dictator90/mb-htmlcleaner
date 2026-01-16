# MB HTML Cleaner

[Русская документация](README.ru.md)

MB HTML Cleaner is a powerful PHP package for cleaning and transforming HTML content. It provides a fluent interface for manipulating HTML structures, removing unwanted elements and attributes, normalizing content, and performing various HTML transformations.

## Installation

Install the package via Composer:

```bash
composer require mb4it/htmlcleaner
```

## Basic Usage

```php
use MB\Support\HtmlCleaner\HtmlCleaner;

$cleaner = new HtmlCleaner();
$result = $cleaner->clean('<p><b>Hello</b> <i>world</i>!</p>');
```

## HtmlCleaner Methods

### stripEmptyTag()

Remove empty elements (with no text content or only whitespace) matching the given tag names.

```php
// Remove empty p tags
$result = $cleaner
    ->stripEmptyTag('p')
    ->clean($html);

// Remove all empty elements
$result = $cleaner
    ->stripEmptyTag(null)
    ->clean($html);
```

## HtmlCleaner Methods

### transform()

Transform elements selected by the provided selectors using a TransformerInterface or closure. Supports CSS-like selectors for advanced element selection.

```php
use MB\Support\HtmlCleaner\Selector\SelectorFacade;
use MB\Support\HtmlCleaner\Transformer\Replace;

// Basic tag selection
$result = $cleaner
    ->transform(
        SelectorFacade::tag('span'),
        Replace::tag('b')
    )
    ->clean($html);

// CSS-like selector syntax
$result = $cleaner
    ->transform(
        'div.container > p',
        Replace::tag('div')
    )
    ->clean($html);

// Multiple selectors with comma (OR)
$result = $cleaner
    ->transform(
        'strong, span.bold',
        Replace::tag('b')
    )
    ->clean($html);

// Attribute selectors
$result = $cleaner
    ->transform(
        'a[href^="/"]',
        function ($node) {
            $node->setAttribute('target', '_blank');
        }
    )
    ->clean($html);

// Pseudo-class selectors
$result = $cleaner
    ->transform(
        'div:has(p)',
        Replace::tag('section')
    )
    ->clean($html);

$result = $cleaner
    ->transform(
        'span:has-text("Hello")',
        Replace::tag('b')
    )
    ->clean($html);

// Class and attribute combinations
$result = $cleaner
    ->transform(
        'div.highlight[data-type="article"]',
        Replace::tag('article')
    )
    ->clean($html);
```

### transformAnd()

Apply transformation when all specified selectors match (logical AND).

```php
use MB\Support\HtmlCleaner\Selector\SelectorFacade;

$result = $cleaner
    ->transformAnd([
        SelectorFacade::tag('span'),
        SelectorFacade::style('font-weight', 'bold')
    ], new Replace('strong'))
    ->clean($html);
```

### transformOr()

Apply transformation when any of the specified selectors match (logical OR).

```php
$result = $cleaner
    ->transformOr([
        SelectorFacade::tag('b'),
        SelectorFacade::tag('strong')
    ], new Replace('strong'))
    ->clean($html);
```

### onlyText()

Keep only text content of specified tags, removing all child elements.

```php
// Keep text content of span and p tags
$result = $cleaner
    ->onlyText('span', 'p')
    ->clean($html);

// Keep text content of all elements
$result = $cleaner
    ->onlyText(null)
    ->clean($html);
```

### drop()

Remove entire elements matching the given tag names.

```php
// Remove script, style, and meta tags
$result = $cleaner
    ->drop('script', 'style', 'meta')
    ->clean($html);
```

### unwrap()

Unwrap elements (remove the tag but keep its children) matching the given tag names.

```php
// Remove html and body tags, keeping their content
$result = $cleaner
    ->unwrap('span', 'font')
    ->clean($html);
```

### wrap()

Wrap elements matching the given tag names with a new tag.

```php
// Wrap all span elements with a div
$result = $cleaner
    ->wrap(['span'], 'div')
    ->clean($html);
```

### allowStyles()

Allow only specific inline styles on elements.

```php
// Allow only background-color and color styles
$result = $cleaner
    ->allowStyles('background-color', 'color')
    ->clean($html);
```

### stripStyles()

Strip specific inline styles from all elements.

```php
// Remove background and margin styles
$result = $cleaner
    ->stripStyles('background', 'margin')
    ->clean($html);
```

### stripAttributes()

Remove specified attributes from all elements.

```php
// Remove class, id, and onclick attributes
$result = $cleaner
    ->stripAttributes('class', 'id', 'onclick')
    ->clean($html);
```

### changeAttr()

Change attribute values for specific tags.

```php
// Change href attribute of all a tags to "#"
$result = $cleaner
    ->changeAttr(['a'], 'href', '#')
    ->clean($html);
```

### replaceTag()

Replace one tag with another, copying all attributes and styles.

```php
// Replace span tags with div tags
$result = $cleaner
    ->replaceTag(['span'], 'div')
    ->clean($html);
```

### normalizeWhitespace()

Normalize whitespace within text nodes (collapse multiple spaces, trim).

```php
$result = $cleaner
    ->normalizeWhitespace()
    ->clean($html);
```

### outputFragment()

Set output mode to return only an HTML fragment (no <html>, <body> etc.).

```php
$result = $cleaner
    ->outputFragment()
    ->clean($html);
```

### outputDocument()

Set output mode to return a full HTML document.

```php
$result = $cleaner
    ->outputDocument()
    ->clean($html);
```

### clean()

Clean the provided HTML string according to the configured rules.

```php
$result = $cleaner->clean($html);
```

## Advanced Examples

### HTML Document Transformation

```php
$html = '<!DOCTYPE html><html dir="ltr"><head><meta charset="utf-8"><style>body{margin:0;}</style></head><body><p>Text <b>example</b></p></body></html>';

$result = $cleaner
    ->drop('style', 'meta', 'head')
    ->normalizeWhitespace()
    ->outputFragment()
    ->clean($html);

// Result: <p>Text <b>example</b></p>
```

### Style-Based Transformation

```php
$html = '<span style="font-weight:bold">Hello</span><p>World</p>';

$result = $cleaner
    ->transform(
        SelectorFacade::style('font-weight', 'bold'),
        Replace::tag('b'),
        10
    )
    ->stripStyles('font-weight')
    ->clean($html);

// Result: <b>Hello</b><p>World</p>
```

### Conditional Transformation with Closures

```php
$html = '<span style="font-weight:bold">Hello</span><p>World</p>';

$result = $cleaner
    ->transform(
        SelectorFacade::style('font-weight', 'bold'),
        function (DomNode $node) {
            $doc = $node->ownerDocument;
            $parent = $node->parentNode;
            if (!$doc || !$parent) {
                return false;
            }

            $newNode = $doc->createElement('b');
            $newNode->setAttribute('class', 'changed');
            while ($node->firstChild) {
                $newNode->appendChild($node->firstChild);
            }

            $parent->replaceChild($newNode, $node);

            return true;
        }
    )
    ->clean($html);

// Result: <b class="changed">Hello</b><p>World</p>
```