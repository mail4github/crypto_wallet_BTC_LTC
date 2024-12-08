<?php
require_once(DIR_WS_CLASSES.'user.class.php');

class Banner
{
	var $bannerid;
	var $userid;
	var $owner;
	var $table_banners = TABLE_BANNERS;

	function get_info($id = '')
	{
		if ( !empty($id) )
			$this->bannerid = $id;
		if ( !empty($this->bannerid) ) {
			$data = get_api_value('banner_read_data', '', array('bannerid' => $this->bannerid), '', $this->init_owner());
			if ( $data ) {
				foreach($data as $akey => $aval) {
					$this->$akey = $aval;
				}
				return true;	
			}
			else {
				echo 'error'; exit;
			}
		} 
		return false;
	}
	
	function init_owner($userid = '')
	{
		if ( !$this->owner ) {
			if ( !empty($userid) )
				$this->userid = $userid;
			$this->owner = new User();
			$this->owner->userid = $this->userid;
			$this->owner->read_data(false);
		}
		return $this->owner;
	}
	/*
	function update_ad(
			$type, 
			$url, 
			$ad_headline, 
			$ad_description, 
			$ad_display_url, 
			$image_source, 
			$html_text, 
			$banner_size, 
			$countries = '', 
			$disabled = '0',
			$title = '', 
			$dinamic_code = '',
			$name = '',
			$description = '',
			$userid = DEFAULT_USERID,
			$ask_question = 0,
			$question = '',
			$right_answer = '',
			$wrong_answer1 = '',
			$wrong_answer2 = '',
			$time_to_view = 0,
			$show_to_user_one_time = 0,
			$standalone_window = 0,
			$gender = '', 
			$married = '', 
			$has_children = '', 
			$from_age = 0, 
			$to_age = 0,
			$education = '',
			$bid = 0,
			$check_errors = 1,
			$limit_clicks = 0,
			$limit_clicks_period = 0
	)
	{
		if ( !empty($url) && !is_integer(strpos(strtolower($url), 'http')) )
			$url = 'http://'.$url;
		
		$message = '';
		
		$name = tep_sanitize_string($name, 64);
		$description = tep_sanitize_string($description, 256);
		
		$this->init_owner($userid)->check_url($url);

		$url = substr($url, 0, 2000);
		$ad_headline = mb_convert_encoding($ad_headline, 'HTML-ENTITIES', 'UTF-8');
		$ad_headline = tep_sanitize_string($ad_headline, AD_HEADLINE_MAX_SIZE, true, false, '', true, false, true);
		$ad_description = mb_convert_encoding($ad_description, 'HTML-ENTITIES', 'UTF-8');
		$ad_description = tep_sanitize_string($ad_description, AD_DESCR_MAX_SIZE, true, false, '', true, false, true);
		$ad_display_url = mb_convert_encoding($ad_display_url, 'HTML-ENTITIES', 'UTF-8');
		$ad_display_url = tep_sanitize_string($ad_display_url, AD_URL_MAX_SIZE, true, false, '', true, false, true);
		
		if ( empty($userid) )
			$userid = 0;
		if ( empty($ask_question) )
			$ask_question = 0;
		$question = tep_sanitize_string($question, 64);
		$right_answer = tep_sanitize_string($right_answer, 64);
		$wrong_answer1 = tep_sanitize_string($wrong_answer1, 64);
		$wrong_answer2 = tep_sanitize_string($wrong_answer2, 64);
		if ( empty($time_to_view) )
			$time_to_view = 0;
		if ( $time_to_view > 60 )
			$time_to_view = 60;
		if ( empty($show_to_user_one_time) )
			$show_to_user_one_time = 0;
		if ( empty($standalone_window) )
			$standalone_window = 0;
		$gender = tep_sanitize_string($gender, 1);
		$married = tep_sanitize_string($married, 1);
		$has_children = tep_sanitize_string($has_children, 1);
		if ( empty($from_age) )
			$from_age = 0; 
		if ( empty($to_age) )
			$to_age = 0;
		$education = tep_sanitize_string($education, 2);

		$bid = (float)$bid;
		if ( empty($bid) )
			$bid = 0;
		
		$image_source = substr($image_source, 0, 1024);
		
		if ( empty($type) )
			$message = '{19}banner type';
		if ( empty($disabled) )
			$disabled = '0';

		if ( empty($limit_clicks) )
			$limit_clicks = 0;
		if ( empty($limit_clicks_period) )
			$limit_clicks_period = 0;

		switch ($type) {
			case 'T' : 
				if ( empty($ad_headline) || is_integer(strpos($ad_headline, '<')) || is_integer(strpos($ad_headline, '>')) ) $message = '{1}enter banner headline';
				if ( empty($ad_description) || is_integer(strpos($ad_description, '<')) || is_integer(strpos($ad_description, '>')) ) $message = '{2}enter banner description';
				if ( empty($ad_display_url) || is_integer(strpos($ad_display_url, '<')) || is_integer(strpos($ad_display_url, '>')) ) $message = '{3}enter banner url';
				if ( empty($banner_size) ) $banner_size = '0x0';
			break;
			case 'F' : 
			case 'I' : 
				if ( empty($image_source) ) $message = '{4}upload banner';
				if ( empty($banner_size) ) $message = '{5}select banner size';
			break;
			case 'H' : 
				if ( empty($html_text) ) $message = '{6}enter banner code';
				if ( empty($banner_size) ) $banner_size = '0x0';
			break;
			case 'J' : 
				if ( empty($html_text) ) $message = '{7}enter banner code';
				if ( empty($banner_size) ) $message = '{8}select banner size';
			break;
			case 'TR' : 
				if ( empty($url) ) $message = '{11}enter Website URL';
				if ( empty($name) ) $message = '{12}enter Website Title';
				if ( empty($description) ) $message = '{13}enter Website Description';
				if ( $ask_question ) {
					if ( empty($question) ) $message = '{14}enter Security Question.';
					if ( empty($right_answer) ) $message = '{15}enter Right Answer.';
					if ( empty($wrong_answer1) ) $message = '{16}enter Wrong Answer 1.';
					if ( empty($wrong_answer2) ) $message = '{17}enter Wrong Answer 2.';
				}
				if ( $bid < MINIMUM_CLICK_COST / CLICK_SUBSIDIZE_RATE && !$this->init_owner($userid)->is_general_manager() ) $message = '{9}minimum click cost: '.currency_format(MINIMUM_CLICK_COST / CLICK_SUBSIDIZE_RATE);
				if ( $bid > MAXIMUM_CLICK_COST && !$this->init_owner($userid)->is_general_manager() ) $message = '{10}maximum click cost: '.currency_format(MAXIMUM_CLICK_COST);

				//if ( !empty($_COOKIE['debug']) ) echo '$userid: '.$userid.', balance: '.$this->init_owner($userid)->get_balance(true).'<br>';

				if ( $this->init_owner($userid)->get_balance(true) < $bid * 10 ) $message = '{19}the click cost is high, you have no available funds.';
			break;
			default:
				$message = '{19}banner type';
			break;
		}
		
		if ( $check_errors && !empty($message) )
			return $message;
		
		if ( !empty($banner_size) && is_integer(strpos($banner_size, 'x')) ) {
			$banner_width = substr($banner_size, 0, strpos($banner_size, 'x') );
			$banner_height = substr($banner_size, strpos($banner_size, 'x') + 1 );
		}
		else {
			$banner_width = 0;
			$banner_height = 0;
		}
		if ( $type == 'I' || $type == 'F' ) {
			if ( is_integer(strpos($image_source, DIR_WS_TEMP_UPLOADS_NAME)) ) {
				$old_file_name = DIR_WS_TEMP_UPLOADS.pathinfo($image_source, PATHINFO_FILENAME).'.'.pathinfo($image_source, PATHINFO_EXTENSION);
				$new_file_name = $userid.'_'.date("His").rand(1, 99).'.'.pathinfo($image_source, PATHINFO_EXTENSION);
				if ( !rename($old_file_name, DIR_WEBSITE_BANNERS.$new_file_name) ) {
					$message = '{21}cannot move image to the permanent folder.';
					return $message;
				}
				$image_source = '/'.DIR_WEBSITE_FOLDER.DIR_WS_BANNERS_NAME.$new_file_name;
				$this->image_source = $image_source;
			}
		}
		
		if ( ! tep_db_is_connected() ) 
			tep_db_connect();
		
		$values = array(
			'type' => $type,
			'disabled' => $disabled,
			'destinationurl' => $url,
			'textlink_headline' => $ad_headline,
			'textlink_description' => $ad_description,
			'textlink_display_url' => $ad_display_url,
			'image_source' => $image_source,
			'html_text' => $html_text,
			'banner_width' => $banner_width,
			'banner_height' => $banner_height,
			'countries' => $countries,
			'title' => $title,
			'dinamic_code' => $dinamic_code,
			'name' => $name,
			'description' => $description,
			'userid' => $userid,
			'ask_question' => $ask_question,
			'question' => $question,
			'right_answer' => $right_answer,
			'wrong_answer1' => $wrong_answer1,
			'wrong_answer2' => $wrong_answer2,
			'time_to_view' => $time_to_view,
			'show_to_user_one_time' => $show_to_user_one_time,
			'standalone_window' => $standalone_window,
			'gender' => $gender,
			'married' => $married,
			'has_children' => $has_children,
			'from_age' => $from_age,
			'to_age' => $to_age,
			'education' => $education,
			'bid' => $bid,
			'limit_clicks' => $limit_clicks,
			'limit_clicks_period' => $limit_clicks_period,
		);
		
		if ( empty($this->bannerid) ) {
			$values['_created'] = 'NOW()';
			if ( ! tep_db_perform($this->table_banners, $values, 'insert', '', 'db_link', !empty($_COOKIE['debug'])) )
				return 'Error: cannot add banner';
			else {
				$q = 'SELECT bannerid FROM '.$this->table_banners.' ORDER BY created DESC LIMIT 1 ';
				if ( $row = tep_db_perform_one_row($q) ){
					$this->bannerid = $row['bannerid'];
				}
			}
		}
		else { 
			if ( ! tep_db_perform($this->table_banners, $values, 'update', ' bannerid = "'.$this->bannerid.'" ', 'db_link', !empty($_COOKIE['debug'])) )
				return 'Error: cannot perform change';
		}
		$this->get_info();
	}
	
	function calculate_total_click_cost()
	{
		return $this->calculate_PTC_click_cost($this->bid, $this->ask_question, $this->time_to_view, $this->standalone_window);
	}
	
	function calculate_PTC_click_cost($bid, $ask_question, $time_to_view, $standalone_window)
	{
		
		$total_click_cost = $bid;
		if ( $ask_question )
			$total_click_cost = $total_click_cost + $bid * 0.3;
		if ( $time_to_view == 40 ) 
			$total_click_cost = $total_click_cost + $bid * 0.2;
		if ( $time_to_view == 60 ) 
			$total_click_cost = $total_click_cost + $bid * 0.4;
		if ( $standalone_window )
			$total_click_cost = $total_click_cost + $bid * 0.3;
		return $total_click_cost;
		
	}
	
	function save_tmp_data($type, $url, $ad_headline, $ad_description, $ad_display_url, $image_source, $html_text, $banner_size, $countries = '', $disabled = '0',
			$title, 
			$dinamic_code,
			$name,
			$description
			)
	{
		$banner_width = substr($banner_size, 0, strpos($banner_size, 'x') );
		$banner_height = substr($banner_size, strpos($banner_size, 'x') + 1 );
		
		$this->type = $type;
		$this->disabled = $disabled;
		$this->url = $url;
		$this->ad_headline = $ad_headline;
		$this->ad_description = $ad_description;
		$this->ad_display_url = $ad_display_url;
		$this->image_source = $image_source;
		$this->html_text = $html_text;
		$this->banner_width = $banner_width;
		$this->banner_height = $banner_height;
		$this->countries = $countries;
		$this->title = $title;
		$this->dinamic_code = $dinamic_code;
		$this->name = $name;
		$this->description = $description;
	}
	*/
	function banner_size()
	{
		if ( !empty($this->banner_width) && !empty($this->banner_height) )
			return $this->banner_width.'x'.$this->banner_height;
		else
			return '';
	}
	/*
	function delete($force_delete = false)
	{
		if ( $this->banned && !$force_delete )
			return 'Error: banned banner cannot be deleted. You have to un-bann it first.';
		if ( $this->disabled || $force_delete ) {
			if ( ! tep_db_is_connected() ) 
				tep_db_connect();
			
			if( !empty($this->image_source) ) {
				$s = $this->image_source;
				if ($s[0] == '/' )
					$s = substr($s, 1);
				if ( is_integer(strpos( $this->image_source, DIR_WEBSITE_FOLDER.DIR_WS_BANNERS_NAME)) ) {
					unlink(DIR_ROOT.$s);
				}
			}
			
			tep_db_perform_one_row('DELETE FROM '.$this->table_banners.' WHERE bannerid = "'.$this->bannerid.'" LIMIT 1 ', !empty($_COOKIE['debug']));
			tep_db_perform_one_row('DELETE FROM '.TABLE_CLICKS.' WHERE bannerid = "'.$this->bannerid.'" LIMIT 1 ', !empty($_COOKIE['debug']));
			tep_db_perform_one_row('DELETE FROM '.TABLE_STATS_BANNER_EXPOSURES.' WHERE bannerid = "'.$this->bannerid.'" LIMIT 1 ', !empty($_COOKIE['debug']));
			tep_db_perform_one_row('DELETE FROM '.TABLE_STATS_LEADS.' WHERE bannerid = "'.$this->bannerid.'" LIMIT 1 ', !empty($_COOKIE['debug']));
			
		}
		else 
			$this->disable();
	}
	
	function toggle_disabled()
	{
		$q = ' UPDATE '.$this->table_banners.' SET disabled = NOT disabled WHERE bannerid = "'.$this->bannerid.'" LIMIT 1 ';
		tep_db_perform_one_row($q);
	}
	
	function disable()
	{
		if ( ! tep_db_is_connected() ) 
			tep_db_connect();
		
		$q = ' UPDATE '.$this->table_banners.' SET disabled = "1" WHERE bannerid = "'.$this->bannerid.'" LIMIT 1 ';
		tep_db_perform_one_row($q);
	}
	
	function enable()
	{
		if ( ! tep_db_is_connected() ) 
			tep_db_connect();
		
		$q = ' UPDATE '.$this->table_banners.' SET disabled = "0" WHERE bannerid = "'.$this->bannerid.'" LIMIT 1 ';
		tep_db_perform_one_row($q);
	}
	
	function save_click_on_ad($click_cost = '')
	{
		if ( ! tep_db_is_connected() ) 
			tep_db_connect();
		if ( empty($click_cost) )
			$click_cost = 0;
		if ( $click_cost <= BANNER_BID_MINIMUM )
			$click_cost = BANNER_BID_MINIMUM;
		$insert_array = array(
			'userid'  => 0,
			'_datetime' => 'NOW()',
			'bannerid' => $this->bannerid, 
			'countrycode' => getCountryCodefromIP(),
			'_ip' => 'INET_ATON("'.$_SERVER['REMOTE_ADDR'].'")',
			'refererurl' => substr(@$_SERVER['HTTP_REFERER'], 0, 250),
		);
		tep_db_perform(TABLE_TMP_CLICKS, $insert_array, 'insert', '', 'db_link', !empty($_COOKIE['debug']) );
		
		// move clicks to permanent data base
		$first_click_minutes_ago = 0;
		$q = 'SELECT TIMESTAMPDIFF( MINUTE, datetime, NOW() ) AS value FROM '.TABLE_TMP_CLICKS.' WHERE bannerid = "'.$this->bannerid.'" ORDER BY datetime LIMIT 1';
		if ( $row = tep_db_perform_one_row($q, $showDebugInfo) )
			$first_click_minutes_ago = $row['value'];
		
		//if ( $showDebugInfo ) echo '$first_click_minutes_ago: '.$first_click_minutes_ago.'<br>';
		
		if ( $first_click_minutes_ago > 60 ) {
			$number_of_clicks = 0;
			$q = 'SELECT userid, COUNT(*) AS clickcount, bannerid, countrycode, type, refererurl, ip
					FROM '.TABLE_TMP_CLICKS.'
					WHERE bannerid = "'.$this->bannerid.'"
					GROUP BY userid, countrycode, type, refererurl, ip
					ORDER BY userid
					';
			if ( $r = tep_db_query($q) ) {
				while ( $row = tep_db_fetch_array($r) ) {
					$row['_datetime'] = 'NOW()';
					$row['domain'] = get_domain($row['refererurl']);
					$row['domain'] = substr($row['domain'], 0, 64);
					tep_db_perform(TABLE_CLICKS, $row);
					$number_of_clicks = $number_of_clicks + $row['clickcount'];
				}
			}
			$q = 'DELETE FROM '.TABLE_TMP_CLICKS.' WHERE bannerid = "'.$this->bannerid.'"';
			tep_db_perform_one_row($q, $showDebugInfo);
			
			if ( !empty($this->userid) ) {
				$transaction = new Transaction();
				$transaction->init_owner($this->userid);
				$transaction->charge_fee($this->bid * $number_of_clicks, $number_of_clicks.' clicks on banner: '.$this->bannerid);
			}
		}
	}
	*/
	function banner_source()
	{
		$s = $this->image_source;
		$s = str_replace('&', '&amp;', $s);
		$s = str_replace('<', '&lt;', $s);
		$s = str_replace('>', '&gt;', $s);
		return $s;
	}
	/*
	function update_stats()
	{
		if ( !tep_db_is_connected() ) 
			tep_db_connect();
			
		$q = 'SELECT *, stat_clicks / stat_shown * 100 AS stat_CTR, stat_leads / stat_shown * 100 AS stat_LTR
			FROM
			(
				SELECT SUM(clickcount) AS stat_clicks
				FROM '.TABLE_CLICKS.'
				WHERE 
				DATE(datetime) >= DATE_SUB(NOW(), INTERVAL 8 DAY)
				AND DATE(datetime) < DATE_SUB(NOW(), INTERVAL 1 DAY)
				AND bannerid = "'.$this->bannerid.'"
			) AS tb_cl,
			(
				SELECT SUM(count) AS stat_shown
				FROM '.TABLE_STATS_BANNER_EXPOSURES.'
				WHERE DATE(date) >= DATE_SUB(NOW(), INTERVAL 8 DAY)
				AND DATE(date) < DATE_SUB(NOW(), INTERVAL 1 DAY)
				AND bannerid = "'.$this->bannerid.'"
			) AS tb_expo,
			(
				SELECT SUM(count) AS stat_leads
				FROM '.TABLE_STATS_LEADS.'
				WHERE DATE(date) >= DATE_SUB(NOW(), INTERVAL 8 DAY)
				AND DATE(date) < DATE_SUB(NOW(), INTERVAL 1 DAY)
				AND bannerid = "'.$this->bannerid.'"
			) AS tb_leads
			';
		
		if ( $row = tep_db_perform_one_row($q) ){
			if ( empty($row['stat_CTR']) ) $row['stat_CTR'] = 0;
			if ( empty($row['stat_shown']) ) $row['stat_shown'] = 0;
			if ( empty($row['stat_clicks']) ) $row['stat_clicks'] = 0;
			if ( empty($row['stat_LTR']) ) $row['stat_LTR'] = 0;
			if ( empty($row['stat_leads']) ) $row['stat_leads'] = 0;
			
			//echo $row['stat_shown'].','.$row['stat_clicks'].'<br>';
			
			if ( $this->stat_CTR < 0 ) $row['stat_CTR'] = $this->stat_CTR;
			
			$update_values = array(
				'stat_CTR' => $row['stat_CTR'],
				'stat_shown' => $row['stat_shown'],
				'stat_clicks' => $row['stat_clicks'],
				'stat_LTR' => $row['stat_LTR'],
				'stat_leads' => $row['stat_leads'],
			);
			tep_db_perform( $this->table_banners, $update_values, 'update', ' bannerid = "'.$this->bannerid.'" ', 'db_link', !empty($_COOKIE['debug']) );
		}
	}
	*/
	function image_source(User $user = NULL, $isPreview = false)
	{
		return $this->replaceUrlConstants($this->image_source, $user, $isPreview);
	}
	
