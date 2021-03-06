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

class ExternalPhoto {

	public $attachment    = null;
	public $attachment_id = null;

	public function __construct( $url ) {
		$this->url = $url;
	}

	public function import( $filename ) {

		if ( !class_exists( 'WP_Http' ) ) {
			include_once ABSPATH . WPINC. '/class-http.php';
		}

		$tmpfname = wp_tempnam($this->url);
		if ( ! $tmpfname )
			return false;

		$photo = wp_safe_remote_get( $this->url, array( 'timeout' => 5, 'stream' => true, 'filename' => $tmpfname ) );

		if ( is_wp_error( $photo ) )
			return false;

		if ( 200 != wp_remote_retrieve_response_code( $photo ) )
			return false;

		$file = array(
			'name' => $filename,
			'type' => $photo['headers']['content-type'],
			'tmp_name' => $tmpfname,
			'error' => 0,
			'size' => $photo['headers']['content-length']
		);

		$overrides = array(
			// tells WordPress to not look for the POST form
			// fields that would normally be present, default is true,
			// we downloaded the file from a remote server, so there
			// will be no form fields
			'test_form' => false,

			// setting this to false lets WordPress allow empty files, not recommended
			'test_size' => true,

			// A properly uploaded file will pass this test.
			// There should be no reason to override this one.
			'test_upload' => true,
		);

		$attachment = wp_handle_sideload( $file, $overrides, date( 'Y/m', strtotime( $photo['headers']['date'] ) ) );

		if ( !empty( $attachment['error'] ) ) {
			return false;
		}

		$this->attachment = $attachment;

		return true;

	}

	public function attach_to( $post_id, $title = null ) {

		$post = get_post( $post_id );

		if ( empty( $post ) ) {
			return false;
		}

		if ( !$attachment = $this->get_attachment() ) {
			return false;
		}

		$filetype = wp_check_filetype( basename( $attachment['file'] ), null );

		if ( !$title ) {
			$title = sprintf( __( '%s Thumbnail', 'featured-media' ), $post->post_title );
		}

		$postinfo = array(
			'post_mime_type' => $filetype['type'],
			'post_title'     => $title,
			'post_status'    => 'inherit',
			'post_content'   => '',
		);
		$attach_id = wp_insert_attachment( $postinfo, $attachment['file'], $post->ID );

		if ( !function_exists( 'wp_generate_attachment_data' ) ) {
			require_once ABSPATH . 'wp-admin/includes/image.php';
		}

		wp_update_attachment_metadata( $attach_id, wp_generate_attachment_metadata( $attach_id, $attachment['file'] ) );

		return $this->attachment_id = $attach_id;

	}

	public function get_attachment() {
		return $this->attachment;
	}

	public function get_attachment_ID() {
		return $this->attachment_id;
	}

}
