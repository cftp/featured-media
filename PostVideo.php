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

class PostVideo {

	public $post    = null;
	public $url     = null;
	public $details = null;
	public $oembed  = null;

	public function __construct( $post_id ) {

		$this->post = get_post( $post_id );
		$this->url  = $this->get_url();

	}

	public function get_embed_code( array $args = null ) {

		if ( ! $this->get_url() )
			return null;

		if ( ! $codes = get_post_meta( $this->post->ID, '_featured-media-video-embed-codes', true ) )
			$codes = array();

		$args = array_merge( wp_embed_defaults(), $args );
		$key  = md5( serialize( $args ) );

		if ( ! isset( $codes[$key] ) ) {

			if ( ! $details = $this->oembed()->fetch_details( $args ) )
				return null;

			$codes[$key] = $details->html;
			update_post_meta( $this->post->ID, '_featured-media-video-embed-codes', $codes );

		}

		return $codes[$key];

	}

	public function get_field( $field ) {

		if ( ! $details = $this->get_details() )
			return null;

		if ( 'html' == $field )
			return $this->get_embed_code();

		if ( ! isset( $details->$field ) )
			return null;

		return $details->$field;

	}

	public function get_details() {

		return get_post_meta( $this->post->ID, '_featured-media-video-details', true );

	}

	public function update_details() {

		$args = wp_embed_defaults();

		if ( ! $details = $this->oembed()->fetch_details( $args ) )
			return false;

		$this->details = $details;

		$embed_key = md5( serialize( $args ) );

		update_post_meta( $this->post->ID, '_featured-media-video-details', $details );
		update_post_meta( $this->post->ID, '_featured-media-video-embed-codes', array(
			$embed_key => $details->html
		) );

		return $this->details;

	}

	public function update_url( $url ) {

		if ( empty( $url ) )
			return false;

		$url = esc_url_raw( $url );

		if ( ! oEmbed::get_provider( $url ) ) {
			return false;
		}

		$this->url = $url;

		update_post_meta( $this->post->ID, '_featured-media-video-url', $url );

		return true;

	}

	public function delete_url() {

		$this->url = null;

		delete_post_meta( $this->post->ID, '_featured-media-video-url' );

		return true;

	}

	public function get_url() {

		$meta = get_post_meta( $this->post->ID, '_featured-media-video-url', true );

		if ( !empty( $meta ) )
			return esc_url_raw( $meta );
		else
			return null;

	}

	protected function oembed() {

		if ( ! $this->oembed )
			$this->oembed = new oEmbed( $this->url );

		return $this->oembed;

	}

}
