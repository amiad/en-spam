<?php
/*
Plugin Name: En Spam
Description: Block Spam with Cookies and Language Filtering.
Plugin URI: http://hatul.info/en-spam
Version: 0.2
Author: Hatul
Author URI: http://hatul.info
License: GPL http://www.gnu.org/copyleft/gpl.html
*/
add_filter('preprocess_comment','ens_check_comment');
function ens_check_comment($comment_all){
	$lang=get_bloginfo('language');
	$alephbet=array('he-IL'=>'א-ת', //hebrew
			'ge_GE'=>'ა-ჰ', //georgian
			);
	
	$comment=$comment_all['comment_content'];
	$lang_in_whitelist=(array_key_exists($lang,$alephbet));
	if(($lang_in_whitelist) &&
	 preg_match('/['.$alephbet[$lang].']/',$comment)) 
	 	return $comment_all;
	else {
		$comment=ens_more_checks($comment);
		if($comment) {
			$comment_all['comment_content']=$comment;
			return $comment_all;
		}
		elseif ($lang_in_whitelist) wp_die(sprintf(__('To respond in a different language from Hebrew, you need to enable cookies or add the number %s at the end of the response.','en-spam'),get_option('ens_code')));
		else wp_die(sprintf(__('To respond, you need to enable cookies or add the number %s at the end of the response.','en-spam'),get_option('ens_code')));
	}
}
function ens_more_checks($comment){
	if((isset($_COOKIE['comment_author_'.COOKIEHASH])) 
	|| (is_user_logged_in())) return $comment;
	elseif(strpos($comment,get_option('ens_code'))!==false) {
		$comment=str_replace(get_option('ens_code'),'',$comment);
		return $comment;
	}
	else return null;
}
add_action('init','ens_add_js');
function ens_add_js(){
	if(!is_user_logged_in()){
		add_action('wp_head','ens_js_cookie');
		add_filter('comment_form_field_comment','ens_cookie_to_commenter');
	}
}
function ens_js_cookie(){
	?>
	<script>
	function setCookie(c_name,value,exdays)
		{
			var exdate=new Date();
			exdate.setDate(exdate.getDate() + exdays);
			var c_value=escape(value) + ((exdays==null) ? "" : "; expires="+exdate.toUTCString());
			document.cookie=c_name + "=" + c_value;
		}
	function getCookie(c_name)
			{
			var i,x,y,ARRcookies=document.cookie.split(";");
			for (i=0;i<ARRcookies.length;i++)
			{
			  x=ARRcookies[i].substr(0,ARRcookies[i].indexOf("="));
			  y=ARRcookies[i].substr(ARRcookies[i].indexOf("=")+1);
			  x=x.replace(/^\s+|\s+$/g,"");
			  if (x==c_name)
				{
				return unescape(y);
				}
			  }
	}
	function cookieToCommenter(){
		var cookie=getCookie('comment_author_<?php echo COOKIEHASH;?>');
		if(cookie==null) setCookie('comment_author_<?php echo COOKIEHASH;?>',document.getElementById('author').value,30);
	}
	</script>
	<?php
}	
function ens_cookie_to_commenter($field){
	$field=str_replace('<textarea','<textarea onfocus="cookieToCommenter()"',$field);
	return $field;
}
//random code in activation the plugin
register_activation_hook(__FILE__, 'ens_init_code');
function ens_init_code(){
	update_option('ens_code',rand(10000,99999));
}
load_plugin_textdomain('en-spam', false, dirname( plugin_basename( __FILE__ ) ) );
?>
