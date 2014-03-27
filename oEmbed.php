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

class oEmbed {

	public $url      = null;
	public $provider = null;

	public function __construct( $url ) {

		$this->url      = $url;
		$this->provider = self::get_provider( $url );

	}

	public function fetch_details( array $args = null ) {

		if ( ! $this->url or ! $this->provider )
			return false;

		require_once ABSPATH . WPINC . '/class-oembed.php';

		return _wp_oembed_get_object()->fetch( $this->provider, $this->url, $args );

	}

	public static function get_provider( $url ) {

		$provider = false;

		if ( ! trim( $url ) ) {
			return $provider;
		}

		require_once ABSPATH . WPINC . '/class-oembed.php';

		# See http://core.trac.wordpress.org/ticket/24381

		foreach ( _wp_oembed_get_object()->providers as $matchmask => $data ) {
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

	public function has_provider() {
		return ( false !== $this->provider );
	}

}
