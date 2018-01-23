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

// Video model

var FeaturedMediaModel = Backbone.Model.extend({

	initialize : function() {
		this.on( 'change:imported_thumbnail_id', this.setThumb, this );
	},

	setVideo : function( url ) {

		this.set( 'video_url', url );
		this.trigger( 'doLoading' );

		jQuery.post( ajaxurl, {

			action  : 'featured_media_fetch',
			url     : url,
			post_id : this.get('post').id

		}, _.bind( function( response ) {

			this.trigger( 'doLoaded' );

			if ( response.success ) {

				// We could short-circuit this with `this.set( response.data )` but the below adds clarity
				if ( response.data.title )
					this.set( 'video_title', response.data.title );
				if ( response.data.html )
					this.set( 'video_html', response.data.html );
				if ( response.data.featured_image_url )
					this.set( 'featured_image_url', response.data.featured_image_url );
				if ( response.data.featured_image_id )
					this.set( 'featured_image_id', response.data.featured_image_id );
				if ( response.data.imported_thumbnail_id )
					this.set( 'imported_thumbnail_id', response.data.imported_thumbnail_id );

			} else if ( response.data.error_message ) {

				this.trigger( 'doError', response.data.error_message );

			}

		}, this ), 'json' );

	},

	setThumb : function( model, value ) {

		wp.media.featuredMediaImage.set( value );

	}

});

// Video input view

var VideoInput = Backbone.View.extend({

	events : {
		'click [data-set-feature="video"]'       : 'doToggle',
		'paste :input[data-for-feature="video"]' : 'doChange',
		'drop :input[data-for-feature="video"]'  : 'doChange'
	},

	initialize : function ( options ) {

		this.model.on( 'doLoading', this.doLoading, this );
		this.model.on( 'doLoaded',  this.doLoaded,  this );
		this.model.on( 'doError',   this.doError,   this );

	},

	doToggle : function( event ) {

		event.preventDefault();

		this.$(':input[data-for-feature="video"]').toggle().focus();

	},

	doChange : function( event ) {

		_.defer( _.bind( function( target ) {

			var url = this.$( target ).val();

			if ( !url )
				return;

			this.model.setVideo( url );

		}, this ), [ event.currentTarget ] );

	},

	doLoading : function() {
		this.$( '.spinner' ).show();
	},

	doLoaded : function() {
		this.$( '.spinner' ).hide();
		this.$(':input[data-for-feature="video"]').hide();
	},

	doError : function( message ) {
		alert( message );
	}

});

// Video thumbnail view

var VideoThumbnail = Backbone.View.extend({

	events : {
		'click .fm_thumbnail_x' : 'doRemove'
	},

	initialize : function ( options ) {

		this.model.on( 'change:featured_image_url', this.setThumbnail, this );

	},

	setThumbnail : function( model, value ) {
		if ( -1 !== value ) {
			var img = jQuery('<img>').attr('src',value);
			this.$( '.fm_thumbnail' ).show().find('.fm').html( img );
			this.$('#featured-media-set').removeClass('no_fm');
		} else {
			this.$( '.fm_thumbnail' ).hide().find('.fm').html('');
		}
	},

	doRemove : function( event ) {
		wp.media.featuredMediaImage.set(-1);
		event.preventDefault();
	}

});

// Gallery view

var GalleryPreview = Backbone.View.extend({

	events : {
		'click .fm_gallery_x' : 'doRemove'
	},

	initialize : function ( options ) {

		this.model.on( 'change:featured_gallery_items', this.setGallery, this );

	},

	setGallery : function( model, value ) {

		// Remove existing slides if necessary:
		var fm = this.$( '.fm_gallery' ).find('.fm'),
			size;
		fm.empty();

		// Append hidden inputs:
		for ( var key in value ) {
			// append
			fm.append('<input type="hidden" name="featured-media-gallery[]" value="'+value[key].id+'">');
		}

		// Append thumbnail (use `medium` or `full` size though as `thumbnail` isn't big enough:
		if ( value[0].sizes.medium )
			size = value[0].sizes.medium;
		else
			size = value[0].sizes.full;

		fm.append('<img src="'+size.url+'" alt="">');
		this.$( '.fm_gallery' ).show();

	},

	doRemove : function( event ) {
		this.$( '.fm_gallery' ).find('.fm').empty();
		wp.media.featuredMediaImage.set(-1);
		event.preventDefault();
	}

});

