# MB HTML Cleaner

HTML Cleaner - это мощный PHP-пакет для очистки и трансформации HTML-кода. Он предоставляет удобный fluent-интерфейс для манипулирования HTML-структурами, удаления нежелательных элементов и атрибутов, нормализации содержимого и выполнения различных преобразований HTML.

## Установка

Установите пакет через Composer:

```bash
composer require mb/htmlcleaner
```

## Базовое использование

```php
use MB\Support\HtmlCleaner\HtmlCleaner;

$cleaner = new HtmlCleaner();
$result = $cleaner->clean('<p><b>Привет</b> <i>мир</i>!</p>');
```

## Методы HtmlCleaner

### transform()

Преобразует элементы, выбранные с помощью указанных селекторов, используя TransformerInterface или замыкание.

```php
use MB\Support\HtmlCleaner\Selector\SelectorFacade;
use MB\Support\HtmlCleaner\Transformer\Replace;

$result = $cleaner
    ->transform(
        SelectorFacade::tag('span'),
        Replace::tag('b')
    )
    ->clean($html);
```

### transformAnd()

Применяет преобразование, когда совпадают все указанные селекторы (логическое И).

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

Применяет преобразование, когда совпадает любой из указанных селекторов (логическое ИЛИ).

```php
$result = $cleaner
    ->transformOr([
        SelectorFacade::tag('b'),
        SelectorFacade::tag('strong')
    ], new Replace('strong'))
    ->clean($html);
```

### onlyText()

Оставляет только текстовое содержимое указанных тегов, удаляя все дочерние элементы.

```php
// Оставить текстовое содержимое тегов span и p
$result = $cleaner
    ->onlyText('span', 'p')
    ->clean($html);

// Оставить текстовое содержимое всех элементов
$result = $cleaner
    ->onlyText(null)
    ->clean($html);
```

### drop()

Удаляет полностью элементы, соответствующие указанным именам тегов.

```php
// Удалить теги script, style и meta
$result = $cleaner
    ->drop('script', 'style', 'meta')
    ->clean($html);
```

### unwrap()

Распаковывает элементы (удаляет тег, но сохраняет его дочерние элементы), соответствующие указанным именам тегов.

```php
// Удалить теги html и body, сохранив их содержимое
$result = $cleaner
    ->unwrap('html', 'body')
    ->clean($html);
```

### wrap()

Оборачивает элементы, соответствующие указанным именам тегов, новым тегом.

```php
// Обернуть все теги span в div
$result = $cleaner
    ->wrap(['span'], 'div')
    ->clean($html);
```

### allowStyles()

Разрешает использовать только определенные встроенные стили для элементов.

```php
// Разрешить только стили background-color и color
$result = $cleaner
    ->allowStyles('background-color', 'color')
    ->clean($html);
```

### stripStyles()

Удаляет определенные встроенные стили из всех элементов.

```php
// Удалить стили background и margin
$result = $cleaner
    ->stripStyles('background', 'margin')
    ->clean($html);
```

### stripAttributes()

Удаляет указанные атрибуты из всех элементов.

```php
// Удалить атрибуты class, id и onclick
$result = $cleaner
    ->stripAttributes('class', 'id', 'onclick')
    ->clean($html);
```

### changeAttr()

Изменяет значения атрибутов для определенных тегов.

```php
// Изменить атрибут href всех тегов a на "#"
$result = $cleaner
    ->changeAttr(['a'], 'href', '#')
    ->clean($html);
```

### replaceTag()

Заменяет один тег на другой, копируя все атрибуты и стили.

```php
// Заменить теги span на теги div
$result = $cleaner
    ->replaceTag(['span'], 'div')
    ->clean($html);
```

### normalizeWhitespace()

Нормализует пробелы внутри текстовых узлов (объединяет несколько пробелов, обрезает).

```php
$result = $cleaner
    ->normalizeWhitespace()
    ->clean($html);
```

### outputFragment()

Устанавливает режим вывода для возврата только фрагмента HTML (без <html>, <body> и т.д.).

```php
$result = $cleaner
    ->outputFragment()
    ->clean($html);
```

### outputDocument()

Устанавливает режим вывода для возврата полного HTML-документа.

```php
$result = $cleaner
    ->outputDocument()
    ->clean($html);
```

### clean()

Очищает предоставленную строку HTML в соответствии с настроенными правилами.

```php
$result = $cleaner->clean($html);
```

## Продвинутые примеры

### Преобразование HTML-документа

```php
$html = '<!DOCTYPE html><html dir="ltr"><head><meta charset="utf-8"><style>body{margin:0;}</style></head><body><p>Текст <b>примера</b></p></body></html>';

$result = $cleaner
    ->unwrap('html', 'body')
    ->drop('style', 'meta', 'head')
    ->normalizeWhitespace()
    ->outputFragment()
    ->clean($html);

// Результат: <p>Текст <b>примера</b></p>
```

### Преобразование на основе стилей

```php
$html = '<span style="font-weight:bold">Привет</span><p>Мир</p>';

$result = $cleaner
    ->transform(
        SelectorFacade::style('font-weight', 'bold'),
        Replace::tag('b'),
        10
    )
    ->stripStyles('font-weight')
    ->clean($html);

// Результат: <b>Привет</b><p>Мир</p>
```

### Условное преобразование с использованием замыканий

```php
$html = '<span style="font-weight:bold">Привет</span><p>Мир</p>';

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

// Результат: <b class="changed">Привет</b><p>Мир</p>
```