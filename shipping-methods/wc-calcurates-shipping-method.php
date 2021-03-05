<?php
defined('ABSPATH') || exit;

class WC_Calcurates_Shipping_Method extends WC_Shipping_Method
{

    public function __construct($instance_id = 0)
    {
        $this->id = 'calcurates';
        $this->instance_id = absint($instance_id);
        $this->method_title = __('Calcurates Shipping Method');
        $this->method_description = __('Calcurates Shipping Method');

        $this->enabled = "yes";
        $this->title = "Calcurates Shipping Method";

        $this->supports = array(
            'shipping-zones',
            'instance-settings',
            'settings',
        );

        $this->init();

        add_action('woocommerce_update_options_shipping_' . $this->id, array($this, 'process_admin_options'));
    }

    public function init()
    {
        $this->init_form_fields();
        $this->init_settings();

        // Save settings in admin if you have any defined
        add_action('woocommerce_update_options_shipping_' . $this->id, array($this, 'process_admin_options'));
    }

    public function calculate_shipping($package = [])
    {
        $rate = array(
            'label' => $this->title,
            'cost' => '0',
            'calc_tax' => 'per_item',
        );

        $this->add_rate($rate);
    }
}