// Video preview view

var VideoPreview = Backbone.View.extend({

	events : {
		'click .fm_previewer_x' : 'doRemove'
	},

	initialize : function ( options ) {
		this.model.on( 'change:video_html', this.setPreview, this );
	},

	doRemove : function( event ) {
		this.$(':input[data-for-feature="video"]').val('').hide();
		this.$('.fm_previewer').hide().find('.fm').html( '' );
		event.preventDefault();
	},

	setPreview : function( model, value ) {
		this.$('.fm_previewer').show().find('.fm').html( value );
		this.$('#featured-media-set').removeClass('no_fm');
	}

});

jQuery( function( $ ) {

	var model = new FeaturedMediaModel({
		post : featuredmedia.post
	});

	wp.media.featuredMediaImage = _.extend(wp.media.featuredImage,{

		init: function() {
			// Open the content media manager to the 'featured image' tab
			$('[data-set-feature="thumbnail"]').on( 'click', function( event ) {
				event.preventDefault();
				wp.media.featuredMediaImage.frame().open();
			});
		},

		set: function( id ) {
			var settings = wp.media.view.settings;

			settings.post.featuredImageId = id;

			wp.media.post( 'set-post-thumbnail', {
				json:         true,
				post_id:      settings.post.id,
				thumbnail_id: settings.post.featuredImageId,
				_wpnonce:     settings.post.nonce
			}).done( function( html ) {
				var img = $(html).find('img').attr('src');
				if ( img ) {
					model.set('featured_image_url',img);
					model.set('featured_image_id',id);
				} else {
					model.set('featured_image_url','');
					model.set('featured_image_id',0);
				}
			});
		}

	});

	$( wp.media.featuredMediaImage.init );

	wp.media.featuredMediaGallery = _.extend(wp.media.gallery,{

		init: function() {

			// Open the content media manager to the gallery tab
			// @TODO hide the tabs on the left hand side
			$('[data-set-feature="gallery"]').on( 'click', function( event ) {
				event.preventDefault();

				var gallery = wp.media.featuredMediaGallery,
					frame;

				if ( featuredmedia.post.gallery_items.length )
					frame = gallery.edit( '[gallery ids="' + featuredmedia.post.gallery_items.join(',') + '"]' );
				else
					frame = gallery.create();

				frame.state('gallery-edit').on( 'update', function( selection ) {

					var attachments = [];

					// Put the selection objects into an array.
					selection.map( function( attachment ) {
						var att = attachment.toJSON();
						attachments.push( att );
					});

					model.set( 'featured_gallery_items', attachments );

				});

			});
		},

		create: function() {

			// Destroy the previous gallery frame.
			if ( this.frame )
				this.frame.dispose();

			// Store the current gallery frame.
			this.frame = wp.media({
				frame:     'post',
				state:     'gallery',
				title:     wp.media.view.l10n.createGalleryTitle,
				editing:   true,
				multiple:  true
			}).open();

			return this.frame;
		}

	});

	$( wp.media.featuredMediaGallery.init );

	new VideoInput( {
		model : model,
		el    : $( '#featured-media-container' )
	} );
	new VideoThumbnail( {
		model : model,
		el    : $( '#featured-media-container' )
	} );
	new VideoPreview( {
		model : model,
		el    : $( '#featured-media-container' )
	} );
	new GalleryPreview( {
		model : model,
		el    : $( '#featured-media-container' )
	} );

} );
