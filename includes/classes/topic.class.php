<?php
require_once(DIR_WS_CLASSES.'user.class.php');

class Topic
{
	var $topicid = 0;
	var $variables = array();
	/*
	function read_data($topicid = '')
	{
		if ( ! tep_db_is_connected() ) 
			tep_db_connect();
		if ( !empty($topicid) )
			$this->topicid = $topicid;
		if ( !empty($this->topicid) ) {
			$q = 'SELECT *, 
				TIMESTAMPDIFF( MINUTE, created, NOW() ) AS created_minutes_ago, 
				DATE_FORMAT(created, "%d %b %Y") AS created_date, 
				DATE_FORMAT(created, "%H:%i") AS created_time,
				UNIX_TIMESTAMP(created) AS created_since_unix
				FROM '.TABLE_FORUM_TOPICS.' WHERE topicid = "'.$this->topicid.'" LIMIT 1 ';
			if ( $this->variables = tep_db_perform_one_row($q, !empty($_COOKIE['debug'])) ) {
				foreach( $this->variables as $akey => $aval ) 
					$this->$akey = $aval;
				return true;
			}
		}
		return false;
	}

	function delete($delete_by_userid = '')
	{
		if ( empty($this->topicid) )
			return 'Error: cannot delete topic.';
		if ( !empty($delete_by_userid) && $this->userid != $delete_by_userid && $this->wall_userid != $delete_by_userid )
			return 'Error: you have no permission to delete this topic.';
		if ( ! tep_db_is_connected() ) 
			tep_db_connect();
		tep_db_perform_one_row('DELETE FROM '.TABLE_FORUM_TOPICS.' WHERE topicid = "'.$this->topicid.'" LIMIT 1');
		tep_db_perform_one_row('DELETE FROM '.TABLE_FORUM_TOPICS.' WHERE parent_topicid = "'.$this->topicid.'" ');
	}
	
	function get_time_passed()
	{
		$p = floor($this->created_minutes_ago / (60 * 24 * 365));
		if ( $p > 0 )
			return $p.' year'.($p > 1?'s':'');
		$p = floor($this->created_minutes_ago / (60 * 24 * 30));
		if ( $p > 0 )
			return $p.' month'.($p > 1?'s':'');
		$p = floor($this->created_minutes_ago / (60 * 24));
		if ( $p > 0 )
			return $p.' day'.($p > 1?'s':'');
		$p = floor($this->created_minutes_ago / 60);
		if ( $p > 0 )
			return $p.' hour'.($p > 1?'s':'');
		return $this->created_minutes_ago.' minutes';
	}

	function get_sub_topics()
	{
		if ( ! tep_db_is_connected() ) 
			tep_db_connect();
		$result = array();
		$q = 'SELECT topicid FROM '.TABLE_FORUM_TOPICS.' WHERE parent_topicid = "'.$this->topicid.'" ORDER BY created DESC';
		if ( $r = tep_db_query($q, 'db_link', !empty($_COOKIE['debug']) ) ) {
			while ( $row = tep_db_fetch_array($r) ) {
					$topic = new Topic();
					if ( $topic->read_data($row['topicid']) )
						$result[] = $topic;
			}
		}
		return $result;
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
	
	function update($text, $userid = '', $wall_userid = '', $parent_topicid = '', $update_by_userid = '', $private_chat = 0, $projectid = '0', $post_to_global_chat = 0, $convert_to_html_entities = 1)
	{
		if ( empty($text) ) 
			return 'Error: Please enter topic text.';
		$data = array();
		while ( is_integer(stripos($text, '<java')) ) delete_text_between_tags($text, '<java', '>');
		while ( is_integer(stripos($text, '<script')) ) delete_text_between_tags($text, '<script', '>');
		while ( is_integer(stripos($text, '<iframe')) ) delete_text_between_tags($text, '<iframe', '>');
		while ( is_integer(stripos($text, '<frame')) ) delete_text_between_tags($text, '<frame', '>');
		if ( isset($text) ) {
			if ( $convert_to_html_entities )
				$data['text'] = mb_convert_encoding($text, 'HTML-ENTITIES', 'UTF-8');
			else
				$data['text'] = $text;
		}
		if ( ! tep_db_is_connected() ) 
			tep_db_connect();
		if ( !empty($this->topicid) && !$post_to_global_chat ) {
			if ( !empty($update_by_userid) && $this->userid != $update_by_userid )
				return 'Error: you have no permission to edit this topic.';

			if ( ! tep_db_perform(TABLE_FORUM_TOPICS, $data, 'update', 'topicid = "'.$this->topicid.'"', 'db_link', !empty($_COOKIE['debug']) ) )
				return 'Error: cannot update topic.';
		}
		else {
			
			if ( !empty($userid) )
				$this->userid = $userid;
			if ( !$this->init_owner()->get_rank_value('feedbacks') && ( !empty($parent_topicid) || $this->userid != $wall_userid ))
				return 'Error: your rank does not give you permission to post comments.';
			if ( $this->init_owner()->disabled )
				return 'Error: you do not have permission to post comments.';
			if ( $post_to_global_chat) {
				$data['CRC'] = substr($data['text'], 0, 256);
				unset($data['text']);
				$data['_created'] = 'NOW()';
				$data['userid'] = $this->userid;
				$data['user_websiteid'] = WEBSITE_NUMBER;
				$data['description'] = tep_sanitize_string($this->init_owner()->firstname, 128);
				$data['currency'] = tep_sanitize_string($projectid, 10);
				$data['var_text1'] = tep_sanitize_string(SITE_DOMAIN.$this->init_owner()->get_photo(), 128);
				$data['type'] = TYPE_CHAT;
				$data['status'] = 'A';
				if ( !tep_db_perform(TABLE_COMMON_TRANSACTIONS, $data, 'insert', '', 'db_link', !empty($_COOKIE['debug']) ) ) {
					global $last_db_error;
					return 'Error: cannot create topic. DB error: '.$last_db_error;
				}
			}
			else {
				$data['_created'] = 'NOW()';
				$data['userid'] = $this->userid;
				$data['private_chat'] = $private_chat;
				$data['projectid'] = tep_sanitize_string($projectid, 10);
				if ( !empty($wall_userid) )
					$data['wall_userid'] = $wall_userid;
				if ( !empty($parent_topicid) ) {
					$data['parent_topicid'] = $parent_topicid;
				}
				if ( !tep_db_perform(TABLE_FORUM_TOPICS, $data, 'insert', '', 'db_link', !empty($_COOKIE['debug']) ) )
					return 'Error: cannot create topic.';
			}
		}
		return '';
	}

	function reply($text, $userid = '', $wall_userid = '', $update_by_userid = '')
	{
		if ( tep_db_perform_one_row('SELECT topicid FROM '.TABLE_FORUM_TOPICS.' WHERE parent_topicid = "'.$this->topicid.'" AND userid = "'.$userid.'" LIMIT 1') )
			return 'Error: you already replied to this topic.';
		$topic = new Topic();
		return $topic->update($text, $userid, $wall_userid, $this->topicid, $update_by_userid);
	}

	function delete_all_user_topics($userid = '')
	{
		if ( ! tep_db_is_connected() ) 
			tep_db_connect();
		if ( !empty($userid) )
			$this->userid = $userid;
		tep_db_perform_one_row('DELETE FROM '.TABLE_FORUM_TOPICS.' WHERE userid = "'.$this->userid.'"');
	}

	function get_list($sort_by = 'created ASC', $condition = 'NOT disabled AND NOT banned', $limit = '')
	{
		if ( ! tep_db_is_connected() ) 
			tep_db_connect();
		if ( empty($sort_by) )
			$sort_by = 'created ASC';
	
		$topics_objects = array();
		$q = 'SELECT topicid FROM '.TABLE_FORUM_TOPICS.' AS forum_topics WHERE '.$condition.' ORDER BY '.$sort_by.' '.$limit;
		if ( $r = tep_db_query($q, 'db_link', !empty($_COOKIE['debug']) ) ) {
			while ( $row = tep_db_fetch_array($r) ) {
				$topic = new Topic();
				if ( $topic->read_data($row['topicid']) )
					$topics_objects[] = $topic;
			}
		}
		return $topics_objects;
	}
	*/
}
?>