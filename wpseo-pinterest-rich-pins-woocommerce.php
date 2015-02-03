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
	 * @var string
	 */
	private $prefix = 'iti_wpseo_pinterest_';

	function __construct() {
		add_action( 'wpseo_tab_header', array( $this, 'tab_header' ), 990 );
		add_action( 'wpseo_tab_content', array( $this, 'tab_content' ), 990 );
		add_filter( 'wpseo_save_metaboxes', array( $this, 'save_metadata' ) );
	}

	/**
	 * Determine whether this is a WooCommerce product page and WP SEO exists
	 *
	 * @since  0.1
	 * @return boolean If the environment is applicable
	 */
	function is_applicable() {
		return 'product' == get_post_type() && class_exists( 'WPSEO_Metabox' );
	}

	/**
	 * Callback for WP SEO's tab header action, outputs our Pinterest tab
	 *
	 * @since  0.1
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
	 * @return string The HTML content for thet ab
	 */
	function tab_content() {
		global $post;

		if ( ! $this->is_applicable() ) {
			return;
		}

		$this->meta_box = new WPSEO_Metabox();

		$this->meta_box->do_tab( 'itiwpseopinterest', __( 'Pinterest', 'iti-wpseo-pinterest' ), $this->pinterest_output( $post ) );
	}

	function save_metadata( $metadata ) {

		$metadata = array_merge( $metadata, $this->get_meta_field_defs() );

		return $metadata;
	}

	private function get_meta_field_defs() {
		return array(
			$this->prefix . 'pdesc' => array(
				'title'         => __( 'Description', 'iti-wpseo-pinterest' ),
				'description'   => __( 'May be truncated, all line breaks and HTML tags will be removed.', 'iti-wpseo-pinterest' ),
				'class'         => 'iti-wpseo-pinterest-description',
				'placeholder'   => get_the_title(),
				'type'          => 'text'
			),
		);
	}

	/**
	 * Generate the HTML for all of the necessary Pinterest Rich Pin fields that aren't auto-defined by WooCommerce product attributes
	 *
	 * @since  0.1
	 * @param  WP_Post $post The product (post) object
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

}

new WPSEO_Pinterest_Rich_Pins();
