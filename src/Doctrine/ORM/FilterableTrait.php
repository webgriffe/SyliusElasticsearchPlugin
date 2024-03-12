<?php

declare(strict_types=1);

namespace Webgriffe\SyliusElasticsearchPlugin\Doctrine\ORM;

use Doctrine\ORM\Mapping as ORM;

trait FilterableTrait
{
    /** @ORM\Column(name="filterable", type="boolean", nullable=false, options={"default"=false}) */
    #[ORM\Column(name: 'filterable', type: 'boolean', nullable: false, options: ['default' => false])]
    protected bool $filterable = false;

    public function isFilterable(): bool
    {
        return $this->filterable;
    }

    public function setFilterable(bool $filterable): void
    {
        $this->filterable = $filterable;
    }
}
