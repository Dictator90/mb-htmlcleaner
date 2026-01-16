<?php

use MB\Support\HtmlCleaner\HtmlCleaner;
use MB\Support\HtmlCleaner\Selector\SelectorFacade;
use MB\Support\HtmlCleaner\Transformer\Replace;
use MB\Support\HtmlCleaner\Transformer\TransformerFacade;
use PHPUnit\Framework\TestCase;

final class HtmlCleanerTest extends TestCase
{
    public function testReplaceTag(): void
    {
        $html = '<span class="bold" style="color:red">Hello</span><p>World</p>';

        $result =
            HtmlCleaner::make()
                ->replaceTag(['span'], 'div')
                ->clean($html)
        ;

        $this->assertSame('<div class="bold" style="color:red">Hello</div><p>World</p>', trim($result));
    }

    public function testUnwrap(): void
    {
        $html = '<span class="bold" style="color:red">Hello</span><p><span>World</span></p>';

        $result =
            HtmlCleaner::make()
                ->unwrap('p')
                ->clean($html)
        ;

        $this->assertSame('<span class="bold" style="color:red">Hello</span><span>World</span>', trim($result));
    }

    public function testWrap(): void
    {
        $html = '<span class="bold" style="color:red">Hello</span><p>World</p>';

        $result =
            HtmlCleaner::make()
                ->wrap(['span'], 'p')
                ->clean($html)
        ;

        $this->assertSame('<p><span class="bold" style="color:red">Hello</span></p><p>World</p>', trim($result));
    }

    public function testNormalizeWhitespace(): void
    {
        $html = '<p>Hello     world</p>';

        $result =
            HtmlCleaner::make()
                ->normalizeWhitespace()
                ->clean($html)
        ;

        $this->assertSame('<p>Hello world</p>', trim($result));
    }

    public function testStyleSelector()
    {
        $html = '<span style="font-weight:bold">Hello</span><p>World</p>';

        $result =
            HtmlCleaner::make()
                ->transform(
                    SelectorFacade::style('font-weight', 'bold'),
                    Replace::tag('b'),
                    10
                )
                ->stripStyles('font-weight')
                ->clean($html)
        ;

        $this->assertSame('<b>Hello</b><p>World</p>', trim($result));
    }

    public function testChangeAttrDocument()
    {
        $html = '<!DOCTYPE html><html lang="en"><body><span style="font-weight:bold">Hello</span><p>World</p></body></html>';

        $result =
            HtmlCleaner::make()
                ->transform(
                    SelectorFacade::style('font-weight', 'bold'),
                    Replace::tag('b'),
                    10
                )
                ->changeAttr(['html'], 'lang', 'ru')
                ->stripStyles('font-weight')
                ->outputDocument()
                ->clean($html)
        ;

        $this->assertSame('<!DOCTYPE html><html lang="ru"><body><b>Hello</b><p>World</p></body></html>', trim($result));
    }

    public function testClosureTransformer(): void
    {
        $html = '<span style="font-weight:bold">Hello</span><p>World</p>';
        $result =
            HtmlCleaner::make()
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
                ->clean($html)
        ;

        $this->assertSame('<b class="changed">Hello</b><p>World</p>', trim($result));
    }

    public function testConditionSelectors(): void
    {
        $html = '<!DOCTYPE html><html dir="ltr"><head><meta http-equiv="Content-Type" content="text/html; charset=utf-8" /><meta http-equiv="X-UA-Compatible" content="IE=Edge" /><meta name="format-detection" content="telephone=no" /><style type="text/css">body{margin:0;padding:8px;}p{line-height:1.15;margin:0;white-space:pre-wrap;}ol,ul{margin-top:0;margin-bottom:0;}img{border:none;}li>p{display:inline;}</style></head><body class="bodyClass"><p><span style="background-color: #ffffff;color: #888888;font-family: Open Sans;font-size: 10pt;font-style: normal;font-weight: normal;line-height: 1.38;">Цвет</span></p><p style="background-color: #ffffff;color: #333333;font-family: Open Sans;font-size: 10pt;font-style: normal;font-weight: normal;line-height: 1.38;"></body></html>';

        $result =
            HtmlCleaner::make()
                ->transformOr(
                    [
                        SelectorFacade::and(
                            SelectorFacade::tag('span'),
                            SelectorFacade::style('font-size', '10pt')
                        ),
                        SelectorFacade::and(
                            SelectorFacade::tag('p'),
                            SelectorFacade::style('color', '#333333')
                        )
                    ],
                    Replace::tag('div')->addClass('replaced')
                )
                ->drop('style', 'meta', 'head')
                ->clean($html)
        ;

        $this->assertSame('<p><div class="replaced">Цвет</div></p><div class="replaced"></div>', trim($result));
    }

