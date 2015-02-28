<?php
/*
Plugin Name: WPSEO Pinterest Rich Pins for WooCommerce
Plugin URI: https://github.com/jchristopher/wpseo-pinterest-rich-pins-woocommerce
Description: Use WordPress SEO to set up Pinterest Rich Pins for WooCommerce Products
Version: 0.1
Author: Jonathan Christopher
Author URI: https://mondaybynoon.com/
Text Domain: iti-wpseo-pinterest

Copyright 2015 Jonathan Christopher

This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; either version 2
of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, see <http://www.gnu.org/licenses/>.
*/

// exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WPSEO_Pinterest_Rich_Pins {

	/**
	 * Reference to the WP SEO Meta Box class
	 *
	 * @since 0.1
	 * @var object
	 */
	private $meta_box;

	/**
	 * Used for form field storage via WP SEO
	 *
	 * @since 0.1
	 *
	 * @var string
	 */
	private $prefix = 'iti_wpseo_pinterest_';

	function __construct() {

		add_action( 'plugins_loaded', array( $this, 'set_meta_box' ) );

		// back end
		add_action( 'wpseo_tab_header', array( $this, 'tab_header' ), 990 );
		add_action( 'wpseo_tab_content', array( $this, 'tab_content' ), 990 );
		add_filter( 'wpseo_save_metaboxes', array( $this, 'save_metadata' ) );

		// front end
		add_filter( 'wpseo_opengraph_title', array( $this, 'opengraph_title' ) );
		add_filter( 'wpseo_metadesc', array( $this, 'opengraph_description' ) );
		add_filter( 'wpseo_opengraph_type', array( $this, 'opengraph_type' ) );
		add_action( 'wp_head', array( $this, 'output_rich_pin_meta_markup' ) );
	}

	/**
	 * Callback to set our meta_box property to WPSEO's meta box class
	 *
	 * @since 0.1
	 */
	function set_meta_box() {
		$this->meta_box = new WPSEO_Metabox();
	}

	/**
	 * Determine whether this is a WooCommerce product page and WP SEO exists
	 *
	 * @since  0.1
	 *
	 * @return boolean If the environment is applicable
	 */
	function is_applicable() {
		return 'product' == get_post_type() && class_exists( 'WPSEO_Metabox' ) && class_exists( 'WC_Product' );
	}

	/**
	 * Callback for WP SEO's tab header action, outputs our Pinterest tab
	 *
	 * @since  0.1
	 *
	 * @return void
	 */
	function tab_header() {
		global $post;

		if ( ! $this->is_applicable() ) {
			return;
		}

		?>
			<li id="itiwpseopinterest" class="itiwpseopinterest">
				<a class="wpseo_tablink" href="#wpseo_itiwpseopinterest"><?php _e( 'Pinterest', 'iti-wpseo-pinterest' ); ?></a>
			</li>
		<?php
	}

	/**
	 * Output the tab content in WP SEO's meta box (the fields themselves)
	 *
	 * @since  0.1
	 *
	 * @return string The HTML content for thet ab
	 */
	function tab_content() {
		global $post;

		if ( ! $this->is_applicable() ) {
			return;
		}

		$content = __( '<p>All fields optional. Fields for URL, title, price, and currency_code are provided directly from WooCommerce.</p>', 'iti-wpseo-pinterest' );

		$this->meta_box->do_tab( 'itiwpseopinterest', __( 'Pinterest', 'iti-wpseo-pinterest' ), $content . $this->pinterest_output( $post ) );
	}

	/**
	 * Callback for WPSEO's metadata save handler; it needs our meta field definitions
	 *
	 * @since 0.1
	 * @param $metadata array Existing metadata
	 *
	 * @return array Metadata definitions
	 */
	function save_metadata( $metadata ) {

		$metadata = array_merge( $metadata, $this->get_meta_field_defs() );

		return $metadata;
	}

	/**
	 * Return the meta box field definitions for WPSEO
	 *
	 * @since 0.1
	 *
	 * @return array Meta field definitions as per WPSEO
	 */
	private function get_meta_field_defs() {
		return array(
			$this->prefix . 'title' => array(
				'title'         => __( 'Title', 'iti-wpseo-pinterest' ),
				'description'   => __( 'Defaults to Product title. May be truncated, all line breaks and HTML tags will be removed.', 'iti-wpseo-pinterest' ),
				'placeholder'   => get_the_title(),
				'type'          => 'text'
			),
			$this->prefix . 'description' => array(
				'title'         => __( 'Description', 'iti-wpseo-pinterest' ),
				'description'   => __( 'Defaults to WooCommerce Short Description. May be truncated, all line breaks and HTML tags will be removed.', 'iti-wpseo-pinterest' ),
				'placeholder'   => strip_tags( get_the_content() ),
				'type'          => 'textarea'
			),
			$this->prefix . 'brand' => array(
				'title'         => __( 'Brand', 'iti-wpseo-pinterest' ),
				'description'   => __( 'Brand name (for example "Lucky Brand").', 'iti-wpseo-pinterest' ),
				'placeholder'   => get_bloginfo( 'name' ),
				'type'          => 'text'
			),
		);
	}

	/**
	 * Generate the HTML for all of the necessary Pinterest Rich Pin fields that aren't auto-defined by WooCommerce product attributes
	 *
	 * @since  0.1
	 * @param  WP_Post $post The product (post) object
	 *
	 * @return string The HTML output for all of the additional Pinterest Rich Pin fields
	 */
	function pinterest_output( $post ) {
		$content = '';

		$meta_fields = $this->get_meta_field_defs();

		foreach ( $meta_fields as $meta_field_key => $meta_field ) {
			$content .= $this->meta_box->do_meta_box( $meta_field, $meta_field_key );
		}

		return $content;
	}

	/**
	 * Retrieve meta value for submitted field
	 *
	 * @since 0.1
	 * @param $field string The field key (without any prefix)
	 * @param $default string The default value
	 *
	 * @return string The meta value
	 */
	function get_wpseo_metadata( $field, $default ) {
		global $post;

		$maybe_custom_meta = $this->meta_box->get_value( $this->prefix . $field, $post->ID );

		return ! empty( $maybe_custom_meta ) ? trim( $maybe_custom_meta ) : trim( $default );
	}

	/**
	 * Maybe change the WPSEO OpenGraph title from the post title to something customized for Pintereset Rich Pin
	 *
	 * @since 0.1
	 * @param $og_title
	 *
	 * @return string the OpenGraph title
	 */
	function opengraph_title( $og_title ) {
		if ( 'product' == get_post_type() ) {
			$og_title = $this->get_wpseo_metadata( 'title', $og_title );
		}

		return $og_title;
	}


	/**
	 * Maybe change the WPSEO OpenGraph Description from the post description to something customized for Pintereset Rich Pin
	 *
	 * @since 0.1
	 * @param $og_description
	 *
	 * @return string the OpenGraph Description
	 */
	function opengraph_description( $og_description ) {
		if ( 'product' == get_post_type() ) {
			$og_description = $this->get_wpseo_metadata( 'description', $og_description );
		}
		
		return $og_description;
	}




	/**
	 * Maybe change the WPSEO OpenGraph type from the default to 'product' as per Pinterest API
	 *
	 * @since 0.1
	 * @param $og_type
	 *
	 * @return string the OpenGraph type
	 */
	function opengraph_type( $og_type ) {
		if ( 'product' == get_post_type() ) {
			$og_type = 'product';
		}

		return $og_type;
	}

	/**
	 * Output all of the applicable <meta> tags for Pinterest Rich Pins
	 *
	 * @since 0.1
	 */
	function output_rich_pin_meta_markup() {
		global $post;

		if ( ! $this->is_applicable() ) {
			return;
		}

		$product = new WC_Product( $post );

		// WooCommerce provides a number of field values automatically, output those
		$this->output_woocommerce_product_rich_pin_meta( $product );

		// output the user-defined Rich Pin fields
		$this->output_product_rich_pin_meta( $product );
	}

	/**
	 * Pinterest allows for the following stock levels which differ from WooCommerce:
	 *  - in stock
	 *  - preorder (not supported at this time)
	 *  - backorder
	 *  - out of stock
	 *  - discontinued (not supported at this time)
	 *
	 * @since 0.1
	 * @param WC_Product $product
	 * @return string $stock_level The stock level formatted for Rich Pin
	 */
	function format_stock_level( WC_Product $product ) {

		$stock_level = get_post_meta( $product->id, '_stock_status', true ); // TODO: got to be a better way

		switch ( $stock_level ) {
			case 'instock':
				$stock_level = 'in stock';
				break;
			case 'outofstock';
				$stock_level = $product->backorders_allowed() ? 'backorder' : 'out of stock';
				break;
		}

		return $stock_level;
	}

	/**
	 * Output the Rich Pin meta tags provided directly by WooCommerce itself
	 *
	 * @todo Output sale details
	 *
	 * @since 0.1
	 * @param WC_Product $product
	 */
	function output_woocommerce_product_rich_pin_meta( WC_Product $product ) {
		$product_cost = number_format( floatval( $product->get_price() ), 2 ); ?>
		<meta property="og:price:amount" content="<?php echo esc_attr( $product_cost ); ?>" />
		<meta property="og:price:currency" content="<?php echo esc_attr( get_woocommerce_currency() ); ?>" />
		<meta property="og:upc" content="<?php echo esc_attr( $product->get_sku() ); ?>" />
		<meta property="og:availability" content="<?php echo esc_attr( $this->format_stock_level( $product ) ); ?>" /><?php
	}

	/**
	 * Output the Pinterest-specific (not WooCommerce provided) Rich Pin metadata
	 *
	 * @since 1.0
	 * @param WC_Product $product
	 */
	function output_product_rich_pin_meta( WC_Product $product ) {
		$brand = $this->get_wpseo_metadata( 'brand', '' );
		if ( ! empty( $brand ) ) : ?>
			<meta property="og:brand" content="<?php echo esc_attr( $brand ); ?>" />
		<?php endif;
	}

}

// kickoff
new WPSEO_Pinterest_Rich_Pins();
