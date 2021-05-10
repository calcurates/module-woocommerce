<?php

declare(strict_types=1);

namespace Calcurates\Origins;

// Stop direct HTTP access.
if (!\defined('ABSPATH')) {
    exit;
}

if (!\class_exists('OriginUtils')) {
    class OriginUtils
    {
        /**
         * Extract Origin code from product.
         */
        public function get_origin_code_from_product(int $product_id): ?string {
            $origin_term_id = $this->get_origin_term_id_from_product($product_id);

            if($origin_term_id){
                return get_term_meta($origin_term_id, 'origin_code', true);
            }

            return null;
        }

        /**
         * Extract Origin term id from product.
         */
        public function get_origin_term_id_from_product(int $product_id): ?int {
            $origin_terms = wp_get_post_terms($product_id, OriginsTaxonomy::TAXONOMY_SLUG, [ 'fields'=> 'ids']);
            
            if(is_array($origin_terms) && !empty($origin_terms)){
                
                return reset($origin_terms);
            }

            return null;
        }

        /**
         * Get all Origins codes array
         */
        public function get_origins_codes(): array {

            $codes = [];

            $origins_term_ids = get_terms( [
                'taxonomy' => OriginsTaxonomy::TAXONOMY_SLUG,
                'hide_empty' => false,
                'fields'=> 'ids'
            ] );

            if($origins_term_ids){
                foreach($origins_term_ids as $term_id){
                    $code = get_term_meta($term_id, 'origin_code', true);

                    if($code){
                        $codes[] = $code;
                    }
                }
            }
            
            return $codes;
        }
    }
}
