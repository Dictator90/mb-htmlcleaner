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
        $this->attrs['class'][] = $class;
        return $this;
    }

    public function addStyle(string $style, string $value): self
    {
        $this->attrs['style'][$style] = $value;
        return $this;
    }

    public function addAttr(string $attr, string $value): self
    {
        $this->attrs[$attr] = $value;
        return $this;
    }

    public function copyStyles(): self
    {
        $this->copyStyles = true;
        return $this;
    }

    public function copyClassList(): self
    {
        $this->copyClassList = true;
        return $this;
    }

    public function copyAttrs(): self
    {
        $this->copyAttrs = true;
        return $this;
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