	function get_image_full_URL(User $user = NULL, $isPreview = false)
	{
		$s = $this->image_source($user, $isPreview);
		if ( is_integer( strpos($s, '://') ) )
			return $s;
		else {
			if ( $s[0] == '/' )
				$s = substr($s, 1);
			return SITE_DOMAIN.$s;
		}
	}
	
	function getBannerCode(User $user, $isPreview = false)
	{
		switch ($this->type) {
			case 'I' : 
				$imageUrl = $this->get_image_full_URL($user, $isPreview);
				$description = $this->getDescription($user);
				$format = $this->getBannerFormat();
				$format = replaceCustomConstantInText('image_src', $imageUrl, $format);
				$format = replaceCustomConstantInText('alt', $description, $format);
			break;
			case 'E' : 
				if ( $isPreview )
					$format = make_synonyms($this->getDescription($user), 2, "\r\n", "\r\n");
				else
					$format = make_synonyms($this->html_text, 2, "\r\n", "\r\n");
			break;
			case 'F' : 
			break;
			case 'R' : 
				$format = $this->getBannerFormat();
			break;
			case 'T' : 
				$format = $this->getBannerFormat();
				$format = replaceCustomConstantInText('title', make_synonyms($this->textlink_headline, 0, '', ''), $format);
				$format = replaceCustomConstantInText('description', make_synonyms($this->textlink_description, 0, '', ''), $format);
		       	$format = replaceCustomConstantInText('display_url', $this->textlink_display_url, $format);
			break;
			case 'H' : 
				$format = $this->getBannerFormat();
				$additional_link_name = '';
				$additional_link_desc = '';
				//get_additional_link($additional_link_name, $additional_link_desc);
				$description = make_synonyms($this->getDescription($user), 3, '', '');
				$description = replaceCustomConstantInText('additional_link_name', $additional_link_name, $description);
				$description = replaceCustomConstantInText('additional_link_desc', $additional_link_desc, $description);
				$format = replaceCustomConstantInText('description', $description, $format);
			break;
		}
		$format = $this->replaceWidthHeightConstants($format, $flags);
		$format = $user->replaceUserConstantsInText($format);
		$format = $this->replaceUrlConstants($format, $user, $isPreview);
		return $format;
	}
	