    public function testChildSelector(): void
    {
        $html = '<span class="bold" style="color:red">Hello</span><p><span>World</span></p>';

        $result =
            HtmlCleaner::make()
                ->changeAttr(['p > span'], 'class', 'replaced')
                ->clean($html)
        ;

        $this->assertSame('<span class="bold" style="color:red">Hello</span><p><span class="replaced">World</span></p>', trim($result));
    }

    public function testHasSelector(): void
    {
        $html = '<div class="bold" style="color:red"><span class="child">Hello</span></div><p><span>World</span></p>';

        $result =
            HtmlCleaner::make()
                ->unwrap('div.bold:has(.child)')
                ->clean($html)
        ;

        $this->assertSame('<span class="child">Hello</span><p><span>World</span></p>', trim($result));
    }

    public function testHasTextSelector(): void
    {
        $html = '<div><span>Hello</span></div><p><span>World</span></p>';

        $result =
            HtmlCleaner::make()
                ->changeAttr(['span:has-text("Hello")'], 'class', 'hello')
                ->clean($html)
        ;

        $this->assertSame(
            '<div><span class="hello">Hello</span></div><p><span>World</span></p>',
            trim($result)
        );
    }

    public function testSmartSelector(): void
    {
        $html = '<div class="bold" style="color:red" data-id="5"><span>Hello</span></div><p><span>World</span></p><noindex>Hello</noindex>';

        $result =
            HtmlCleaner::make()
                ->changeAttr(['div[data-id].bold > span'], 'class', 'found')
                ->unwrap('p > span')
                ->onlyText('noindex')
                ->clean($html)
        ;

        $this->assertSame(
            '<div class="bold" style="color:red" data-id="5"><span class="found">Hello</span></div><p>World</p>Hello',
            trim($result)
        );
    }

    public function testOrSelector(): void
    {
        // 'strong, span.bold',
        $html = '<strong>Hello</strong><span class="bold">World</span>';

        $result =
            HtmlCleaner::make()
                ->replaceTag(['strong', 'span.bold'], 'b', false)
                ->clean($html)
        ;

        $this->assertSame(
            '<b>Hello</b><b>World</b>',
            trim($result)
        );
    }

    public function testBatchTransformer(): void
    {
        $html = '<div class="bold" style="color:red" data-id="5"><span>Hello</span></div>';

        $result =
            HtmlCleaner::make()
                ->transform(
                    'div.bold',
                    TransformerFacade::batch(
                        TransformerFacade::changeAttr('data-id', '10'),
                        TransformerFacade::wrap('article'),
                        TransformerFacade::dropAttrs('style')
                    )
                )
                ->clean($html)
        ;

        $this->assertSame(
            '<article><div class="bold" data-id="10"><span>Hello</span></div></article>',
            trim($result)
        );
    }

    public function testDocHtmlFragmentTransform(): void
    {
        $html = '<!DOCTYPE html><html dir="ltr"><head><meta http-equiv="Content-Type" content="text/html; charset=utf-8" /><meta http-equiv="X-UA-Compatible" content="IE=Edge" /><meta name="format-detection" content="telephone=no" /><style type="text/css">body{margin:0;padding:8px;}p{line-height:1.15;margin:0;white-space:pre-wrap;}ol,ul{margin-top:0;margin-bottom:0;}img{border:none;}li>p{display:inline;}</style></head><body class="bodyClass"><p><span style="background-color: #ffffff;color: #888888;font-family: Open Sans;font-size: 10pt;font-style: normal;font-weight: normal;line-height: 1.38;">Цвет</span></p><p style="background-color: #ffffff;color: #333333;font-family: Open Sans;font-size: 10pt;font-style: normal;font-weight: normal;line-height: 1.38;"></body></html>';

        $result =
            HtmlCleaner::make()
                ->drop('style', 'meta', 'head')
                ->allowStyles('background-color')
                ->normalizeWhitespace()
                ->outputFragment()
                ->clean($html)
        ;

        $this->assertSame('<p><span style="background-color:#ffffff">Цвет</span></p><p style="background-color:#ffffff"></p>', trim($result));
    }

