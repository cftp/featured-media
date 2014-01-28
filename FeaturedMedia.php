<?php
/*
Copyright © 2014 Code for the People Ltd

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

*/

namespace FeaturedMedia;

class FeaturedMedia extends Plugin {

	protected function __construct( $file ) {

		# Actions
		add_action( 'plugins_loaded', array( $this, 'action_plugins_loaded' ) );

		# Filters
		add_filter( 'post_class', array( $this, 'filter_post_class' ), 10, 3 );

		# Parent setup:
		parent::__construct( $file );

	}

	public static function init( $file = null ) {
		static $instance = null;

		if ( !$instance )
			$instance = new FeaturedMedia( $file );

		return $instance;

	}

	public static function get_oembed_provider( $url ) {

		$provider = false;

		if ( ! trim( $url ) )
			return $provider;

		require_once ABSPATH . WPINC . '/class-oembed.php';

		$providers = _wp_oembed_get_object()->providers;

		# See http://core.trac.wordpress.org/ticket/24381

		foreach ( $providers as $matchmask => $data ) {
			list( $providerurl, $regex ) = $data;

			// Turn the asterisk-type provider URLs into regex
			if ( !$regex ) {
				$matchmask = '#' . str_replace( '___wildcard___', '(.+)', preg_quote( str_replace( '*', '___wildcard___', $matchmask ), '#' ) ) . '#i';
				$matchmask = preg_replace( '|^#http\\\://|', '#https?\://', $matchmask );
			}

			if ( preg_match( $matchmask, $url ) ) {
				$provider = str_replace( '{format}', 'json', $providerurl ); // JSON is easier to deal with than XML
				break;
			}
		}

		return $provider;

	}

	public function filter_post_class( $classes, $class, $post_id ) {

		if ( has_featured_media_video( $post_id ) )
			$classes[] = 'has-featured-media-video';
		if ( has_featured_media_thumbnail( $post_id ) )
			$classes[] = 'has-featured-media-thumbnail';
		if ( has_featured_media_gallery( $post_id ) )
			$classes[] = 'has-featured-media-gallery';

		return $classes;

	}

	public function action_plugins_loaded() {

		if ( is_admin() )
			$this->admin = new Admin;

	}

}
