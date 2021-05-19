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
        /**
         * @var self
         */
        private static $instance;

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
        public function get_origin_code_from_product(int $product_id): ?string
        {
            $origin_term_id = $this->get_origin_term_id_from_product($product_id);

            if ($origin_term_id) {
                return \get_term_meta($origin_term_id, 'origin_code', true);
            }

            return null;
        }

        /**
         * Extract Origin term id from product.
         */
        public function get_origin_term_id_from_product(int $product_id): ?int
        {
            $origin_terms = \wp_get_post_terms($product_id, OriginsTaxonomy::TAXONOMY_SLUG, ['fields' => 'ids']);

            if ($origin_terms && \is_array($origin_terms)) {
                return \reset($origin_terms);
            }

            return null;
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
