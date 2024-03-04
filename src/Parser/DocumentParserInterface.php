<?php

declare(strict_types=1);

namespace Webgriffe\SyliusElasticsearchPlugin\Parser;

use Webgriffe\SyliusElasticsearchPlugin\Client\ClientInterface;
use Webgriffe\SyliusElasticsearchPlugin\Model\ResponseInterface;

/**
 * @psalm-import-type ESHit from ClientInterface
 */
interface DocumentParserInterface
{
    /**
     * @param ESHit $document
     */
    public function parse(array $document): ResponseInterface;
}
