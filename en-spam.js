function setCookie(cname, cvalue, exdays) {
	const d = new Date();
	d.setTime(d.getTime() + (exdays*24*60*60*1000));
	const expires = "expires="+ d.toUTCString();
	document.cookie = cname + "=" + cvalue + ";" + expires + ";path=/";
  }

function getCookie(cname) {
	const name = cname + "=";
	const decodedCookie = decodeURIComponent(document.cookie);
	const ca = decodedCookie.split(';');
	for(let i = 0; i <ca.length; i++) {
	  let c = ca[i];
	  while (c.charAt(0) == ' ') {
		c = c.substring(1);
	  }
	  if (c.indexOf(name) == 0) {
		return c.substring(name.length, c.length);
	  }
	}
	return "";
  }

jQuery(document).ready(function( $ ) {
	$('#commentform').submit(function(){
		var hash = data.hash;
		if (!getCookie('wordpress_logged_in_' + hash) && !getCookie('comment_author_email_' + hash)) {
			setCookie('comment_author_email_' + hash, $('#email').val(), 30);
		}
	});
});