	function getDescription(User $user = NULL) 
	{
		if ( $this->type == 'H') 
			$description = $this->html_text;
		else
			$description = $this->description;
		$s = $this->dinamic_code;
		if ( !empty($s) ) {
			$s = file_get_contents($s);
			if ( !empty($s) )
				$description = $s;
		}
		if ( ! $user )
			$user = new User();
		$description = $user->replaceUserConstantsInText($description);
		
		return $description;
	}
	
	function getBannerFormat() 
	{
		switch ($this->type) {
			case 'I' : return IMAGE_BANNER_FORMAT; break;
			case 'T' : return TEXT_BANNER_FORMAT; break;
			case 'F' : return FLASH_BANNER_FORMAT; break;
			case 'R' : return ROTATOR_BANNER_FORMAT; break;
			case 'H' : return HTML_BANNER_FORMAT; break;
		}
	}
	
	function replaceUrlConstants($text, User $user = NULL, $isPreview = false) {
		if ( $user ) {
			$clickUrl = $this->getClickUrl($user);
			$impressionTrack = $this->getImpressionTrackingCode($user, $isPreview);
			$clickUrlEncoded = $clickUrl;
			$text = replaceCustomConstantInText('userid', $user->userid, $text);
			$user_common_params = $user->get_list_of_common_params();
			foreach($user_common_params as $key => $val) {
				$text = replaceCustomConstantInText($key, $val, $text);
			}
		}
		$text = $this->replace_common_constants($text);
		$text = replaceCustomConstantInText('targeturl', $clickUrl, $text);
		$text = replaceCustomConstantInText('targeturl_encoded', $clickUrlEncoded, $text);
		$text = replaceCustomConstantInText('impression_track', $impressionTrack, $text);
		return $text;
	}
	
