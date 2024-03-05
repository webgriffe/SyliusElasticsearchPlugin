<?php

declare(strict_types=1);

namespace Webgriffe\SyliusElasticsearchPlugin\Event\ProductDocumentType;

use Symfony\Contracts\EventDispatcher\Event;

final class ProductDocumentTypeSettingsEvent extends Event
{
    /**
     * @param array<string, array> $settings
     */
    public function __construct(private array $settings)
    {
    }

    /**
     * @return array<string, array>
     */
    public function getSettings(): array
    {
        return $this->settings;
    }

    /**
     * @param array<string, array> $settings
     */
    public function setSettings(array $settings): void
    {
        $this->settings = $settings;
    }
}
