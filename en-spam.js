function setCookie(c_name,value,exdays) {
	var exdate=new Date();
	exdate.setDate(exdate.getDate() + exdays);
	var c_value=escape(value) + ((exdays==null) ? "" : "; expires="+exdate.toUTCString()) + "; path=/";
	document.cookie=c_name + "=" + c_value;
}

function getCookie(c_name) {
	var i,x,y,ARRcookies=document.cookie.split(";");
	for (i=0;i<ARRcookies.length;i++) {
		x=ARRcookies[i].substr(0,ARRcookies[i].indexOf("="));
		y=ARRcookies[i].substr(ARRcookies[i].indexOf("=")+1);
		x=x.replace(/^\s+|\s+$/g,"");
			if (x==c_name) {
				return unescape(y);
			}
	}
}

jQuery(document).ready(function( $ ) {
	$('#commentform').submit(function(){
		var hash = data.hash;
		if (!getCookie('wordpress_logged_in_' + hash) && !getCookie('comment_author_email_' + hash)) {
			setCookie('comment_author_email_' + hash, $('#email').val(), 30);
		}
	});
});
