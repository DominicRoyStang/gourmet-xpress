<?php
/**
 * Storefront engine room
 *
 * @package storefront
 */

/**
 * Assign the Storefront version to a var
 */
$theme              = wp_get_theme( 'storefront' );
$storefront_version = $theme['Version'];

/**
 * Set the content width based on the theme's design and stylesheet.
 */
if ( ! isset( $content_width ) ) {
	$content_width = 980; /* pixels */
}

$storefront = (object) array(
	'version' => $storefront_version,

	/**
	 * Initialize all the things.
	 */
	'main'       => require 'inc/class-storefront.php',
	'customizer' => require 'inc/customizer/class-storefront-customizer.php',
);

require 'inc/storefront-functions.php';
require 'inc/storefront-template-hooks.php';
require 'inc/storefront-template-functions.php';

if ( class_exists( 'Jetpack' ) ) {
	$storefront->jetpack = require 'inc/jetpack/class-storefront-jetpack.php';
}

if ( storefront_is_woocommerce_activated() ) {
	$storefront->woocommerce = require 'inc/woocommerce/class-storefront-woocommerce.php';

	require 'inc/woocommerce/storefront-woocommerce-template-hooks.php';
	require 'inc/woocommerce/storefront-woocommerce-template-functions.php';
}

if ( is_admin() ) {
	$storefront->admin = require 'inc/admin/class-storefront-admin.php';

	require 'inc/admin/class-storefront-plugin-install.php';
}

/**
 * NUX
 * Only load if wp version is 4.7.3 or above because of this issue;
 * https://core.trac.wordpress.org/ticket/39610?cversion=1&cnum_hist=2
 */
if ( version_compare( get_bloginfo( 'version' ), '4.7.3', '>=' ) && ( is_admin() || is_customize_preview() ) ) {
	require 'inc/nux/class-storefront-nux-admin.php';
	require 'inc/nux/class-storefront-nux-guided-tour.php';

	if ( defined( 'WC_VERSION' ) && version_compare( WC_VERSION, '3.0.0', '>=' ) ) {
		require 'inc/nux/class-storefront-nux-starter-content.php';
	}
}

/**
 * Note: Do not add any custom code here. Please use a custom plugin so that your customizations aren't lost during updates.
 * https://github.com/woocommerce/theme-customisations
 */

// Gourmet Xpress mods
// Add date option to order
add_action( 'woocommerce_after_order_notes', 'my_custom_checkout_field' );

function my_custom_checkout_field( $checkout ) {
	echo '<div id="my_order_date"><h2>' . __('Order Date') . '</h2>';
	$_args = array(
		'type'          => 'select',
		'class'         => array('my-field-class form-row-wide'),
		'label'         => __('Date'),
		'placeholder'   => __('Enter date'),
		'options'				=> array(),
	);
	$_time = time();
	$_day = 3600 * 24;
	for ( $x = 0; $x < 10; $x ++ ) {
		$_date = date( 'D M d', $_time + $x * $_day);
		$_args['options'][ $_date ] = $_date;
	}
	woocommerce_form_field( 'order_date', $_args, $checkout->get_value( 'my_field_name' ));
	echo '</div>';
}

// Product variation workaround (== ugly hack) to allow dates on prods
// add_filter( 'woocommerce_variation_option_name', 'woocommerce_variation_option_name_cmj', 10, 1 );
// function woocommerce_variation_option_name_cmj( $_val ) {
// 	if ( ( '0' === $_val ) || ( 0 < intval( $_val ) ) ) {
// 		$_time = time();
// 		$_day = 3600 * 24;
// 		$_val = intval( $_val );
// 		$_date = date( 'D M d', $_time + $_val * $_day);
// 		return $_date;
// 	} else {
// 		return $_val;
// 	}
// }
