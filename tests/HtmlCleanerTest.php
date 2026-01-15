<?php

use MB\Support\HtmlCleaner\HtmlCleaner;
use MB\Support\HtmlCleaner\Selector\AndSelector;
use MB\Support\HtmlCleaner\Selector\OrSelector;
use MB\Support\HtmlCleaner\Selector\SelectorFacade;
use MB\Support\HtmlCleaner\Selector\StyleSelector;
use MB\Support\HtmlCleaner\Transformer\ChangeAttribute;
use MB\Support\HtmlCleaner\Transformer\Replace;
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
        $html = '<span class="bold" style="color:red">Hello</span><p>World</p>';

        $result =
            HtmlCleaner::make()
                ->unwrap('p')
                ->clean($html)
        ;

        $this->assertSame('<span class="bold" style="color:red">Hello</span>World', trim($result));
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

        $cleaner = new HtmlCleaner();

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
                        AndSelector::make(
                            SelectorFacade::tag('span'),
                            SelectorFacade::style('font-size', '10pt')
                        ),
                        AndSelector::make(
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
}