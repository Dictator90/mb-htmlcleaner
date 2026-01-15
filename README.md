# MB HTML Cleaner

HTML Cleaner - это мощный PHP-пакет для очистки и трансформации HTML-кода. Он позволяет легко манипулировать HTML-структурами, удалять нежелательные элементы и атрибуты, нормализовывать содержимое и выполнять другие операции по очистке.

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

## Основные функции

### Удаление элементов

Удалите указанные HTML-теги с помощью метода `drop()`:

```php
$result = $cleaner
    ->drop('script', 'style', 'meta')
    ->clean($html);
```

### Извлечение содержимого (Unwrap)

Извлеките содержимое из указанных элементов, удалив сами теги:

```php
$result = $cleaner
    ->unwrap('p', 'div')
    ->clean($html);
```

### Замена элементов

Замените одни элементы на другие с сохранением атрибутов:

```php
use MB\Support\HtmlCleaner\Selector\SelectorFacade;
use MB\Support\HtmlCleaner\Transformer\Replace;

$result = $cleaner
    ->replace(
        SelectorFacade::tag('span'),
        Replace::tag('b')->copyClassList()
    )
    ->clean($html);
```

### Удаление атрибутов

Удалите указанные атрибуты из всех элементов:

```php
$result = $cleaner
    ->stripAttributes('style', 'class')
    ->clean($html);
```

### Разрешение определенных стилей

Разрешите использование определенных CSS-свойств в атрибуте style:

```php
$result = $cleaner
    ->allowStyles('color', 'background-color', 'font-size')
    ->clean($html);
```

### Нормализация пробелов

Нормализуйте пробелы и переносы строк в текстовом содержимом:

```php
$result = $cleaner
    ->normalizeWhitespace()
    ->clean($html);
```

## Примеры использования

### Очистка HTML-документа

```php
$html = '<!DOCTYPE html><html><head><meta charset="utf-8"><style>body{margin:0;}</style></head><body><p>Текст <b>примера</b></p></body></html>';

$result = $cleaner
    ->drop(['style', 'meta', 'head'])
    ->normalizeWhitespace()
    ->outputFragment()
    ->clean($html);

// Результат: <p>Текст <b>примера</b></p>
```

### Преобразование форматирования

```php
$html = '<span class="bold" style="color:red">Текст</span>';

$result = $cleaner
    ->replace(
        SelectorFacade::tag('span'),
        Replace::tag('b')->copyClassList()
    )
    ->stripAttributes('style')
    ->clean($html);

// Результат: <b class="bold">Текст</b>
```
