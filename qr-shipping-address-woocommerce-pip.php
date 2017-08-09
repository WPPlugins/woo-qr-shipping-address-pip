<?php
/**
 * Plugin Name: Shipping Address QR code for WooCommerce Print Invoices & Packing lists
 * Plugin URI: https://www.webdados.pt/produtos-e-servicos/internet/desenvolvimento-wordpress/shipping-address-qr-code-for-woocommerce-print-invoices-packing-lists-wordpress/
 * Description: Adds a QR Code with the Shipping Address link on Google Maps into "Print Invoices & Packing lists" (woocommerce.com) and "WooCommerce Print Invoice & Delivery Note" (wordpress.org) plugins.
 * Version: 1.0.1
 * Author: Webdados
 * Author URI: https://www.webdados.pt
 */

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) {
	exit; 
}

class QR_Code_WC_PIP {

	public $qr_size = 100;

	// Constructor
	public function __construct() {
		// Hook up to the init action
		add_action( 'init', array( $this, 'init' ) );
	}

	// Init
	public function init() {
		// Set QR Code size - You can define QR_WCDN_SIZE to any value you want on wp-config.php
		$this->qr_size = intval( defined( 'QR_WCDN_SIZE' ) ? QR_WCDN_SIZE : $this->qr_size );
		// WooCommerce Print Invoices & Packing lists (below shipping address) - https://woocommerce.com/products/print-invoices-packing-lists/
		add_filter( 'wc_pip_shipping_address', array( $this, 'wc_pip_shipping_address' ), 10, 3);
		// WooCommerce Print Invoice & Delivery Note (below shipping address) - https://wordpress.org/plugins/woocommerce-delivery-notes/
		add_filter( 'wcdn_address_shipping', array( $this, 'wcdn_address_shipping' ), 10, 2);
	}

	// QR Code Image Link from qrserver.com
	public function qr_code_image_link( $order ) {
		add_filter( 'woocommerce_formatted_address_replacements', array( $this, 'shipping_address_remove_names' ), 10, 2 );
		$qr_address = trim( str_replace( '<br>', ' ', str_replace( '<br/>', ' ', $order->get_formatted_shipping_address() ) ) );
		remove_filter( 'woocommerce_formatted_address_replacements', array( $this, 'shipping_address_remove_names' ), 10, 2 );
		$google_maps_url = 'https://maps.google.com/maps?daddr='.urlencode( $qr_address );
		$qr_image_url = 'https://api.qrserver.com/v1/create-qr-code/?size='.intval($this->qr_size).'x'.intval($this->qr_size).'&data='.urlencode( $google_maps_url );
		return $qr_image_url;
	}

	// QR Code Image tag
	public function qr_code_image_tag( $order ) {
		return '<img src="'.esc_url( $this->qr_code_image_link( $order ) ).'" width="'.intval($this->qr_size).'" height="'.intval($this->qr_size).'"/>';
	}

	// Remove names and company from the shipping address
	public function shipping_address_remove_names( $array, $args ) {
		$array['{first_name}']			='';
		$array['{last_name}']			='';
		$array['{name}']				='';
		$array['{company}']				='';
		$array['{first_name_upper}']	='';
		$array['{last_name_upper}']		='';
		$array['{name_upper}']			='';
		$array['{company_upper}']		='';
		return $array;
	}

	// WooCommerce Print Invoices & Packing lists (below shipping address)
	public function wc_pip_shipping_address( $address, $type, $order ) {
		return $address.'<br/><br/>'.$this->qr_code_image_tag( $order );
	}

	// WooCommerce Print Invoice & Delivery Note(below shipping address)
	public function wcdn_address_shipping( $address, $order ) {
		return $address.'<br/><br/>'.$this->qr_code_image_tag( $order );
	}

}

$qr_code_wc_pip = new QR_Code_WC_PIP();

/* If you're reading this you must know what you're doing ;-) Greetings from sunny Portugal! */

