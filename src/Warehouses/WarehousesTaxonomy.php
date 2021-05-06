<?php

declare(strict_types=1);

namespace Calcurates\Warehouses;

// Stop direct HTTP access.
if (!\defined('ABSPATH')) {
    exit;
}

if (!\class_exists('WarehousesTaxonomy')) {
    class WarehousesTaxonomy
    {
        public const TAXONOMY_SLUG = 'warehouse';

        /**
         * Bootstrap new taxonomy.
         */
        public function init(): void
        {
            // register taxonomy
            add_action('init', [$this, 'register_taxonomy'], 1);

            // hide some fields
            add_action(self::TAXONOMY_SLUG.'_edit_form', [$this, 'hide_fields']);
            add_action(self::TAXONOMY_SLUG.'_add_form', [$this, 'hide_fields']);

            // edit columns
            add_filter('manage_edit-'.self::TAXONOMY_SLUG.'_columns', [$this, 'edit_columns']);
            add_filter('manage_'.self::TAXONOMY_SLUG.'_custom_column', [$this, 'manage_columns'], 10, 3);

            // generate Warehouse code
            add_action('create_'.self::TAXONOMY_SLUG, [$this, 'generate_warehouse_code'], 10, 2);

            // show Warehouse code
            add_action(self::TAXONOMY_SLUG.'_edit_form_fields', [$this, 'add_taxonomy_custom_fields'], 10, 2);
        }

        /**
         * Register the taxonomy.
         */
        public function register_taxonomy(): void
        {
            $labels = [
                'name' => __('Warehouse'),
                'singular_name' => __('Warehouse'),
                'menu_name' => __('Warehouses'),
                'all_items' => __('All Warehouses'),
                'parent_item' => null,
                'parent_item_colon' => null,
                'new_item_name' => __('New Warehouse Name'),
                'add_new_item' => __('Add New Warehouse'),
                'edit_item' => __('Edit Warehouse'),
                'update_item' => __('Update Warehouse'),
                'separate_items_with_commas' => __('Separate Warehouse with commas'),
                'search_items' => __('Search Warehouses'),
                'add_or_remove_items' => __('Add or remove Warehouses'),
                'choose_from_most_used' => __('Choose from the most used Warehouses'),
            ];
            $capabilities = [
                'manage_terms' => 'manage_woocommerce',
                'edit_terms' => 'manage_woocommerce',
                'delete_terms' => 'manage_woocommerce',
                'assign_terms' => 'manage_woocommerce',
            ];
            $args = [
                'labels' => $labels,
                'hierarchical' => false,
                'public' => true,
                'show_ui' => true,
                'show_admin_column' => true,
                'show_in_rest' => false,
                'show_in_nav_menus' => true,
                'show_tagcloud' => false,
                'meta_box_cb' => false,
                'capabilities' => $capabilities,
            ];

            register_taxonomy(self::TAXONOMY_SLUG, 'product', $args);
        }

        /**
         * Hide unused fields from admin.
         */
        public function hide_fields(): void
        {
            echo '<style>.term-description-wrap, .term-parent-wrap, .term-slug-wrap { display:none; } </style>';
        }

        /**
         * Change columns displayed in table.
         */
        public function edit_columns(array $columns): array
        {
            $columns['code'] = 'Code';

            if (isset($columns['description'])) {
                unset($columns['description']);
            }

            if (isset($columns['slug'])) {
                unset($columns['slug']);
            }

            return $columns;
        }

        /**
         * Print data in terms column.
         */
        public function manage_columns(string $out, string $column_name, int $term_id): void
        {
            switch ($column_name) {
                case 'code':
                    echo \get_term_meta($term_id, 'warehouse_code', true);
                    break;
                default:
                    break;
            }
        }

        /**
         * Generate Warehouse Code.
         */
        public function generate_warehouse_code(int $term_id, int $tt_id): void
        {
            add_term_meta($term_id, 'warehouse_code', \wc_rand_hash(), true);
        }

        /**
         * Print fields to warehouse taxonomy.
         */
        public function add_taxonomy_custom_fields(\WP_Term $term): void
        {
            ?>

		<tr class="form-field">
			<th scope="row" valign="top">
				<label>Warehouse Code</label>
			</th>
			<td>
				<p><?php echo get_term_meta($term->term_id, 'warehouse_code', true); ?></p>
			</td>
		</tr>

		<?php
        }
    }
}
