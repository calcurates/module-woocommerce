<?php

declare(strict_types=1);

namespace Calcurates\Origins;

// Stop direct HTTP access.
if (!\defined('ABSPATH')) {
    exit;
}

if (!\class_exists('OriginsTaxonomy')) {
    class OriginsTaxonomy
    {
        public const TAXONOMY_SLUG = 'origin';

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

            // generate Origin code
            add_action('create_'.self::TAXONOMY_SLUG, [$this, 'generate_origin_code'], 10, 2);

            // show Origin code
            add_action(self::TAXONOMY_SLUG.'_edit_form_fields', [$this, 'add_taxonomy_custom_fields'], 10, 2);
        }

        /**
         * Register the taxonomy.
         */
        public function register_taxonomy(): void
        {
            $labels = [
                'name' => __('Origin'),
                'singular_name' => __('Origin'),
                'menu_name' => __('Origins'),
                'all_items' => __('All Origins'),
                'parent_item' => null,
                'parent_item_colon' => null,
                'new_item_name' => __('New Origin Name'),
                'add_new_item' => __('Add New Origin'),
                'edit_item' => __('Edit Origin'),
                'update_item' => __('Update Origin'),
                'separate_items_with_commas' => __('Separate Origin with commas'),
                'search_items' => __('Search Origins'),
                'add_or_remove_items' => __('Add or remove Origins'),
                'choose_from_most_used' => __('Choose from the most used Origins'),
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
                    echo \get_term_meta($term_id, 'origin_code', true);
                    break;
                default:
                    break;
            }
        }

        /**
         * Generate Origin Code.
         */
        public function generate_origin_code(int $term_id, int $tt_id): void
        {
            add_term_meta($term_id, 'origin_code', \wc_rand_hash(), true);
        }

        /**
         * Print fields to origin taxonomy.
         */
        public function add_taxonomy_custom_fields(\WP_Term $term): void
        {
            ?>

		<tr class="form-field">
			<th scope="row" valign="top">
				<label>Origin Code</label>
			</th>
			<td>
				<p><?php echo get_term_meta($term->term_id, 'origin_code', true); ?></p>
			</td>
		</tr>

		<?php
        }
    }
}
