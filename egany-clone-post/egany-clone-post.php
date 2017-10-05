<?php
/**
 * Plugin Name: Egany Clone Post
 * Description: Clone post from one site to another site
 * Author: Egany
 * Author URI: http://egany.com
 * Version: 0.1
 */

function add_events_metaboxes() {
    add_meta_box('clone_post_to_another_site', 'Clone post to another site', 'chc_mata_box_cb', 'post', 'side', 'default');
}
add_action( 'add_meta_boxes', 'add_events_metaboxes' );
function chc_mata_box_cb()
{
    global $post;
    // Noncename needed to verify where the data originated
    echo '<input type="hidden" name="eventmeta_noncename" id="eventmeta_noncename" value="' .
        wp_create_nonce( plugin_basename(__FILE__) ) . '" />';

    echo "<button id='chc_open_popup' class='button button-primary' >Open Editor</button>";
    echo "<style>
		#chc_editor{display:none;
					position: fixed; top:300px;z-index: 9999;
				    top: 0;
				    left: 0;
				    width: 100%;
				    text-align:center;}
		#chc_modal{ position: relative;
				    text-align:left; display: inline-block; min-width: 700px; background-color: #fff;
				     padding: 20px; margin-top:40px; border:1px solid #ccc;
				     -webkit-box-shadow: 0px 0px 5px 0px rgba(0,0,0,0.75);
					-moz-box-shadow: 0px 0px 5px 0px rgba(0,0,0,0.75);
					box-shadow: 0px 0px 5px 0px rgba(0,0,0,0.75);
				 }
		#chc_modal > h4{text-align:center; margin:0; margin-bottom: 10px;}
		#chc_close_modal{
				     position: absolute;
				    top: 5px;
				    right: 10px;
				}	
		#chc_actions{margin:10px 0;}
        #chc_post_title{    width: 100%;margin-bottom: 10px;}
        #chc_msg{text-align: center; margin: 8px 0;}

	</style>";
    echo "<div id='chc_editor' >";
    echo "<div id='chc_modal'> <h4>Clone post to another site</h4>";
    echo "<a id='chc_close_modal' href='javascript:void(0)'>close</a>";

    echo "<input data-error='title' data-chcrequire='1' id='chc_post_title' value='".esc_html($post->post_title)."' />";
    wp_editor($post->post_content , 'chc_editor_content', array('editor_height'=> '200') );

    echo "<div id='chc_actions'>
		<label>Site:</label>
		<input data-chcrequire='1' data-error='site' id='chc_site' placeholder='http://cayphuong.com' />
		<input data-chcrequire='1' data-error='username' id='chc_username' placeholder='username' />
		<input data-chcrequire='1' data-error='password' id='chc_password' placeholder='password' type='password' />
		<button  id='chc_clone' class='button button-primary' >Clone</button>
	</div>";

    echo "<div id='chc_msg'></div>";

    echo "</div>";
    echo "</div>";

    echo "<script>
	   
		jQuery('#chc_close_modal').click(function(){
				jQuery('#chc_editor').hide();
		});
		jQuery('#chc_open_popup').click(function(e){
			e.preventDefault();
			jQuery('#chc_editor').show();
			return false;
		}); 
		//clone post to site
		jQuery('#chc_clone').click(function(e){
			e.preventDefault();
			var c = tinyMCE.activeEditor.getContent();
			var title = jQuery('#chc_post_title').val();
			var site = jQuery('#chc_site').val();
			var uname = jQuery('#chc_username').val();
			var pass = jQuery('#chc_password').val();
			//console.log(title+' '+site+' '+uname+' '+pass);
			var is_error = false;
			jQuery('#chc_msg').text('');
			jQuery('#chc_modal input').each(function() {
			    if(jQuery(this).data('chcrequire')=='1')
			        {
			            if(!jQuery(this).val())
                        {
                            is_error = true;
                            jQuery('#chc_msg').text('Please fill out the '+jQuery(this).data('error') + ' field');
                            return;
                        }
			        }
			});
			var data_post = {
			    'title': title,
			    'content':c,
			    'status':'publish'
			};
			var encode = btoa(uname + ':' + pass);
			if(!is_error)
			    {
			    	jQuery('#chc_msg').text('Cloning....');
			        jQuery.ajax({
			            url: site + '/wp-json/wp/v2/posts',
			            type:'POST',
			            contentType: 'application/json',
			            crossDomain: true,
			            data:JSON.stringify(data_post),
			            beforeSend: function ( xhr ) {
                            xhr.setRequestHeader( 'Authorization', 'Basic '+encode );
                        }
			        }).done(function(d) {
			            jQuery('#chc_msg').text('Clone success!');
			        }).fail(function(err) {
			        	console.log(JSON.stringify(err));
			            jQuery('#chc_msg').text('Error! May be invalid username or password. May be site field incorrect pattern: http://domain.dot (no trailing slash)');
			        });
			    }
			
			return false;
		}); 
		</script>";
}