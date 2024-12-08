function set_cookie(name, value) {  // , expires in days, path, domain, secure
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
 
function get_cookie(Name) {
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

function get_rotator_size(rotator_id)
{
	for (i = 0; i < ban_rotators_max_size; ++ i) {
		if ( ban_rotators[i][0] == rotator_id ) {
			return ban_rotators[i][1];
		}
	}
}

function get_banner_id(banner_size, a_country_code)
{
	numb_of_banners = 0;
	var banner_ids = new Array();
	
	for (i = 0; i < banner_arr_max_size; ++ i) {
		if ( banners[i][1] == banner_size ) {
			if ( a_country_code.length == 0 || banners[i][3].indexOf(a_country_code) >= 0 ) {
				banner_ids[numb_of_banners] = banners[i][0];
				numb_of_banners = numb_of_banners + 1;
			}
		}
	}
	if ( numb_of_banners == 0 ) {
		for (i = 0; i < banner_arr_max_size; ++ i) {
			if ( banners[i][1] == banner_size ) {
				if ( banners[i][3].length == 0 ) {
					banner_ids[numb_of_banners] = banners[i][0];
					numb_of_banners = numb_of_banners + 1;
				}
			}
		}
	}
	
	ban_index = Math.round( Math.random() * ( numb_of_banners - 1 ) );
	if ( ban_index > numb_of_banners - 1 )
		ban_index = numb_of_banners - 1;
	if ( banner_ids[ban_index] )
		return banner_ids[ban_index];
	else
		return '';
}

function get_banner_field(banner_id, field_number)
{
	for (i = 0; i < banner_arr_max_size; ++ i) {
		if ( banners[i][0] == banner_id ) {
			return banners[i][field_number];
		}
	}
}

function Hex_To_String(str) 
{
	var r = "";
	try{
		var str_l = 0;
		str_l = str.length;
		
		if ( str_l == 0 )
			return r;
			
		var i = 0;
		var s = "";
		while( i < str_l ) {
			s = "0x" + str.substr(i, 2);
			s2 = String.fromCharCode(s);
			r = r + String.fromCharCode(s);
			i = i + 2;
		}
	}
	catch(error){}
	return r;
}

var real_banner_id = "";
var wait_time = 0;
function display_banner()
{
	try{
		if ( country_code == '' ) ;
	}
	catch(error){
		country_code = '';
	}
	var size = '0x0';
	try{
		if ( banner_id.length > 0 ) {
			size = get_rotator_size(banner_id);
			if ( !size )
				size = '0x0';
			if ( size.indexOf('0x0') >= 0 ) {
				size = 'U';
				banner_type = 'T';
			}
			else {
				banner_type = 'I';
			}
			real_banner_id = get_banner_id(size, country_code);
		}
	}
	catch(error){	}
	
	if ( real_banner_id.length == 0 )
		real_banner_id = banners[0][0];
	try{
		if ( user_id.length == 0 )
			user_id = default_userid;
	}
	catch(error){
		user_id = default_userid;
	}
	banner_type = get_banner_field(real_banner_id, 2);
	if ( banner_type == 'T' )
		banner_code = banner_codes[0];
	else
		banner_code = banner_codes[1];
	
	try{
		if ( just_preview ) {
			s = scripts_path + "click.php?tst_cl=1";
		}
		else
			s = mainSiteUrl + MOD_REWRITE_PREFIX + user_id + MOD_REWRITE_SEPARATOR + real_banner_id + MOD_REWRITE_SUFIX;
	}
	catch(error){
		s = mainSiteUrl + MOD_REWRITE_PREFIX + user_id + MOD_REWRITE_SEPARATOR + real_banner_id + MOD_REWRITE_SUFIX;
	}
	while ( banner_code.indexOf("{$targeturl}") >= 0 ) banner_code = banner_code.replace("{$targeturl}", s);
	
	title = Hex_To_String(get_banner_field(real_banner_id, 4));
	while ( banner_code.indexOf("{$title}") >= 0 ) banner_code = banner_code.replace( "{$title}", title );
	
	width = size.substr( 0, size.indexOf('x') );
	height = size.substr( size.indexOf('x') + 1, size.length );
	
	if ( width >= 160 && GPA_campaignid.length > 0 )
		banner_code = '<div style="width:{$width}px; text-align:right; padding-top:0px; margin-top:0px; background-color:#ffffff;">' + banner_code + '</div>';
		
	descr = Hex_To_String(get_banner_field(real_banner_id, 5));
	while ( banner_code.indexOf("{$description}") >= 0 ) banner_code = banner_code.replace("{$description}", descr );
	
	while ( banner_code.indexOf("{$image_src}") >= 0 ) banner_code = banner_code.replace( "{$image_src}", title );
	
	while ( banner_code.indexOf("{$alt}") >= 0 ) banner_code = banner_code.replace("{$alt}", descr );
	
	while ( banner_code.indexOf("{$width}") >= 0 ) banner_code = banner_code.replace("{$width}", width);
	while ( banner_code.indexOf("{$height}") >= 0 ) banner_code = banner_code.replace("{$height}", height);
	
	while ( banner_code.indexOf("{$userid}") >= 0 ) banner_code = banner_code.replace("{$userid}", user_id);
	while ( banner_code.indexOf("{$mainSiteUrl}") >= 0 ) banner_code = banner_code.replace("{$mainSiteUrl}", mainSiteUrl);
	
	display_url = Hex_To_String(get_banner_field(real_banner_id, 6));
	while ( banner_code.indexOf("{$display_url}") >= 0 ) banner_code = banner_code.replace("{$display_url}", display_url);
	
	while ( banner_code.indexOf("'") >= 0 ) banner_code = banner_code.replace("'", "\'");
	while ( banner_code.indexOf("\r") >= 0 ) banner_code = banner_code.replace("\r", '');
	while ( banner_code.indexOf("\n") >= 0 ) banner_code = banner_code.replace("\n", '');
	
	s = banner_code;
	while ( s.indexOf("{$impression_track}") >= 0 ) s = s.replace("{$impression_track}", "");
	
	s = "";
	try{
		if ( just_preview )
			s = '';
		else
			s = '<img style="border:0" src="' + scripts_path + 'imp.php?' + PARAM_AFFILIATE_ID + '=' + user_id + '&' + PARAM_BANNER_ID + '=' + real_banner_id + '" width="1" height="1" alt="" />';
	}
	catch(error){
		s = '<img style="border:0" src="' + scripts_path + 'imp.php?' + PARAM_AFFILIATE_ID + '=' + user_id + '&' + PARAM_BANNER_ID + '=' + real_banner_id + '" width="1" height="1" alt="" />';
	}
	while ( banner_code.indexOf("{$impression_track}") >= 0 ) banner_code = banner_code.replace("{$impression_track}", s);
	document.write(banner_code);
}

display_banner();
