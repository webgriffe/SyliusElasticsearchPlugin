<?php

declare(strict_types=1);

namespace Webgriffe\SyliusElasticsearchPlugin\Form\Extension;

use Sylius\Bundle\ProductBundle\Form\Type\ProductAttributeType;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\FormBuilderInterface;

final class ProductAttributeTypeExtension extends AbstractTypeExtension
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('filterable', CheckboxType::class, [
                'label' => 'webgriffe_sylius_elasticsearch_plugin.form.filterable.label',
                'required' => true,
            ])
        ;
    }

    public static function getExtendedTypes(): array
    {
        return [
            ProductAttributeType::class,
        ];
    }
}
