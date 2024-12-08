function get_parameter(param_name)
{
	var retval = "";
	aURL = document.URL;
	parPos = aURL.indexOf('?');
	ParStr = "";
	if (parPos > -1 ) {
		ParStr = aURL.substring(parPos + 1, aURL.length);
		parPos = ParStr.indexOf(param_name + '=');
		if (parPos > -1 ) {
			parPos = parPos + param_name.length + 1;
			parEnds = ParStr.indexOf("&", parPos);
			if (parEnds < 0) 
	  			parEnds = ParStr.length;
			retval = ParStr.substring(parPos, parEnds);
		}
	}
	return retval;
}

function set_cookie(name, value)  // , expires in days, path, domain, secure
{
	var argv = set_cookie.arguments;  
	var argc = set_cookie.arguments.length;  
	var expires = (argc > 2) ? argv[2] : null;  
	var path = (argc > 3) ? argv[3] : null;  
	var domain = (argc > 4) ? argv[4] : null;  
	var secure = (argc > 5) ? argv[5] : false;  
	if ( expires != null ) {
		var expDate = new Date();
		expDate.setTime(expDate.getTime() +  (24 * 60 * 60 * 1000 * expires)); 
	}
	
	document.cookie = name + "=" + escape (value) + 
	   ((expires == null) ? "" : ("; expires=" + expDate.toGMTString())) + 
	   ((path == null) ? "" : ("; path=" + path)) +  
	   ((domain == null) ? "" : ("; domain=" + domain)) +    
	   ((secure == true) ? "; secure" : "");
}

function get_cookie(Name) 
{
	var search = Name + "="
	var returnvalue = "";
	if (document.cookie.length > 0) {
		offset = document.cookie.indexOf(search)
		if (offset != -1) { // if cookie exists
			offset += search.length
			end = document.cookie.indexOf(";", offset);
			if (end == -1)
				end = document.cookie.length;
			returnvalue=unescape(document.cookie.substring(offset, end))
		}
	}
	return returnvalue;
}

function check_email( email_to_check )
{
	var filter = /^([\w-]+(?:\.[\w-]+)*)@((?:[\w-]+\.)*\w[\w-]{0,66})\.([a-z]{2,6}(?:\.[a-z]{2})?)$/i;
	if ( filter.test( email_to_check ) )
		return true;
	else
		return false;
}

if (get_parameter('to_top') == 'yes' && top != self)
	top.location = location;
	
set_cookie("from_html_signup", "1");