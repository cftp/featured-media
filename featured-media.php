<?php
/*
Plugin Name: Featured Media
Plugin URI:  https://github.com/cftp/featured-media
Description: Use an image, video, or gallery for your featured media
Author:      Code For The People
Version:     1.1.1
Author URI:  http://codeforthepeople.com/
Text Domain: featured-media
Domain Path: /assets/languages/
License:     GPL v2 or later

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

defined( 'ABSPATH' ) or die();

function featured_media_autoloader( $class ) {

	if ( false === strpos( $class, 'FeaturedMedia' ) )
		return;

	$name = ltrim( $class, '\\' );
	$name = str_replace( array( '\\', '_' ), '/', $name );
	$name = preg_replace( '|^FeaturedMedia/|', '', $name );

	$file = sprintf( '%1$s/%2$s.php',
		dirname( __FILE__ ),
		$name
	);

	if ( is_readable( $file ) )
		include $file;

}

spl_autoload_register( 'featured_media_autoloader' );

require_once dirname( __FILE__ ) . '/template.php';

# Go!
\FeaturedMedia\FeaturedMedia::init( __FILE__ );
