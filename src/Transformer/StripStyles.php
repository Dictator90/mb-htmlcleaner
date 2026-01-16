<?php
namespace MB\Support\HtmlCleaner\Transformer;

use DOMNode;
use MB\Support\HtmlCleaner\Contracts\TransformerInterface;

class StripStyles implements TransformerInterface
{
    public function __construct(
        private array $styles
    ) {}

    public function apply(DOMNode $node): bool
    {
        if ($node->nodeType !== XML_ELEMENT_NODE) {
            return true;
        }

        $nodeAttr = $node->getAttribute('style');
        $styles = explode(';', $nodeAttr);
        $result = [];

        foreach ($styles as $style) {
            [$name, $value] = array_map('trim', explode(':', $style, 2) + [null, null]);

            if ($name && !in_array($name, $this->styles, true)) {
                $result[] = "$name:$value";
            }
        }

        if ($result) {
            $node->setAttribute('style', implode(';', $result));
        } else {
            $node->removeAttribute('style');
        }

        return true;
    }
}
