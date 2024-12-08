<?php
require_once(DIR_WS_CLASSES.'user.class.php');

class Job
{
	var $jobid = 0;
	var $variables = array();
	var $skillid = 'clicks';
	var $skill_level_this_and_higher = 1;
	var $who_receives = 'FREELANCE';
	var $reward = BANNER_BID_MINIMUM;
	var $result_type = 'report';
	var $cancel_unfinished_job_in = 1440;
	var $owner;
	var $result_auto_approve = 1;
	var $result_unique_text = 0;
	var $repeat_times = 100;
	var $rank = 1;
	var $can_be_performed = true;
	var $skills = array(
		array('skillid' => 'signup', 'name' => 'Sign Up', 'description' => 'Create account on a website. Fill in the sign up form. Obtain a user name. Log in to account. Perform some activity.', 'show_hide_objects' => '', 'change_values' => ''),
		array('skillid' => 'clicks', 'name' => 'Click on Site', 'description' => 'Open a website and surf through it clicking on links.', 'show_hide_objects' => '', 'change_values' => ''),
		array('skillid' => 'post', 'name' => 'Post to Forum or Blog', 'description' => 'Making post to a forum or add new article to a blog.', 'show_hide_objects' => '', 'change_values' => ''),
		array('skillid' => 'review', 'name' => 'Review or Vote', 'description' => 'Log in to a website and make review or vote for something.', 'show_hide_objects' => '', 'change_values' => ''),
		array('skillid' => 'writing', 'name' => 'Write Article', 'description' => 'Write an article on a given topic or subject including the given keywords.', 'show_hide_objects' => 'weburl_tr=0&show_result_data=text&result_unique_text_tr=1', 'change_values' => 'result_type:text=1'),
		array('skillid' => 'alert', 'name' => 'System Alert', 'description' => 'This job sends a email', 'show_hide_objects' => 'job_description_tr=0&kind_tr=0&must_be_done_tr=0&reward_tr=0&reward_tr2=0&skill_level_tr=0&unfinished_tr=0&disable_when_tr=0&eval_posted_tr=0&eval_approved_tr=0&eval_disapproved_tr=0&eval_send_result_tr=0&eval_skill_upgrade_tr=0&weburl_tr=0&result_tr=0&send_email_tr=1&', 'change_values' => 'kind=0&result_type:URL=1'),
		array('skillid' => 'googlepl', 'name' => 'Google +1', 'description' => 'Clicks on Google +1 button to increase rank of a website.', 'show_hide_objects' => 'repeat_times_tr=0&result_tr=0&must_be_done_tr=0', 'change_values' => 'repeat_times=0&result_type:URL=1&_title=Click on the Google+ Like button&_job=Click on the <b>Google+</b> button below. That is it. The job is done.'),
		array('skillid' => 'facebook', 'name' => 'Facebook Likes', 'description' => 'Clicks on Facebook Like Button to increase rank of a website.', 'show_hide_objects' => 'repeat_times_tr=0&result_tr=0&must_be_done_tr=0', 'change_values' => 'repeat_times=0&result_type:URL=1&_title=Click on the Facebook Like button&_job=Click on the <b>Like</b> button below. That is it. The job is done.'),
		array('skillid' => 'linkedin', 'name' => 'LinkedIn Shares', 'description' => 'Clicks on LinkedIn Share button to increase rank of a website.', 'show_hide_objects' => 'repeat_times_tr=0&result_tr=0&must_be_done_tr=0', 'change_values' => 'repeat_times=0&result_type:URL=1&_title=Click on the LinkedIn Share button&_job=Click on the <b>LinkedIn Share</b> button below. That is it. The job is done.'),
		array('skillid' => 'tweet', 'name' => 'Tweets', 'description' => 'Tweet a message on twitter.com.', 'show_hide_objects' => 'repeat_times_tr=0&result_tr=0&must_be_done_tr=0&tweet_tr=1', 'change_values' => 'repeat_times=0&result_type:URL=1&_title=Tweet the message on twitter.&_job=Click on the <b>Tweet</b> button below, then, in the pop-up window, click the <b>Tweet</b> button again. That is it. The job is done.'),
		array('skillid' => 'follow', 'name' => 'Followers on Twitter', 'description' => 'Follow a Twitter account as follower.', 'show_hide_objects' => 'repeat_times_tr=0&result_tr=0&must_be_done_tr=0&follow_tr=1&weburl_tr=0', 'change_values' => 'repeat_times=0&result_type:URL=1&_title=Follow a Twitter account.&_job=Click on the <b>Follow</b> button below. That is it. The job is done.'),
		array('skillid' => 'permlink', 'name' => 'Create Backlinks', 'description' => 'Post short information about a website and make link to this website.', 'show_hide_objects' => 'job_description_tr=0&permalink_site_title_tr=1&permalink_site_description_tr=1&target_audience=0&result_tr=0&repeat_times_tr=0&must_be_done_tr=0', 'change_values' => 'repeat_times=100&result_type:URL=1&result_auto_approve_URL=0&result_unique_URL:1=1&_title=Make post on a forum to create permanent link&_job=<ol><li>In the Google search choose a forum, navigate to it, create account on it (if you dont have), and post new topic:</li><p><b>Topic title:</b> <em></em></p><p><b>Topic text:</b></p><p><em></em></p><li>Make link from this topic to the following address:</li><p><b><b></p></ol><p>Please send me the URL of your topic.</p>'),
		array('skillid' => 'other', 'name' => 'Other', 'description' => 'It can be anything.', 'show_hide_objects' => '', 'change_values' => ''),
	);
	var $show_on_server_only = '';
	
