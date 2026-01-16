<?php
namespace MB\Support\HtmlCleaner\Transformer;

use DOMNode;
use MB\Support\HtmlCleaner\Contracts\TransformerInterface;

class BatchTransformer implements TransformerInterface
{
    /**
     * @param TransformerInterface[] $transformers
     */
    public function __construct(protected array $transformers = [])
    {}

    public function apply(DOMNode $node): bool
    {
        foreach ($this->transformers as $transformer) {
            if (!$transformer->apply($node)) {
                return false;
            }
        }

        return true;
    }
}