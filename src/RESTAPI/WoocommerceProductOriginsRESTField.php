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
                'update_callback' => [$this, 'update_origins'],
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

        public function update_origins(array $origins, \WC_Product $product): void
        {
            $product_id = $product->get_id();
            $old_term_ids = \wp_get_post_terms($product_id, OriginsTaxonomy::TAXONOMY_SLUG, ['fields' => 'ids']);

            if ($old_term_ids && !\is_wp_error($old_term_ids)) {
                \wp_remove_object_terms($product_id, $old_term_ids, OriginsTaxonomy::TAXONOMY_SLUG);
            }

            if ($origins) {
                $new_term_ids = \array_map(static function (array $origin): int {
                    return (int) $origin['id'];
                }, $origins);

                \wp_set_post_terms($product_id, $new_term_ids, OriginsTaxonomy::TAXONOMY_SLUG, true);
            }
        }
    }
}
