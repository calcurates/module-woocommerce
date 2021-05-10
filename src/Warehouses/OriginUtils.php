<?php

declare(strict_types=1);

namespace Calcurates\Warehouses;

// Stop direct HTTP access.
if (!\defined('ABSPATH')) {
    exit;
}

if (!\class_exists('OriginUtils')) {
    class OriginUtils
    {
        /**
         * Extract Warehouse code from product.
         */
        public function get_origin_code_from_product(int $product_id): ?string {
            $origin_term_id = $this->get_origin_term_id_from_product($product_id);

            if($origin_term_id){
                return get_term_meta($origin_term_id, 'warehouse_code', true);
            }

            return null;
        }

        /**
         * Extract Warehouse term id from product.
         */
        public function get_origin_term_id_from_product(int $product_id): ?int {
            $origin_terms = wp_get_post_terms($product_id, WarehousesTaxonomy::TAXONOMY_SLUG, [ 'fields'=> 'ids']);
            
            if(is_array($origin_terms) && !empty($origin_terms)){
                
                return reset($origin_terms);
            }

            return null;
        }
    }
}
