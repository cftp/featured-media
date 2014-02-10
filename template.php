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

/**
 * example usage:
 * 
 *   if ( has_featured_media_gallery() ) {
 *       $gallery = get_featured_media_gallery();
 *       foreach ( $gallery as $image ) {
 *           $src = wp_get_attachment_image_src( $image, 'medium' );
 *           printf( '<img src="%s" alt="">', $src[0] );
 *       }
 *   }
 * 
 */
function has_featured_media_gallery( $post_id = null ) {
	$gallery = get_featured_media_gallery( $post_id );
	return !empty( $gallery );
}

function get_featured_media_gallery( $post_id = null ) {
	$post = get_post( $post_id );
	$gallery_items = get_post_meta( $post->ID, '_featured-media-gallery', true );
	if ( empty( $gallery_items ) )
		return false;
	else
		return $gallery_items;
}

function has_featured_media_thumbnail( $post_id ) {
	return has_post_thumbnail( $post_id );
}

function get_featured_media_thumbnail( $post_id, $size = null ) {
	return get_the_post_thumbnail( $post_id, $size );
}