    public function testDocHtmlQuerySelect(): void
    {
        $html = '<!DOCTYPE html><html dir="ltr"><head><meta http-equiv="Content-Type" content="text/html; charset=utf-8" /><meta http-equiv="X-UA-Compatible" content="IE=Edge" /><meta name="format-detection" content="telephone=no" /><style type="text/css">body{margin:0;padding:8px;}p{line-height:1.15;margin:0;white-space:pre-wrap;}ol,ul{margin-top:0;margin-bottom:0;}img{border:none;}li>p{display:inline;}</style></head><body class="bodyClass"><p><span style="background-color: #ffffff;color: #888888;font-family: Open Sans;font-size: 10pt;font-style: normal;font-weight: normal;line-height: 1.38;">Цвет</span></p><p style="background-color: #ffffff;color: #333333;font-family: Open Sans;font-size: 10pt;font-style: normal;font-weight: normal;line-height: 1.38;"></body></html>';

        $result =
            HtmlCleaner::make()
                ->drop('meta[http-equiv="Content-Type"]', 'style')
                ->stripStyles('font-family', 'color', 'font-style', 'font-size', 'font-weight', 'line-height')
                ->normalizeWhitespace()
                ->outputDocument()
                ->clean($html)
        ;

        $this->assertSame(
            '<!DOCTYPE html><html dir="ltr"><head><meta http-equiv="X-UA-Compatible" content="IE=Edge"><meta name="format-detection" content="telephone=no"></head><body class="bodyClass"><p><span style="background-color:#ffffff">Цвет</span></p><p style="background-color:#ffffff"></p></body></html>',
            trim($result)
        );
    }

    public function testDocHtmlDocumentTransform(): void
    {
        $html = '<!DOCTYPE html><html dir="ltr"><head><meta http-equiv="Content-Type" content="text/html; charset=utf-8" /><meta http-equiv="X-UA-Compatible" content="IE=Edge" /><meta name="format-detection" content="telephone=no" /><style type="text/css">body{margin:0;padding:8px;}p{line-height:1.15;margin:0;white-space:pre-wrap;}ol,ul{margin-top:0;margin-bottom:0;}img{border:none;}li>p{display:inline;}</style></head><body class="bodyClass"><p><span style="background-color: #ffffff;color: #888888;font-family: Open Sans;font-size: 10pt;font-style: normal;font-weight: normal;line-height: 1.38;">Цвет</span></p><p style="background-color: #ffffff;color: #333333;font-family: Open Sans;font-size: 10pt;font-style: normal;font-weight: normal;line-height: 1.38;"></body></html>';

        $result =
            HtmlCleaner::make()
                ->drop('style', 'meta', 'head')
                ->allowStyles('background-color')
                ->normalizeWhitespace()
                ->outputDocument()
                ->clean($html)
        ;

        $this->assertSame(
            '<!DOCTYPE html><html dir="ltr"><body class="bodyClass"><p><span style="background-color:#ffffff">Цвет</span></p><p style="background-color:#ffffff"></p></body></html>',
            trim($result)
        );
    }

    public function testStripEmptyTag(): void
    {
        $html = file_get_contents(__DIR__ . '/test_files/doc_html');
        $result =
            HtmlCleaner::make()
                ->stripComments()
                ->normalizeWhitespace()
                ->stripEmptyTag('p')
                ->outputDocument()
                ->clean($html)
        ;

        $this->assertSame(
            file_get_contents(__DIR__ . '/test_files/doc-result_html'),
            trim($result)
        );
    }

    public function testLargeDoc(): void
    {
        $html = file_get_contents(__DIR__ . '/test_files/large_doc_html');
        $result =
            HtmlCleaner::make()
                ->stripComments()
                ->normalizeWhitespace()
                ->stripEmptyTag('p')
                ->changeAttr(['a[href^=/"]'], 'target', '_blank')
                ->drop('iframe', 'script')
                ->wrap(['a[rel="nofollow"]'], 'noindex')
                ->outputDocument()
                ->clean($html)
        ;

        $this->assertSame(
            file_get_contents(__DIR__ . '/test_files/large_doc-result_html'),
            trim($result)
        );
    }
}