	function getClickUrl(User $user) 
	{
		if ( $this->type == 'R') {
			$clickUrl = PARAM_AFFILIATE_ID."=".$user->userid."&".PARAM_BANNER_ID."=".$this->bannerid;
		}
		else {
			$mainSiteUrl = SITE_DOMAIN;
			if ( $mainSiteUrl[strlen($mainSiteUrl)-1] != '/' )
				$mainSiteUrl .= '/';
			$clickUrl = $mainSiteUrl.MOD_REWRITE_PREFIX.$user->userid.MOD_REWRITE_SEPARATOR.$this->bannerid.MOD_REWRITE_SUFIX;
		}
		return $clickUrl;
	}
	
	function getImpressionTrackingCode(User $user, $isPreview = false) 
	{
		if ( $isPreview )
            return '';
        else {
			$code  = "<img style=\"border:0\" src=\"".SITE_DOMAIN.DIR_WS_SERVICES_DIR.'imp.php';
	        $code .= "?".PARAM_AFFILIATE_ID."=".$user->userid;
	        $code .= "&amp;".PARAM_BANNER_ID."=".$this->bannerid;
	        $code .= "\" width=\"0\" height=\"0\" alt=\"\" />";
			return $code;
        }
	}
	
	function replaceWidthHeightConstants($format) 
	{
		$format = replaceCustomConstantInText('width', $this->banner_width, $format);
        $format = replaceCustomConstantInText('height', $this->banner_height, $format);
        return $format;
	}
	
