<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 *
 * Prod:
 * site: 20113
 * url: eea6210cf95230b086cbbd98e3de440f21e8f6fd
 * prodid: 19354
 *
 * Demo:
 * site: yzhah5yj62
 * url: 5a13b1015beab8a04617016c774ffe0b
 * prodid: 502907
 */

/**
 * Description of WC_nps_payment
 *
 * @author amritansh
 */
class WC_nps_payment extends WC_Payment_Gateway {

	public function __construct() {
		$this->id                 = "nps_gateway";
		$this->has_fields         = false;
		$this->method_title       = "NPS payment";
		$this->method_description = "NPS payment gateway allows you to pay via NPS";
		$this->order_button_text  = __( 'Proceed to NPS', 'woocommerce' );
		$this->method_title       = __( 'NPS payment', 'woocommerce' );
		$this->method_description = "NPS payment gateway allows you to pay via NPS";
		$this->supports           = array(
			'products'
		);

		$this->init_form_fields();
		$this->init_settings();
		$this->title             = $this->get_option( 'title' );
		$this->order_button_text = $this->get_option( 'checkout_button_text' );
		$this->testmode          = 'yes' === $this->get_option( 'testmode', 'no' );
		$this->mode              = ( $this->get_option( 'testmode', 'no' ) == "yes" ) ? "test" : "live";

		add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array(
			$this,
			'process_admin_options'
		) );
	}

	public function init_form_fields() {

		$this->form_fields = array(
			'enabled'              => array(
				'title'   => __( 'Enable/Disable', 'woocommerce' ),
				'type'    => 'checkbox',
				'label'   => __( 'Enable NPS payment', 'woocommerce' ),
				'default' => 'no'
			),
			'title'                => array(
				'title'       => __( 'Title', 'woocommerce' ),
				'type'        => 'text',
				'description' => __( 'This controls the title which the user sees during checkout.', 'woocommerce' ),
				'default'     => __( 'NPS payment', 'woocommerce' ),
				'desc_tip'    => true,
			),
			'checkout_button_text' => array(
				'title'       => __( 'Checkout Button text', 'woocommerce' ),
				'type'        => 'text',
				'description' => __( 'This controls the title which the user sees during checkout.', 'woocommerce' ),
				'default'     => __( 'Proceed to pay now', 'woocommerce' ),
				'desc_tip'    => true,
			),
			'testmode'             => array(
				'title'   => __( 'Enable test mode', 'woocommerce' ),
				'type'    => 'checkbox',
				'label'   => __( 'Enable Nps test', 'woocommerce' ),
				'default' => 'yes',
			),
			'urlkey_test'          => array(
				'title'       => __( 'Url key (test)', 'woocommerce' ),
				'type'        => 'text',
				'description' => __( 'This controls the title which the user sees during checkout.', 'woocommerce' ),
				'default'     => __( '', 'woocommerce' ),
				'desc_tip'    => true,
			),
			'siteid_test'          => array(
				'title'       => __( 'Site Id (test)', 'woocommerce' ),
				'type'        => 'text',
				'description' => __( 'This controls the title which the user sees during checkout.', 'woocommerce' ),
				'default'     => __( '', 'woocommerce' ),
				'desc_tip'    => true,
			),
			'prodid_test'          => array(
				'title'       => __( 'Prod Id (test)', 'woocommerce' ),
				'type'        => 'text',
				'description' => __( 'This controls the title which the user sees during checkout.', 'woocommerce' ),
				'default'     => __( '', 'woocommerce' ),
				'desc_tip'    => true,
			),
			'urlkey_live'          => array(
				'title'       => __( 'Url key (Live)', 'woocommerce' ),
				'type'        => 'text',
				'description' => __( 'This controls the title which the user sees during checkout.', 'woocommerce' ),
				'default'     => __( '', 'woocommerce' ),
				'desc_tip'    => true,
			),
			'siteid_live'          => array(
				'title'       => __( 'Site Id (Live)', 'woocommerce' ),
				'type'        => 'text',
				'description' => __( 'This controls the title which the user sees during checkout.', 'woocommerce' ),
				'default'     => __( '', 'woocommerce' ),
				'desc_tip'    => true,
			),
			'prodid_live'          => array(
				'title'       => __( 'Prod Id (Live)', 'woocommerce' ),
				'type'        => 'text',
				'description' => __( 'This controls the title which the user sees during checkout.', 'woocommerce' ),
				'default'     => __( '', 'woocommerce' ),
				'desc_tip'    => true,
			),
			'description'          => array(
				'title'   => __( 'Customer Message', 'woocommerce' ),
				'type'    => 'textarea',
				'default' => ''
			)
		);
	}

	public function process_payment( $order_id ) {
		global $woocommerce;
		$order = wc_get_order( $order_id );

		ob_start();

		print_r( array( $order->get_items(), $order_id, $order ) );

		$out = ob_get_clean();

		update_option( "order_debug", $out );

		// Mark as on-hold (we're awaiting the cheque)
		// $order->update_status('pending-payment');
		// Reduce stock levels
		$order->reduce_order_stock();

		// Remove cart
		$woocommerce->cart->empty_cart();

		if ( $this->testmode ) {
			$endpont = 'https://demo.municipay.com/payapp/public/WSRequest.html';
		} else {
			$endpont = 'https://secure.municipay.com/payapp/public/WSRequest.html';
		}

		// Return thankyou redirect
		return array(
			'result'   => 'success',
			'redirect' => $endpont . "?" . $this->generate_args_url( $order )
		);
	}

	public function generate_args_url( $order ) {
		$nps_agrs = http_build_query( array_merge(
			array(
				'urlKey'        => $this->get_option( 'urlkey_' . $this->mode ),
				'siteId'        => $this->get_option( 'siteid_' . $this->mode ),
				'transactionId' => '#',
				'redirectURL'   => ( $this->get_return_url( $order ) . "&npsTransactionId" )
			), $this->generate_items_array( $order ) ), '', '&' );

		return $nps_agrs;
	}

	public function generate_items_array( $order ) {
		$incr  = 1;
		$array = array();

		foreach ( $order->get_items() as $item ) {

			$pID = $item->get_id();

			if ( $pID ) {
				// Add condition for production/test modes
				$pro_id = '';
				if ( isset( $item['_refdata'] ) ) {
					$pro_id = explode( "_", $item['_refdata'] );
				}
				$array[ 'prodId_' . $incr ] = $this->get_option( 'prodid_' . $this->mode );
				$array[ 'refNum_' . $incr ] = $item['name'];
				$array[ 'amount_' . $incr ] = $item['line_total'];
				$incr ++;
			}
		}

		update_option( 'debug', $order );

		return $array;
	}

}
