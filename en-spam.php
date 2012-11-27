<?php
/*
Plugin Name: En Spam
Description: Block Spam with Cookies and JavaScript Filtering.
Plugin URI: http://hatul.info/en-spam
Version: 0.6
Author: Hatul
Author URI: http://hatul.info
License: GPL http://www.gnu.org/copyleft/gpl.html
*/
add_filter('preprocess_comment','ens_check_comment');
function ens_check_comment($comment){
	if((isset($_COOKIE['comment_author_'.COOKIEHASH])) 
	  || (is_user_logged_in())
	  || ($_POST['code']==get_option('ens_code'))) {
	  	$comment['comment_content']=stripcslashes($comment['comment_content']);
		return $comment;
	}
	else ens_block_page();
}
function ens_block_page(){
	$message=sprintf(__('For to post comment, you need to enable cookies and JavaScript or to click on "%s" button in this page','en-spam'),__( 'Post Comment' ));
	$message.='<form method="post">';
	foreach($_POST as $name=>$value){
		if($name=='comment')
			$message.=sprintf('<label for="comment">%s</label><br /><textarea id="comment" name="comment">%s</textarea><br />',__('Your comment:','en-spam'),$value);
		else
			$message.=sprintf('<input type="hidden" name="%s" value="%s" />',$name,stripcslashes($value));
	}
	$message.=sprintf('<input type="hidden" name="code" value="%s" />',get_option('ens_code'));
	$message.=sprintf('<input type="submit" name="submit" value="%s" />',__( 'Post Comment' ));
	$message.='</form>';
	
	wp_die($message);
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
	<!-- En Spam Script -->
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
/* old functions for languages filtering

function ens_check_comment($comment_all){
	$hasCheckLang=false; // lang check is disabled now.
	$comment=$comment_all['comment_content'];
	if ($hasCheckLang && ens_checkLang($comment)) return $comment_all; 
	$commentAfterCheck=ens_more_checks($comment);
	if($commentAfterCheck) {
		$comment_all['comment_content']=$commentAfterCheck;
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
function ens_checkLang($comment){
	if(($lang_in_whitelist()) &&
	 preg_match('/['.$alephbet[$lang].']/',$comment)) 
	 	return true;
	 else return false;
}
function lang_in_whitelist(){
	$lang=get_bloginfo('language');
	$alephbet=array('he-IL'=>'א-ת', //hebrew
			'ge_GE'=>'ა-ჰ', //georgian
			);
	return (array_key_exists($lang,$alephbet));
}
*/
