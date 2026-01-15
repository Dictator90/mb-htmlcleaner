<?php
namespace MB\Support\HtmlCleaner\Engine;

use DOMDocument;
use DOMElement;
use DOMNode;
use MB\Support\HtmlCleaner\Contracts\{
    EngineInterface,
    RuleInterface,
    OutputMode
};

final class DOMDocumentEngine implements EngineInterface
{
    private OutputMode $outputMode = OutputMode::FRAGMENT;

    /** @var array<int,RuleInterface> */
    private array $rules = [];

    public function __construct(protected $encoding = 'utf-8')
    {}

    public function rule(RuleInterface $rule): self
    {
        $this->rules[] = $rule;
        usort($this->rules, fn($a,$b) => $b->priority() <=> $a->priority());
        return $this;
    }

    public function clean(string $html): string
    {
        $dom = new DOMDocument('1.0', strtoupper($this->encoding));
        $dom->substituteEntities = false;

        libxml_use_internal_errors(true);
        $dom->loadHTML(
            mb_convert_encoding($html, 'HTML-ENTITIES', $this->encoding),
            LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD
        );
        libxml_clear_errors();

        $this->walk($dom);

        return match ($this->outputMode) {
            OutputMode::FRAGMENT => $this->renderFragment($dom),
            OutputMode::DOCUMENT => $this->renderDocument($dom),
        };
    }

    public function output(OutputMode $mode): self
    {
        $this->outputMode = $mode;
        return $this;
    }

    private function walk(DOMNode $node): void
    {
        foreach ($this->rules as $rule) {
            if ($rule->supports($node)) {
                if ($rule->apply($node)) {
                    break;
                }
            }
        }

        foreach (iterator_to_array($node->childNodes) as $child) {
            $this->walk($child);
        }
    }

    private function renderFragment(DOMDocument $dom): string
    {
        $body = $dom->getElementsByTagName('body')->item(0);

        if (!$body) {
            return $this->innerHtml($dom);
        }

        $html = '';

        foreach ($body->childNodes as $child) {
            $html .= $dom->saveHTML($child);
        }

        return $this->normalize($html);
    }

    private function renderDocument(DOMDocument $dom): string
    {
        if (!$dom->doctype) {
            $dom->encoding = $this->encoding;
            $doctype = $dom->implementation->createDocumentType('html');
            $dom->insertBefore($doctype, $dom->firstChild);
        }
        $html = $dom->saveHTML();
        $html = html_entity_decode($html, ENT_QUOTES | ENT_HTML5, $this->encoding);

        return $this->normalize($html);
    }

    private function normalize(string $html): string
    {
        return str_replace(["\n", "\r"], "", $html);
    }

    private function innerHtml(DOMDocument $dom): string
    {
        $html = '';
        foreach ($dom->childNodes as $child) {
            $html .= $dom->saveHTML($child);
        }
        return $html;
    }
}
