<?php

declare(strict_types=1);

namespace Webgriffe\SyliusElasticsearchPlugin\Parser;

use Webgriffe\SyliusElasticsearchPlugin\Model\ResponseInterface;

interface DocumentParserInterface
{
    /**
     * @param array{_index: string, _id: string, score: float, _source: array} $document
     */
    public function parse(array $document): ResponseInterface;
}
