﻿/**
 * @license Copyright (c) 2003-2013, CKSource - Frederico Knabben. All rights reserved.
 * For licensing, see LICENSE.html or http://ckeditor.com/license
 */

/**
 * @fileOverview Image plugin
 */

(function() {

	CKEDITOR.plugins.add( 'image', {
		requires: 'dialog',
		lang: 'af,ar,bg,bn,bs,ca,cs,cy,da,de,el,en-au,en-ca,en-gb,en,eo,es,et,eu,fa,fi,fo,fr-ca,fr,gl,gu,he,hi,hr,hu,is,it,ja,ka,km,ko,ku,lt,lv,mk,mn,ms,nb,nl,no,pl,pt-br,pt,ro,ru,sk,sl,sr-latn,sr,sv,th,tr,ug,uk,vi,zh-cn,zh', // %REMOVE_LINE_CORE%
		icons: 'image', // %REMOVE_LINE_CORE%
		init: function( editor ) {
			var pluginName = 'image';

			// Register the dialog.
			CKEDITOR.dialog.add( pluginName, this.path + 'dialogs/image.js' );

			// Register the command.
			editor.addCommand( pluginName, new CKEDITOR.dialogCommand( pluginName ) );

			// Register the toolbar button.
			editor.ui.addButton && editor.ui.addButton( 'Image', {
				label: editor.lang.common.image,
				command: pluginName,
				toolbar: 'insert,10'
			});

			editor.on( 'doubleclick', function( evt ) {
				var element = evt.data.element;

				if ( element.is( 'img' ) && !element.data( 'cke-realelement' ) && !element.isReadOnly() )
					evt.data.dialog = 'image';
			});

			// If the "menu" plugin is loaded, register the menu items.
			if ( editor.addMenuItems ) {
				editor.addMenuItems({
					image: {
						label: editor.lang.image.menu,
						command: 'image',
						group: 'image'
					}
				});
			}

			// If the "contextmenu" plugin is loaded, register the listeners.
			if ( editor.contextMenu ) {
				editor.contextMenu.addListener( function( element, selection ) {
					if ( getSelectedImage( editor, element ) )
						return { image: CKEDITOR.TRISTATE_OFF };
				});
			}
		},
		afterInit: function( editor ) {
			// Customize the behavior of the alignment commands. (#7430)
			setupAlignCommand( 'left' );
			setupAlignCommand( 'right' );
			setupAlignCommand( 'center' );
			setupAlignCommand( 'block' );

			function setupAlignCommand( value ) {
				var command = editor.getCommand( 'justify' + value );
				if ( command ) {
					if ( value == 'left' || value == 'right' ) {
						command.on( 'exec', function( evt ) {
							var img = getSelectedImage( editor ),
								align;
							if ( img ) {
								align = getImageAlignment( img );
								if ( align == value ) {
									img.removeStyle( 'float' );

									// Remove "align" attribute when necessary.
									if ( value == getImageAlignment( img ) )
										img.removeAttribute( 'align' );
								} else
									img.setStyle( 'float', value );

								evt.cancel();
							}
						});
					}

					command.on( 'refresh', function( evt ) {
						var img = getSelectedImage( editor ),
							align;
						if ( img ) {
							align = getImageAlignment( img );

							this.setState(
							( align == value ) ? CKEDITOR.TRISTATE_ON : ( value == 'right' || value == 'left' ) ? CKEDITOR.TRISTATE_OFF : CKEDITOR.TRISTATE_DISABLED );

							evt.cancel();
						}
					});
				}
			}
		}
	});

	function getSelectedImage( editor, element ) {
		if ( !element ) {
			var sel = editor.getSelection();
			element = sel.getSelectedElement();
		}

		if ( element && element.is( 'img' ) && !element.data( 'cke-realelement' ) && !element.isReadOnly() )
			return element;
	}

	function getImageAlignment( element ) {
		var align = element.getStyle( 'float' );

		if ( align == 'inherit' || align == 'none' )
			align = 0;

		if ( !align )
			align = element.getAttribute( 'align' );

		return align;
	}

})();

/**
 * Whether to remove links when emptying the link URL field in the image dialog.
 *
 *		config.image_removeLinkByEmptyURL = false;
 *
 * @cfg {Boolean} [image_removeLinkByEmptyURL=true]
 * @member CKEDITOR.config
 */
CKEDITOR.config.image_removeLinkByEmptyURL = true;

/**
 * Padding text to set off the image in preview area.
 *
 *		config.image_previewText = CKEDITOR.tools.repeat( '___ ', 100 );
 *
 * @cfg {String} [image_previewText='Lorem ipsum dolor...' (placeholder text)]
 * @member CKEDITOR.config
 */
