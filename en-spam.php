<?php
/*
Plugin Name: En Spam
Description: Block Spam with Cookies and JavaScript Filtering.
Plugin URI: http://hatul.info/en-spam
Version: 1.0
Author: Hatul
Author URI: http://hatul.info
License: GPL http://www.gnu.org/copyleft/gpl.html
*/

load_plugin_textdomain('en-spam', false, dirname( plugin_basename( __FILE__ ) ) );

add_filter('preprocess_comment','ens_check_comment');
function ens_check_comment($comment){
	if (ens_valid_no_bot() || wp_verify_nonce($_POST['code'], 'en-spam')) {
	  	$comment['comment_content'] = stripcslashes($comment['comment_content']);
		return $comment;
	}
	else ens_block_page();
}

function ens_block_page(){
	ens_count_block();

	$message = sprintf(__('For to post comment, you need to enable cookies and JavaScript or to click on "%s" button in this page', 'en-spam'), __( 'Post Comment' ));
	$message .= '<form method="post">';
	foreach ($_POST as $name=>$value){
		if ($name == 'comment')
			$message .= sprintf('<label for="comment">%s</label><br /><textarea id="comment" name="comment">%s</textarea><br />',__('Your comment:', 'en-spam'), $value);
		else
			$message .= sprintf('<input type="hidden" name="%s" value="%s" />', $name, stripcslashes($value));
	}
	$message .= sprintf('<input type="hidden" name="code" value="%s" />', wp_create_nonce('en-spam'));
	$message .= sprintf('<input type="submit" name="submit" value="%s" />', __( 'Post Comment' ));
	$message .= '</form>';

	wp_die($message);
}

function ens_count_block(){
	$counter = get_option('ens_counter', 0) + 1;
	update_option('ens_counter', $counter);
}

add_action('wp_enqueue_scripts', 'ens_scripts');
function ens_scripts() {
	wp_enqueue_script('en-spam', plugins_url('en-spam.js', __FILE__));
}

function ens_add_dashboard_widgets() {
	wp_add_dashboard_widget(
                 'en_spam_dashboard_widget',
                 __('Blocked Spambots by En Spam', 'en-spam'),
                 'ens_dashboard_widget_function'
        );
}

add_action( 'wp_dashboard_setup', 'ens_add_dashboard_widgets' );
function ens_dashboard_widget_function() {
	echo get_option('ens_counter', 0);
}

function ens_valid_no_bot(){
	return isset($_COOKIE['comment_author_email_' . COOKIEHASH])
	|| is_user_logged_in()
	|| isset($_COOKIE['en_spam_validate']);
}

// CF7
function ens_check_cf7($form, &$abort, $submission) {
    if (! ens_valid_no_bot()) {
        $abort = true;
		$submission->set_status( 'validation_failed' );
		$submission->set_response($form->filter_message(__('Enable JavaScript and cookies', 'en-spam')));
		
		ens_count_block();
    }
}
add_action( 'wpcf7_before_send_mail', 'ens_check_cf7', 10, 3);

// Elementor Form
function ens_check_elementor_form($record, $ajax_handler) {
	if (! ens_valid_no_bot()){
		$ajax_handler->add_error('field_id', __('Enable JavaScript and cookies', 'en-spam')); // stop sending
		$ajax_handler->add_error_message(__('Enable JavaScript and cookies', 'en-spam'));
	}
}
add_action( 'elementor_pro/forms/validation', 'ens_check_elementor_form');