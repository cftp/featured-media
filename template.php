<?php

/**
 * example usage:
 * 
 *   if ( has_featured_media_video() ) {
 *       $video = get_featured_media_video();
 *       echo $video->get_embed_code( array(
 *           'width'  => 400,
 *           'height' => 300
 *       ) );
 *   }
 * 
 */
function has_featured_media_video( $post_id = null ) {
	$video = new \FeaturedMedia\PostVideo( $post_id );
	$url = $video->get_url();
	return !empty( $url );
}

function get_featured_media_video( $post_id = null ) {
	if ( has_featured_media_video( $post_id ) )
		return new \FeaturedMedia\PostVideo( $post_id );
	else
		return null;
}

function has_featured_media_gallery( $post_id = null ) {
	/* **************** */
	return false;
	/* **************** */
	$gallery = new \FeaturedMedia\PostGallery( $post_id );
	$images = $gallery->get_gallery();
	return !empty( $images );
}

function get_featured_media_gallery( $post_id = null ) {
	if ( has_featured_media_gallery( $post_id ) )
		return new \FeaturedMedia\PostGallery( $post_id );
	else
		return null;
}

function has_featured_media_thumbnail( $post_id ) {
	return has_post_thumbnail( $post_id );
}

function get_featured_media_thumbnail( $post_id, $size = null ) {
	return get_the_post_thumbnail( $post_id, $size );
}
