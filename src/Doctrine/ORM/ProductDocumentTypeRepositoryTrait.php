<?php

declare(strict_types=1);

namespace Webgriffe\SyliusElasticsearchPlugin\Doctrine\ORM;

use Sylius\Component\Core\Model\ChannelInterface;

trait ProductDocumentTypeRepositoryTrait
{
    public function findDocumentsToIndex(ChannelInterface $channel): array
    {
        return $this->createQueryBuilder('p')
            ->where('p.enabled = :enabled')
            ->andWhere(':channel MEMBER OF p.channels')
            ->addOrderBy('p.createdAt', 'ASC')
            ->setParameter('channel', $channel)
            ->setParameter('enabled', true)
            ->getQuery()
            ->getResult()
        ;
    }
}
