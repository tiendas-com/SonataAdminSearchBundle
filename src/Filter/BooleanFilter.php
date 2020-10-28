<?php

declare(strict_types=1);

/*
 * This file is part of the Sonata Project package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\AdminSearchBundle\Filter;

use Elastica\QueryBuilder;
use Sonata\AdminBundle\Datagrid\ProxyQueryInterface;
use Sonata\AdminBundle\Form\Type\Filter\DefaultType;
use Sonata\Form\Type\BooleanType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;

class BooleanFilter extends Filter
{
    /**
     * {@inheritdoc}
     */
    public function filter(ProxyQueryInterface $query, $alias, $field, $data): void
    {
        if (!$data || !\is_array($data) || !\array_key_exists('type', $data) || !\array_key_exists('value', $data)) {
            return;
        }
        
        if (\is_array($data['value'])) {
            $values = [];
            foreach ($data['value'] as $v) {
                if (!\in_array($v, [BooleanType::TYPE_NO, BooleanType::TYPE_YES], true)) {
                    continue;
                }

                $values[] = (BooleanType::TYPE_YES === $v);
            }

            if (0 === \count($values)) {
                return;
            }

            $queryBuilder = new QueryBuilder();
            $innerQuery = $queryBuilder
                ->query()
                ->terms($field, $values);

            $query->addMust($innerQuery);
        } else {
            if (!\in_array($data['value'], [BooleanType::TYPE_NO, BooleanType::TYPE_YES], true)) {
                return;
            }

            $queryBuilder = new QueryBuilder();
            $innerQuery = $queryBuilder
                ->query()
                ->term([
                    $field => (BooleanType::TYPE_YES === $data['value']),
                ]);

            $query->addMust($innerQuery);
        }
        
        $this->active = true;
    }

    /**
     * {@inheritdoc}
     */
    public function getDefaultOptions()
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function getRenderSettings()
    {
        return [DefaultType::class, [
            'field_type' => $this->getFieldType(),
            'field_options' => $this->getFieldOptions(),
            'operator_type' => HiddenType::class,
            'operator_options' => [],
            'label' => $this->getLabel(),
        ]];
    }
}
