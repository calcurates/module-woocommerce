<?php

declare(strict_types=1);

namespace Calcurates\Origins;

// Stop direct HTTP access.
if (!\defined('ABSPATH')) {
    exit;
}

if (!\class_exists(OriginUtils::class)) {
    class OriginUtils
    {
        private static ?self $instance = null;

        private function __construct()
        {
        }

        public static function getInstance(): self
        {
            if (null === self::$instance) {
                self::$instance = new self();
            }

            return self::$instance;
        }

        /**
         * Extract Origin code from product.
         */
        public function get_origin_codes_from_product(int $product_id): array
        {
            $origin_term_ids = $this->get_origin_term_ids_from_product($product_id);

            if (!$origin_term_ids) {
                return [];
            }

            return \array_map(static function (int $id): string {
                return \get_term_meta($id, 'origin_code', true);
            }, $origin_term_ids);
        }

        /**
         * Extract Origin term id from product.
         */
        public function get_origin_term_ids_from_product(int $product_id): array
        {
            $origin_terms = \wp_get_post_terms($product_id, OriginsTaxonomy::TAXONOMY_SLUG, ['fields' => 'ids']);

            return $origin_terms;
        }

        /**
         * Get all Origins with codes and names.
         */
        public function get_origins_for_rest(): array
        {
            $origins = [];

            $origins_terms = \get_terms([
                'taxonomy' => OriginsTaxonomy::TAXONOMY_SLUG,
                'hide_empty' => false,
            ]);

            if ($origins_terms && \is_array($origins_terms)) {
                foreach ($origins_terms as $term) {
                    $code = \get_term_meta($term->term_id, 'origin_code', true);

                    if ($code) {
                        $origins[] = [
                            'name' => $term->name,
                            'code' => $code,
                        ];
                    }
                }
            }

            return $origins;
        }

        /**
         * Check if Code exists.
         */
        public function is_code_exists(string $code): bool
        {
            $origins_term_ids = \get_terms([
                'taxonomy' => OriginsTaxonomy::TAXONOMY_SLUG,
                'hide_empty' => false,
                'fields' => 'ids',
                'meta_query' => [
                    [
                        'key' => 'origin_code',
                        'value' => \sanitize_text_field($code),
                        'compare' => '=',
                    ],
                ],
            ]);

            return $origins_term_ids && \is_array($origins_term_ids);
        }
    }
}
