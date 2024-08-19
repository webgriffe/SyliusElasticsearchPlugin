<?php

declare(strict_types=1);

namespace Webgriffe\SyliusElasticsearchPlugin\Validator;

use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Symfony\Component\HttpFoundation\Request;

final class RequestValidator implements RequestValidatorInterface
{
    public function validate(Request $request): void
    {
        $this->validateSorting($request);
        $this->validatePage($request);
        $this->validateLimit($request);
        $this->validateFilters($request);
    }

    private function validateSorting(Request $request): void
    {
        /** @var array<string, string> $sorting */
        $sorting = $request->query->all('sorting');
        if (count($sorting) === 0) {
            return;
        }

        // the "_" in $_field is necessary or psalm will complain..
        foreach ($sorting as $_field => $direction) {
            if (!in_array(strtolower($direction), ['asc', 'desc'], true)) {
                throw new BadRequestException(
                    sprintf(
                        'Unexpected value for parameter "sorting": expecting "asc" or "desc", got "%s".',
                        $direction,
                    ),
                );
            }
        }
    }

    private function validatePage(Request $request): void
    {
        $page = $request->query->get('page');
        if ($page === null) {
            return;
        }

        if (!is_numeric($page)) {
            throw new BadRequestException(
                sprintf('Unexpected value for parameter "page": expecting "integer", got "%s".', get_debug_type($page)),
            );
        }
    }

    private function validateLimit(Request $request): void
    {
        $limit = $request->query->get('limit');
        if ($limit === null) {
            return;
        }

        if (!is_numeric($limit)) {
            throw new BadRequestException(
                sprintf(
                    'Unexpected value for parameter "limit": expecting "integer", got "%s".',
                    get_debug_type($limit),
                ),
            );
        }
    }

    private function validateFilters(Request $request): void
    {
        // this will throw if the filters parameter is not an array
        /** @var array<string, array<array-key, array{code?: string, value?: string}>> $allFiltersByType */
        $allFiltersByType = $request->query->all('filters');
        foreach ($allFiltersByType as $filtersByType) {
            foreach ($filtersByType as $filter) {
                if (!array_key_exists('code', $filter) ||
                    !array_key_exists('value', $filter) ||
                    $filter['code'] === '' ||
                    $filter['value'] === ''
                ) {
                    throw new BadRequestException('Invalid filter format');
                }
            }
        }
    }
}
