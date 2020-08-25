<?php
/**
 * WooCommerce Extension Boilerplate Lite Hooks
 *
 * Hooks for various functions used.
 *
 * @author 		Your Name / Your Company Name
 * @category 	Core
 * @package 	WooCommerce Extension Boilerplate Lite/Functions
 * @version 	1.0.2
 */
if (!defined('ABSPATH'))
    exit; // Exit if accessed directly

function check_attr_flds() {

    global $post;

    $get_post_meta = get_post_meta(get_the_ID(), '_prodId_nps', TRUE);


   
        echo '<div class="options_group">';

        ob_start();
        woocommerce_wp_text_input(array(
            'id' => '_prodId_nps',
            'label' => __('NPS product ID', 'woocommerce'),
            'value' => $get_post_meta,
            //   'options' => $options_Arr,
            'description' => __('Input your product ID here for NPS payment', 'woocommerce'),
            'desc_tip' => true
        ));
        ?>
        </div>
        <?php
    
    echo ob_get_clean();
}

add_action("woocommerce_product_options_general_product_data", "check_attr_flds", 10, 0);

add_action('woocommerce_process_product_meta', 'save_gen_meta');

function save_gen_meta($id) {
    if (isset($_POST['_prodId_nps'])) {
        update_post_meta($id, "_prodId_nps", $_POST['_prodId_nps']);
    }
}
?>