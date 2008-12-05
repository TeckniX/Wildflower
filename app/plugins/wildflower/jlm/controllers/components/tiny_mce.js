$.jlm.addComponent('tinyMce', {

    startup: function() {
        if (typeof(tinyMCE) == 'object') {
            $('textarea.tinymce').each(function() {
                var id = $(this).attr('id');
                tinyMCE.execCommand("mceAddControl", true, id);
            });
        }
	},
	
	getConfig: function() {
	    var stylesheetUrl = $.jlm.base + '/css/tiny_mce.css';
	    return {
            mode: "none",
            theme: "advanced",
            // @TODO cleanup unneeded plugins
            plugins: "wfinsertimage,safari,style,paste,directionality,visualchars,nonbreaking,xhtmlxtras,inlinepopups",
            doctype: '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">',

            // Theme options
            theme_advanced_buttons1: "undo,redo,|,bold,italic,strikethrough,|,formatselect,|,bullist,numlist,|,outdent,indent,blockquote,|,link,unlink,wfinsertimage,|,charmap,code",
    		theme_advanced_buttons2: "",
    		theme_advanced_buttons3: "",
            theme_advanced_toolbar_location: "top",
            theme_advanced_toolbar_align: "left",
            theme_advanced_statusbar_location: "bottom",
            theme_advanced_resizing: true,
            theme_advanced_resize_horizontal: false,
    		theme_advanced_path: false,
            width: '100%',

            // URLs
            relative_urls: false,
            remove_script_host: true,
            document_base_url: $.jlm.base, // @TODO investage if this works as intended
            
            //
            content_css: stylesheetUrl
        };
	},
	
	insertImage: function(editor) {
	    // Append img browser
	    var browserEl = $('#image-browser');
	    var doBindAndLoad = false;
	    if (browserEl.size() == 0) {
	        doBindAndLoad = true;
	        browserEl = $($.jlm.element('image_browser')).hide();
	    } 
	    $('.title-input:first').after(browserEl);
	    
	    if (browserEl.css('display') == 'none') {
	        browserEl.slideDown(300);
	    } else {
	        // Already open, close
	        browserEl.slideUp(200);
	        return false;
	    }
	    
	    if (!doBindAndLoad) {
	        return false;
	    }
	    
	    // Load images
	    // @TODO: I want to do something like this:
	    // $.jlm.url({ plugin: 'wildflower', controller: 'wild_assets', action: 'wf_insert_image' });
	    var url = $.jlm.base + '/' + $.jlm.params.prefix + '/assets/insert_image';
	    
	    $.get(url, function(imagesHtml) {
	        areImagesLoaded = true;
	        var imagesHtmlEl = $(imagesHtml).hide();
            browserEl.prepend(imagesHtml);
            imagesHtmlEl.fadeIn('normal');
		});
	    
	    // Bind insert button
		$('button', browserEl).click(function() {
			var imgName = $('.selected', browserEl).attr('alt');
			
			if (typeof(imgName) == 'undefined') {
			    return false;
			}
			
            // Original size (scaled)
            var width, height;
            if (isNaN(width = $('#ImageResizeX', t.dialogEl).val())) {
                width = 0;
            }
            if (isNaN(height = $('#ImageResizeY', t.dialogEl).val())) {
                height = 0;
            }
            var imgUrl = 'img/thumb/' + imgName + '/' + width + '/' + height;
			
			// Thumbnail
            if ($('#ImageSize', t.dialogEl).val() == 'thumbnail') {
             imgUrl = 'img/thumb/' + imgName + '/120/120/1';
            }
			
			// Image HTML
			var imgHtml = '<img alt="' + imgName + '" src="' + imgUrl + '" />';
			
			editor.execCommand('mceInsertContent', 0, imgHtml);
			
			return false;
		});
		
		// Bind close
        $('.cancel', browserEl).click(function() {
            browserEl.slideUp(200);
            return false;
        });
        
        $.jlm.components.tinyMce.bindImageSelecting();
	    
	    return false;
	},
	
	bindImageSelecting: function() {
		// Bind selecting
        $('#image-browser ul img').click(function() {
            $(this).toggleClass('selected');
            $('#image-browser .selected').not(this).removeClass('selected');
        });
	},
	
	bindPaginator: function() {
		var t = this;
		$('#image-browser .paginate-page').click(function() {
            var url = $(this).attr('href');
            t.loaderEl.show();
            $('#image-browser').remove();
            $.get(url, function(data) {
                t.loaderEl.hide().before(data);
                // rebind select
                t.bindImageSelecting();
                // rebind pager
				t.bindPaginator();
            });
            return false;
        });
	},
	
	closeDialog: function() {
		$.jlm.components.tinyMce.dialogEl.remove();
	},
    
    insertLink: function() {
        log('INSERT LINK');
    }
});