	function destinationurl(User $user = NULL)
	{
		$s = '';
		
		if ( !empty($this->destinationurl) ) {
			$s = $this->destinationurl;
			if ( ! $user )
				$user = new User();
			$user_common_params = $user->get_list_of_common_params();
			foreach($user_common_params as $key => $val) {
				$s = replaceCustomConstantInText($key, $val, $s);
			}
		}
		return $s;
	}
	
	function textlink_display_url(User $user = NULL) {
		$text = $this->textlink_display_url;
		if ( $user ) {
			$user_common_params = $user->get_list_of_common_params();
			foreach($user_common_params as $key => $val) {
				$text = replaceCustomConstantInText($key, $val, $text);
			}
		}
		$text = $this->replace_common_constants($text);
		return $text;
	}
	
	function replace_common_constants($text)
	{
		$text = replaceCustomConstantInText('site_short_domain', SITE_SHORTDOMAIN, $text);
		$text = replaceCustomConstantInText('site_domain', SITE_DOMAIN, $text);
		$text = replaceCustomConstantInText('dir_ws_images', DIR_WS_IMAGES_DIR, $text);
		$text = replaceCustomConstantInText('dir_ws_banners', DIR_WS_BANNERS_NAME, $text);
		$text = replaceCustomConstantInText('FullScriptsUrl', SITE_DOMAIN.DIR_WS_SERVICES_DIR, $text);
		
		//$text = replaceCustomConstantInText('permalinks_domain', PERMALINKS_DOMAIN, $text);
		//$text = replaceCustomConstantInText('permalinks_shortdomain', PERMALINKS_SHORTDOMAIN, $text);
		//$text = replaceCustomConstantInText('selltraffic_domain', SELLTRAFFIC_DOMAIN, $text);
		//$text = replaceCustomConstantInText('selltraffic_shortdomain', SELLTRAFFIC_SHORTDOMAIN, $text);
		$text = replaceCustomConstantInText('business_name', BUSINESS_NAME, $text);
		$text = replaceCustomConstantInText('programName', SITE_SHORTDOMAIN, $text);
		$text = replaceCustomConstantInText('site_slogan', SITE_SLOGAN, $text);
		$text = replaceCustomConstantInText('site_name', SITE_NAME, $text);
		$text = replaceCustomConstantInText('login_url', LOGIN_URL, $text);
		
		return $text;
	}
	/*
	function bann($value = 1)
	{
		if ( ! tep_db_is_connected() ) 
			tep_db_connect();
		if ( !$value ) {
			if ( $this->init_owner()->get_balance(true) > UNBANN_JOB_FEE ) {
				$transaction = new Transaction();
				$transaction->charge_fee(UNBANN_JOB_FEE, 'Unbann banner '.$this->bannerid, $this->bannerid, $this->userid);
			}
			else 
				return 'Error: Insufficient funds.';
		}

		$q = ' UPDATE '.$this->table_banners.' SET banned = "'.$value.'" WHERE bannerid = "'.$this->bannerid.'" LIMIT 1 ';
		tep_db_perform_one_row($q, !empty($_COOKIE['debug']));
	}

	function report_inappropriate($bannerid = '', $url = '')
	{
		if ( !empty($bannerid) )
			$this->bannerid = $bannerid;

		if ( !empty($this->bannerid) ) {
			$this->get_info();
			$url = $this->destinationurl;
		}
		if ( $this->userid == DEFAULT_USERID )
			return false;
		if ( !empty($url) ) {
			if ( ! tep_db_is_connected() ) 
				tep_db_connect();
			$q = 'INSERT INTO '.TABLE_BANNED_URLS.' (url, created) VALUES ("'.tep_sanitize_string($url, 255).'", NOW()) ON DUPLICATE KEY UPDATE reports = reports + 1';
			tep_db_perform_one_row( $q, !empty($_COOKIE['debug']) );

			$q = 'UPDATE '.TABLE_BANNED_URLS.' SET reports = reports - 2, created = NOW() WHERE created < DATE_SUB(NOW(), INTERVAL 1 DAY)';
			tep_db_perform_one_row( $q, !empty($_COOKIE['debug']) );

			$q = 'DELETE FROM '.TABLE_BANNED_URLS.' WHERE reports <= 0';
			tep_db_perform_one_row( $q, !empty($_COOKIE['debug']) );

			$this->destinationurl = $url;
		}
	}

	function set_bid($bid, $check_balance = 1)
	{
		if ( empty($this->bannerid) ) 
			return false;
		
		$bid = trim($bid, '$');
		if ( !isNumber($bid) )
			return 'Error: wrong number.';
		
		$bid = (float)$bid;
		switch ($this->type) {
			case 'TR' : 
				if ( $bid < MINIMUM_CLICK_COST / CLICK_SUBSIDIZE_RATE ) return 'Error: minimum click cost: '.currency_format(MINIMUM_CLICK_COST / CLICK_SUBSIDIZE_RATE);
				if ( $bid > MAXIMUM_CLICK_COST ) return 'Error: maximum click cost: '.currency_format(MAXIMUM_CLICK_COST);
			break;
			default: 
				if ( $bid < BANNER_BID_MINIMUM ) return 'Error: minimum banner bid: '.currency_format(BANNER_BID_MINIMUM);
				if ( $bid > BANNER_BID_MAXIMUM ) return 'Error: maximum banner bid: '.currency_format(BANNER_BID_MAXIMUM);
		}

		if ( ! tep_db_is_connected() ) 
			tep_db_connect();
		
		if ( $check_balance && $this->init_owner()->get_balance(true) < $bid * 10 ) 
			return 'Error: the bid cost is high, you have no available funds.';
	
		$q = ' UPDATE '.$this->table_banners.' SET bid = "'.$bid.'" WHERE bannerid = "'.$this->bannerid.'" LIMIT 1 ';
		tep_db_perform_one_row($q);

		$this->bid = $bid;

		return '';
	}

	function has_been_viewed()
	{
		if ( !tep_db_is_connected() ) 
			tep_db_connect();
		
		if ( $this->limit_clicks > 0 ) {
			if ( $this->stat_limit_clicks_count > $this->limit_clicks ) {
				if ( $this->need_update_limit_clicks_started ) {
					$q = ' UPDATE '.$this->table_banners.' SET 
						stat_limit_clicks_started = DATE_ADD(NOW(), INTERVAL '.$this->limit_clicks_period.' MINUTE), 
						stat_limit_clicks_count = 0 
						WHERE bannerid = "'.$this->bannerid.'" LIMIT 1 ';
					tep_db_perform_one_row($q, !empty($_COOKIE['debug']));
				}
			}
			else {
				$q = ' UPDATE '.$this->table_banners.' SET stat_limit_clicks_count = stat_limit_clicks_count + 1 WHERE bannerid = "'.$this->bannerid.'" LIMIT 1 ';
				tep_db_perform_one_row($q, !empty($_COOKIE['debug']));
			}
		}
		
	}

	function delete_all_user_banners($userid = '')
	{
		if ( ! tep_db_is_connected() ) 
			tep_db_connect();
		if ( !empty($userid) )
			$this->userid = $userid;
		$q = 'SELECT bannerid FROM '.$this->table_banners.' WHERE userid = "'.$this->userid.'" ';
		if ( $r = tep_db_query($q, 'db_link', !empty($_COOKIE['debug']) ) ) {
			while ( $row = tep_db_fetch_array($r) ) {
					$banner = new Banner();
					if ( $banner->get_info($row['bannerid']) )
						$banner->delete(true);
			}
		}
	}
	*/
}
?>