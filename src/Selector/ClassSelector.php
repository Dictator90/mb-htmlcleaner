<?php
namespace MB\Support\HtmlCleaner\Selector;

use DOMNode;
use DOMElement;
use MB\Support\HtmlCleaner\Contracts\SelectorInterface;

class ClassSelector implements SelectorInterface
{
    /**
     * @param array $classes
     */
    public function __construct(private array $classes) {}

    public function matches(DOMNode $node): bool
    {
        if (!$node instanceof DOMElement || !$node->hasAttribute('class')) {
            return false;
        }

        $classList = preg_split('/\s+/', $node->getAttribute('class'));

        foreach ($this->classes as $class) {
            if (!in_array($class, $classList, true)) {
                return false;
            }
        }

        return true;
    }
}