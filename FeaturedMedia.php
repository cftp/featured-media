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