	function read_data($jobid = '')
	{
		if ( !empty($jobid) )
			$this->jobid = $jobid; 
		$this->variables = get_api_value('job_read_data', '', array('jobid' => $this->jobid));
		if ( $this->variables ) {
			foreach( $this->variables as $akey => $aval ) 
				$this->$akey = $aval;
			return true;
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
		
	function replace_placeholders($s, User $user = NULL, $replace_quotes = true)
	{
		if ( !$user ) {
			$user = new User();
			$user->userid = $this->userid;
			$user->read_data(false);
		}
		$s = make_synonyms($s, 3, '', '', true);
		$common_params = $user->get_list_of_common_params();
		$common_params['job_reward'] = number_format($this->reward, 2);
		$common_params['var_text1'] = $this->var_text1; 
		$common_params['var_text2'] = $this->var_text2; 
		$common_params['var_int1'] = $this->var_int1; 
		$common_params['var_int2'] = $this->var_int2; 
		$common_params['var_int3'] = $this->var_int3; 

		foreach($common_params as $key => $value) {
			if ( $replace_quotes ) {
				$value = str_replace('"', '`', str_replace("'", '`', $value));
			}
			$s = str_replace('{$'.$key.'}', $value, $s);
		}
		return $s;
	}
	
	function get_job_text(User $user = NULL, &$var_text1, &$var_text2, &$var_int1, &$var_int2, &$var_int3)
	{
		if ( !isset($user) ) {
			$user = new User();
			$user->userid = $this->userid;
			$user->read_data(false);
		}
		$display_text = $user->parse_job_text($this->jobid, $this, '', $var_text1, $var_text2, $var_int1, $var_int2, $var_int3);
		$display_text = $this->replace_placeholders($display_text, $user);
		return $display_text;
	}
	
	function result_mandatory_keywords(User $user = NULL)
	{
		return $this->replace_placeholders($this->result_mandatory_keywords, $user);
	}

	function result_intolerable_keywords(User $user = NULL)
	{
		return $this->replace_placeholders($this->result_intolerable_keywords, $user);
	}

	function result_page_must_have_URL(User $user = NULL)
	{
		return $this->replace_placeholders($this->result_page_must_have_URL, $user);
	}

	function result_page_must_have_substring(User $user = NULL, $replace_quotes = true)
	{
		return $this->replace_placeholders($this->result_page_must_have_substring, $user, $replace_quotes);
	}

	function result_URL_must_have_substring(User $user = NULL)
	{
		return $this->replace_placeholders($this->result_URL_must_have_substring, $user);
	}
	
	function result_domain_must_have_substring(User $user = NULL)
	{
		return $this->replace_placeholders($this->result_domain_must_have_substring, $user);
	}
		
	function get_tweet_text()
	{
		$result = make_synonyms($this->permalink_site_title, 3, '', '', true);
		if ( strlen($result.' '.$this->URL) > 139 )
			$result = shorter_text($result, 139 - strlen($this->URL));
		$result = $result.' '.$this->URL;
		$result = html_entity_decode($result, ENT_QUOTES, 'UTF-8');
		$result = str_replace('%', '%25', $result);
		$s = '';
		for ($i = 0; $i < strlen($result); $i++) {
			if ( ord($result[$i]) < 0x20 )
				$s = $s.'%'.bin2hex($result[$i]);
			else
				$s = $s.$result[$i];
		}
		$s = str_replace('’', '%b4', str_replace('"', '%22', str_replace('&', '%26', str_replace("'", '%27', str_replace('?', '%3f', $s)))));
		return $s;
	}

	function URL()
	{
		return make_synonyms($this->URL, 1, '', '', true);
	}

}
