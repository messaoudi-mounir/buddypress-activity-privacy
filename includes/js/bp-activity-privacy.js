if ( typeof jq == "undefined" )
	var jq = jQuery;


jq(document).ready( function() {
	
	jq('span#activity-visibility').prependTo('div#whats-new-submit');
	jq("input#aw-whats-new-submit").off("click");

	var selected_item_id = jq("select#whats-new-post-in").val();
	jq("select#whats-new-post-in").data('selected', selected_item_id );
	//if selected item is not 'My profil'
	if( selected_item_id != 0 )
		jq('select#activity-privacy').replaceWith(visibility_levels.groups);

	jq("select#whats-new-post-in").on("change", function() {
		var old_selected_item_id = jq(this).data('selected');
		var item_id = jq("#whats-new-post-in").val();

		if(item_id == 0 && item_id != old_selected_item_id){
			jq('select#activity-privacy').replaceWith(visibility_levels.profil);
		}else{
			if(item_id != 0 && old_selected_item_id == 0 ){
				jq('select#activity-privacy').replaceWith(visibility_levels.groups);
			}
		}

		jq(this).data('selected',item_id);
	});
	
	/* New posts */
	jq("input#aw-whats-new-submit").on('click', function() {
		var button = jq(this);
		var form = button.parent().parent().parent().parent();

		form.children().each( function() {
			if ( jq.nodeName(this, "textarea") || jq.nodeName(this, "input") )
				jq(this).prop( 'disabled', true );
		});

		/* Remove any errors */
		jq('div.error').remove();
		button.addClass('loading');
		button.prop('disabled', true);

		/* Default POST values */
		var object = '';
		var item_id = jq("#whats-new-post-in").val();
		var content = jq("textarea#whats-new").val();
		var visibility = jq("select#activity-privacy").val();

		/* Set object for non-profile posts */
		if ( item_id > 0 ) {
			object = jq("#whats-new-post-object").val();
		}

		jq.post( ajaxurl, {
			action: 'post_update',
			'cookie': encodeURIComponent(document.cookie),
			'_wpnonce_post_update': jq("input#_wpnonce_post_update").val(),
			'content': content,
			'visibility': visibility,
			'object': object,
			'item_id': item_id,
			'_bp_as_nonce': jq('#_bp_as_nonce').val() || ''
		},
		function(response) {

			form.children().each( function() {
				if ( jq.nodeName(this, "textarea") || jq.nodeName(this, "input") ) {
					jq(this).prop( 'disabled', false );
				}
			});

			/* Check for errors and append if found. */
			if ( response[0] + response[1] == '-1' ) {
				form.prepend( response.substr( 2, response.length ) );
				jq( 'form#' + form.attr('id') + ' div.error').hide().fadeIn( 200 );
			} else {
				if ( 0 == jq("ul.activity-list").length ) {
					jq("div.error").slideUp(100).remove();
					jq("div#message").slideUp(100).remove();
					jq("div.activity").append( '<ul id="activity-stream" class="activity-list item-list">' );
				}

				jq("ul#activity-stream").prepend(response);
				jq("ul#activity-stream li:first").addClass('new-update');

				if ( 0 != jq("#latest-update").length ) {
					var l = jq("ul#activity-stream li.new-update .activity-content .activity-inner p").html();
					var v = jq("ul#activity-stream li.new-update .activity-content .activity-header p a.view").attr('href');

					var ltext = jq("ul#activity-stream li.new-update .activity-content .activity-inner p").text();

					var u = '';
					if ( ltext != '' )
						u = l + ' ';

					u += '<a href="' + v + '" rel="nofollow">' + BP_DTheme.view + '</a>';

					jq("#latest-update").slideUp(300,function(){
						jq("#latest-update").html( u );
						jq("#latest-update").slideDown(300);
					});
				}

				jq("li.new-update").hide().slideDown( 300 );
				jq("li.new-update").removeClass( 'new-update' );
				jq("textarea#whats-new").val('');
			}

			jq("#whats-new-options").animate({
				height:'0px'
			});
			jq("form#whats-new-form textarea").animate({
				height:'20px'
			});
			jq("#aw-whats-new-submit").prop("disabled", true).removeClass('loading');

			//reset the select
			jq("select#activity-privacy option[selected]").prop('selected', true);
		});

		return false;
	});

});
