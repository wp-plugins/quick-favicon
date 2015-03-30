// Uploading files
	var file_frame;
	var wp_media_post_id = wp.media.model.settings.post.id; // Store the old id
	var set_to_post_id = 222222222; // Set this

	  jQuery('.icon-upload').live('click', function( event ){

	    element = jQuery(this);

	    event.preventDefault();

	    // If the media frame already exists, reopen it.
	    if ( file_frame ) {
	      // Set the post ID to what we want
	      file_frame.uploader.uploader.param( 'post_id', set_to_post_id );
	      // Open frame
	      file_frame.open();
	      return;
	    } else {
	      // Set the wp.media post id so the uploader grabs the ID we want when initialised
	      wp.media.model.settings.post.id = set_to_post_id;
	    }

	    // Create the media frame.
	    file_frame = wp.media.frames.file_frame = wp.media({
	      title: jQuery( this ).data( 'uploader_title' ),
	      button: {
	        text: jQuery( this ).data( 'uploader_button_text' ),
	      },
	      multiple: false  // Set to true to allow multiple files to be selected
	    });

	    // When an image is selected, run a callback.
	    file_frame.on( 'select', function() {
	      // We set multiple to false so only get one image from the uploader
	      attachment = file_frame.state().get('selection').first().toJSON();

	      // Do something with attachment.id and/or attachment.url here
        jQuery(element).closest('.options-section').find('.icon-image').children('.icon-image-inner').children('img').attr('src',attachment.url);
        jQuery(element).closest('.options-section').find('.icon-image').removeClass('hidden');
        jQuery(element).closest('.options-section').find('.icon-remove').removeClass('hidden');
        jQuery(element).closest('.options-section').find('.icon-upload').addClass('hidden');
        jQuery(element).closest('.options-section').find('.hidden-id-field').val(attachment.id);
        quickfavicon_generate_canvases();

	      // Restore the main post ID
	      wp.media.model.settings.post.id = wp_media_post_id;
	    });

	    // Finally, open the modal
	    file_frame.open();
	  });

	  // Restore the main ID when the add media button is pressed
	  jQuery('a.add_media').on('click', function() {
	    wp.media.model.settings.post.id = wp_media_post_id;
	  });

	  // Remove icon from section on click
	  jQuery('.icon-remove').on('click', function() {
      jQuery(this).closest('.options-section').find('.icon-image').addClass('hidden');
      jQuery(this).closest('.options-section').find('.icon-remove').addClass('hidden');
      jQuery(this).closest('.options-section').find('.icon-upload').removeClass('hidden');
      jQuery(this).closest('.options-section').find('.h2').children('span').addClass('hidden');
      jQuery(this).closest('.options-section').find('.hidden-id-field').val('');
	  });

	  // Track last tab used
	  jQuery('.setting-tab-pane').on('click', function() {
      jQuery('#quickfavicon_last_tab_used').val(jQuery(this).attr('data-tab-id'));
	  });

    // initialize color pickers
    jQuery('.colorpicker').colorpicker();

    jQuery('#quickfavicon_windows_tile_color').parent().colorpicker().on('changeColor.colorpicker', function(event){
      jQuery('.windows_icon_cell').children('.windows_icon_image_preview').css('background',event.color.toHex());
    });

    jQuery('#quickfavicon_android_icon_bg').parent().colorpicker().on('changeColor.colorpicker', function(event){
      jQuery('.android_icon_image_preview').css('background',event.color.toHex());
    });

    jQuery('#quickfavicon_ios_icon_bg').parent().colorpicker().on('changeColor.colorpicker', function(event){
      jQuery('.ios_icon_image_preview').css('background',event.color.toHex());
    });

    // Detect iOS icon bg color radio button change
    jQuery('input[type=radio][name=quickfavicon_ios_icon_bg_radio]').change(function() {
      if (this.value == 'white') {
        jQuery('input[type=hidden][name=quickfavicon_ios_icon_bg]').val('#ffffff');
        jQuery('input[type=text][name=quickfavicon_ios_icon_bg_visible]').prop('disabled', true);
        jQuery(this).closest('.options-section').find('.ios_icon_image_preview').css('background','#ffffff');
      }
      if (this.value == 'black') {
        jQuery('input[type=hidden][name=quickfavicon_ios_icon_bg]').val('#000000');
        jQuery('input[type=text][name=quickfavicon_ios_icon_bg_visible]').prop('disabled', true);
        jQuery(this).closest('.options-section').find('.ios_icon_image_preview').css('background','#000000');
      }
      if (this.value == 'custom') {
        jQuery('input[type=text][name=quickfavicon_ios_icon_bg_visible]').prop('disabled', false);
        jQuery(this).closest('.options-section').find('.ios_icon_image_preview').css('background',jQuery('#quickfavicon_ios_icon_bg').val());
      }
    });

    jQuery(document).ready(function() {
      jQuery('.ios_icon_image_preview').css('background',quick_favicon.quickfavicon_ios_icon_bg);
    });

    // Detect Android icon bg color radio button change
    jQuery('input[type=radio][name=quickfavicon_android_icon_bg_radio]').change(function() {
      if (this.value == 'white') {
        jQuery('input[type=hidden][name=quickfavicon_android_icon_bg]').val('#ffffff');
        jQuery('input[type=text][name=quickfavicon_android_icon_bg_visible]').prop('disabled', true);
        jQuery(this).closest('.options-section').find('.android_icon_image_preview').css('background','#ffffff');
      }
      if (this.value == 'black') {
        jQuery('input[type=hidden][name=quickfavicon_android_icon_bg]').val('#000000');
        jQuery('input[type=text][name=quickfavicon_android_icon_bg_visible]').prop('disabled', true);
        jQuery(this).closest('.options-section').find('.android_icon_image_preview').css('background','#000000');
      }
      if (this.value == 'transparent') {
        jQuery('input[type=hidden][name=quickfavicon_android_icon_bg]').val('');
        jQuery('input[type=text][name=quickfavicon_android_icon_bg_visible]').prop('disabled', true);
        jQuery(this).closest('.options-section').find('.android_icon_image_preview').css('background','');
      }
      if (this.value == 'custom') {
        jQuery('input[type=text][name=quickfavicon_android_icon_bg_visible]').prop('disabled', false);
        jQuery(this).closest('.options-section').find('.android_icon_image_preview').css('background',jQuery('#quickfavicon_android_icon_bg').val());
      }
    });

    jQuery(document).ready(function() {
      jQuery('.android_icon_image_preview').css('background',quick_favicon.quickfavicon_android_icon_bg);
    });

	  // Detect standard windows tile color clicks
	  jQuery('.windows_tile_label').on('click', function() {
      jQuery('#quickfavicon_windows_tile_color').parent().colorpicker('setValue', jQuery(this).attr('data-tile-color'));
      jQuery('.preview-windows').find('.windows_icon_image_preview').css('background',jQuery(this).attr('data-tile-color'));
	  });

    // Detect Windows icon style radio button change
    jQuery('input[type=radio][name=quickfavicon_windows_icon_style]').change(function() {
      if (this.value == 'white') {
        jQuery('.windows_sizes').find('.windows_icon_image_preview').addClass('windows_icon_image_preview_dark');
        quickfavicon_generate_canvases();
      }
      if (this.value == 'asis') {
        jQuery('.windows_sizes').find('.windows_icon_image_preview').removeClass('windows_icon_image_preview_dark');
        quickfavicon_generate_canvases();
      }
    });

    jQuery(document).ready(function() {
      jQuery('.preview-windows').find('.windows_icon_image_preview').css('background',quick_favicon.quickfavicon_windows_tile_color);
      if (quick_favicon.quickfavicon_windows_icon_style == 'white') {
        jQuery('.windows_sizes').find('.windows_icon_image_preview').addClass('windows_icon_image_preview_dark');
      }
      quickfavicon_generate_canvases();
    });

    function quickfavicon_generate_canvases() {

      // Get the source image
      var image1 = document.getElementById("quickfavicon_white_mask_source");

      // Step 1
      var canvas1 = document.getElementById("hidden_canvas_1");
      var context1 = canvas1.getContext("2d");
      canvas1.width = image1.width;
      canvas1.height = image1.height;
      context1.drawImage(image1, 0, 0, canvas1.width, canvas1.height);

      // Step 2
      var canvas2 = document.getElementById("hidden_canvas_2");
      var context2 = canvas2.getContext("2d");
      canvas2.width = image1.width * 0.6;
      canvas2.height = image1.height * 0.6;
      context2.drawImage(canvas1, 0, 0, canvas2.width, canvas2.height);

      // Step 3
      var canvas3 = document.getElementById("hidden_canvas_3");
      var context3 = canvas3.getContext("2d");
      canvas3.width = canvas2.width * 0.6;
      canvas3.height = canvas2.height * 0.6;
      context3.drawImage(canvas2, 0, 0, canvas3.width, canvas3.height);

      // Step 4
      var canvas4 = document.getElementById("hidden_canvas_4");
      var context4 = canvas4.getContext("2d");
      canvas4.width = canvas3.width * 0.6;
      canvas4.height = canvas3.height * 0.6;
      context4.drawImage(canvas3, 0, 0, canvas4.width, canvas4.height);

      // Step 5
      var canvas5 = document.getElementById("hidden_canvas_5");
      var context5 = canvas5.getContext("2d");
      canvas5.width = canvas4.width * 0.6;
      canvas5.height = canvas4.height * 0.6;
      context5.drawImage(canvas4, 0, 0, canvas5.width, canvas5.height);

      // Step 6
      var canvas6 = document.getElementById("hidden_canvas_6");
      var context6 = canvas6.getContext("2d");
      canvas6.width = canvas5.width * 0.6;
      canvas6.height = canvas5.height * 0.6;
      context6.drawImage(canvas5, 0, 0, canvas6.width, canvas6.height);

      // Step 7
      var canvas7 = document.getElementById("hidden_canvas_7");
      var context7 = canvas7.getContext("2d");
      canvas7.width = canvas6.width * 0.6;
      canvas7.height = canvas6.height * 0.6;
      context7.drawImage(canvas6, 0, 0, canvas7.width, canvas7.height);

      // Step 8
      var canvas8 = document.getElementById("hidden_canvas_8");
      var context8 = canvas8.getContext("2d");
      canvas8.width = canvas7.width * 0.6;
      canvas8.height = canvas7.height * 0.6;
      context8.drawImage(canvas7, 0, 0, canvas8.width, canvas8.height);

      // Step 9
      var canvas9 = document.getElementById("hidden_canvas_9");
      var context9 = canvas9.getContext("2d");
      canvas9.width = canvas8.width * 0.6;
      canvas9.height = canvas8.height * 0.6;
      context9.drawImage(canvas8, 0, 0, canvas9.width, canvas9.height);

      // Step 10
      var canvas10 = document.getElementById("hidden_canvas_10");
      var context10 = canvas10.getContext("2d");
      canvas10.width = canvas9.width * 0.6;
      canvas10.height = canvas9.height * 0.6;
      context10.drawImage(canvas9, 0, 0, canvas10.width, canvas10.height);

      jQuery('.win-canvas').each(function() {
        var newcanvas = this;
        var width = jQuery(newcanvas).attr('width');
        var height = jQuery(newcanvas).attr('height');
        var context = newcanvas.getContext("2d");
        newcanvas.width = width;
        newcanvas.height = height;

        if (width < canvas10.width) {
        context.drawImage(canvas10, 0, 0, newcanvas.width, newcanvas.height);
        }
        else if (width < canvas9.width) {
        context.drawImage(canvas9, 0, 0, newcanvas.width, newcanvas.height);
        }
        else if (width < canvas8.width) {
        context.drawImage(canvas8, 0, 0, newcanvas.width, newcanvas.height);
        }
        else if (width < canvas7.width) {
        context.drawImage(canvas7, 0, 0, newcanvas.width, newcanvas.height);
        }
        else if (width < canvas6.width) {
        context.drawImage(canvas6, 0, 0, newcanvas.width, newcanvas.height);
        }
        else if (width < canvas5.width) {
        context.drawImage(canvas5, 0, 0, newcanvas.width, newcanvas.height);
        }
        else if (width < canvas4.width) {
        context.drawImage(canvas4, 0, 0, newcanvas.width, newcanvas.height);
        }
        else if (width < canvas3.width) {
        context.drawImage(canvas3, 0, 0, newcanvas.width, newcanvas.height);
        }
        else if (width < canvas2.width) {
        context.drawImage(canvas2, 0, 0, newcanvas.width, newcanvas.height);
        }
        else {
        context.drawImage(canvas1, 0, 0, newcanvas.width, newcanvas.height);
        }
        if (jQuery('#quickfavicon_windows_icon_style').is(':checked')) {
          var imgd = context.getImageData(0, 0, width, height);
          var pix = imgd.data;
          var blackpixel = 255;
          for (var i = 0, n = pix.length; i < n; i += 4) {
            pix[i  ] = blackpixel;
            pix[i+1] = blackpixel;
            pix[i+2] = blackpixel;
          }
          context.putImageData(imgd, 0, 0);
        }
      });

    }
