<?php

declare(strict_types=1);

namespace LRuozzi9\SyliusElasticsearchPlugin\Parser;

use LRuozzi9\SyliusElasticsearchPlugin\Model\ResponseInterface;

interface DocumentParserInterface
{
    /**
     * @param array{_index: string, _id: string, score: float, _source: array} $document
     */
    public function parse(array $document): ResponseInterface;
}
