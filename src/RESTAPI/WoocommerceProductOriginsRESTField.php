<?php

declare(strict_types=1);

namespace Calcurates\RESTAPI;

use Calcurates\Origins\OriginsTaxonomy;

// Stop direct HTTP access.
if (!\defined('ABSPATH')) {
    exit;
}

if (!\class_exists(WoocommerceProductOriginsRESTField::class)) {
    class WoocommerceProductOriginsRESTField
    {
        public function register(): void
        {
            \register_rest_field('product', 'origins', [
                'get_callback' => [$this, 'get_origins'],
                'schema' => [
                    'description' => 'Calcurates origin(s) assigned to the product',
                    'type' => 'array',
                    'items' => [
                        'type' => 'object',
                        'properties' => [
                            'id' => ['type' => 'integer'],
                            'name' => ['type' => 'string'],
                            'code' => ['type' => 'string'],
                        ],
                    ],
                ],
            ]);
        }

        public function get_origins(array $product): array
        {
            $terms = \wp_get_post_terms($product['id'], OriginsTaxonomy::TAXONOMY_SLUG, ['fields' => 'all']);
            if (!$terms || \is_wp_error($terms)) {
                return [];
            }

            return \array_map(static function (\WP_Term $term): array {
                return [
                    'id' => $term->term_id,
                    'name' => $term->name,
                    'code' => \get_term_meta($term->term_id, 'origin_code', true),
                ];
            }, $terms);
        }
    }
}
