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

            // Save Origin code
            add_action('create_'.self::TAXONOMY_SLUG, [$this, 'save_code_field'], 10, 2);

            // show Origin code
            add_action(self::TAXONOMY_SLUG.'_edit_form_fields', [$this, 'add_taxonomy_custom_fields'], 10, 2);

            // Add Origin code field
            add_action(self::TAXONOMY_SLUG.'_add_form_fields', [$this, 'add_code_field']);

            // Validate the Code
            add_filter('pre_insert_term', [$this, 'validate_code'], 10, 2);
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

        /**
         * Add code field to 'add term' screen.
         */
        public function add_code_field(string $taxonomy_slug): void
        {
            ?>
            <div class="form-field form-required">
                <label for="tag-title">Code</label>
                <input name="origin_code" id="tag-title" type="text" value="" maxlength="255" aria-required="true"/>
                <p>Enter any unique code.</p>
            </div>
            <?php
        }

        /**
         * Save Origin Code.
         */
        public function save_code_field(int $term_id, int $tt_id): void
        {
            add_term_meta($term_id, 'origin_code', \sanitize_text_field($_POST['origin_code']), true);
        }

        /**
         * Code validation.
         *
         * @return string|\WP_Error
         */
        public function validate_code(string $term, string $taxonomy)
        {
            if (self::TAXONOMY_SLUG === $taxonomy && (!\sanitize_text_field($_POST['origin_code']) || OriginUtils::getInstance()->is_code_exists($_POST['origin_code']))) {
                return new \WP_Error('code-validation-error', __('The Code already exists. Please enter a unique one.'));
            }

            return $term;
        }
    }
}
