<?php
namespace MB\Support\HtmlCleaner\Transformer;

use DOMNode;
use MB\Support\HtmlCleaner\Contracts\TransformerInterface;

final class NullTransformer implements TransformerInterface
{
    public function apply(DOMNode $node): bool
    {
        return true;
    }
}
