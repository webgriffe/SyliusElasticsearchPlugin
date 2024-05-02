<?php

declare(strict_types=1);

namespace Webgriffe\SyliusElasticsearchPlugin\Client\Enum;

enum Action: string
{
    case CREATE = 'create';
    case DELETE = 'delete';
    case INDEX = 'index';
    case UPDATE = 'update';
}
