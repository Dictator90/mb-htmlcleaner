<?php
namespace MB\Support\HtmlCleaner\Transformer;

use DOMElement;
use DOMNode;
use MB\Support\HtmlCleaner\Contracts\TransformerInterface;

final class Replace implements TransformerInterface
{
    private array $attrs = [];
    private bool $copyStyles = false;
    private bool $copyClassList = false;
    private bool $copyAttrs = false;

    public function __construct(private string|null $tag) {}

    public static function tag(string|null $tag): self
    {
        return new self($tag);
    }

    public function addClass(string $class): self
    {
        $c = clone $this;
        $c->attrs['class'][] = $class;
        return $c;
    }

    public function addStyle(string $style, string $value): self
    {
        $c = clone $this;
        $c->attrs['style'][$style] = $value;
        return $c;
    }

    public function addAttr(string $attr, string $value): self
    {
        $c = clone $this;
        $c->attrs[$attr] = $value;
        return $c;
    }

    public function copyStyles(): self
    {
        $c = clone $this;
        $c->copyStyles = true;
        return $c;
    }

    public function copyClassList(): self
    {
        $c = clone $this;
        $c->copyClassList = true;
        return $c;
    }

    public function copyAttrs(): self
    {
        $c = clone $this;
        $c->copyAttrs = true;
        return $c;
    }

    public function apply(DOMNode $node): bool
    {
        if ($node->nodeType !== XML_ELEMENT_NODE) {
            return true;
        }

        $doc = $node->ownerDocument;
        $parent = $node->parentNode;

        if (!$doc || !$parent) return false;

        if (is_null($this->tag)) {
            $parent->removeChild($node);
            return false;
        }

        $new = $doc->createElement($this->tag);

        if ($this->copyClassList && $node->hasAttribute('class')) {
            $new->setAttribute('class', $node->getAttribute('class'));
        }

        if ($this->copyStyles && $node->hasAttribute('style')) {
            $new->setAttribute('style', $node->getAttribute('style'));
        }

        if ($this->copyAttrs) {
            foreach ($node->attributes as $attrName => $attrNode) {
                if ($attrName === 'class' || $attrName === 'style') continue;
                $new->setAttribute($attrName, $attrNode->nodeValue);
            }
        }

        if (!empty($this->attrs['style'])) {
            $styles = [];
            foreach ($this->attrs['style'] as $k => $style) {
                if (is_int($k)) {
                    $styles[] = $style;
                } else {
                    $styles[] = $k . ':' . $style;
                }
            }
            $new->setAttribute('class', implode(';', $styles));
        }

        if (!empty($this->attrs['class'])) {
            $new->setAttribute('class', implode(' ', $this->attrs['class']));
        }

        foreach ($this->attrs as $attr => $value) {
            if ($attr === 'class' || $attr === 'style') continue;
            $new->setAttribute($attr, $value);
        }

        while ($node->firstChild) {
            $new->appendChild($node->firstChild);
        }

        $parent->replaceChild($new, $node);
        return true;
    }
}
