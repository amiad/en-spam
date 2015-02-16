<?php
/*
Plugin Name: En Spam
Description: Block Spam with Cookies and JavaScript Filtering.
Plugin URI: http://hatul.info/en-spam
Version: 0.7.1
Author: Hatul
Author URI: http://hatul.info
License: GPL http://www.gnu.org/copyleft/gpl.html
*/

add_filter('preprocess_comment','ens_check_comment');
function ens_check_comment($comment){
	if ((isset($_COOKIE['comment_author_email_' . COOKIEHASH]))
	  || (is_user_logged_in())
	  || ($_POST['code'] == get_option('ens_code'))) {
	  	$comment['comment_content'] = stripcslashes($comment['comment_content']);
		return $comment;
	}
	else ens_block_page();
}

function ens_block_page(){
	$counter = get_option('ens_counter', 0) + 1;
	update_option('ens_counter', $counter);

	$message = sprintf(__('For to post comment, you need to enable cookies and JavaScript or to click on "%s" button in this page', 'en-spam'), __( 'Post Comment' ));
	$message .= '<form method="post">';
	foreach ($_POST as $name=>$value){
		if ($name == 'comment')
			$message .= sprintf('<label for="comment">%s</label><br /><textarea id="comment" name="comment">%s</textarea><br />',__('Your comment:', 'en-spam'), $value);
		else
			$message .= sprintf('<input type="hidden" name="%s" value="%s" />', $name, stripcslashes($value));
	}
	$message .= sprintf('<input type="hidden" name="code" value="%s" />', get_option('ens_code'));
	$message .= sprintf('<input type="submit" name="submit" value="%s" />', __( 'Post Comment' ));
	$message .= '</form>';

	wp_die($message);
}


//random code in activation the plugin
register_activation_hook(__FILE__, 'ens_init_code');
function ens_init_code(){
	update_option('ens_code', rand(10000, 99999));
}

add_action('wp_enqueue_scripts', function(){
	wp_register_script('en-spam', plugins_url('en-spam.js', __FILE__), array('jquery'));
	wp_localize_script('en-spam', 'data', array('hash'=>COOKIEHASH));
	wp_enqueue_script('en-spam');
});

function ens_add_dashboard_widgets() {
	wp_add_dashboard_widget(
                 'en_spam_dashboard_widget',
                 __('Blocked Spambots by En Spam', 'en-spam'),
                 'ens_dashboard_widget_function'
        );
}
add_action( 'wp_dashboard_setup', 'ens_add_dashboard_widgets' );
function ens_dashboard_widget_function() {
	echo get_option('ens_counter');
}

load_plugin_textdomain('en-spam', false, dirname( plugin_basename( __FILE__ ) ) );
