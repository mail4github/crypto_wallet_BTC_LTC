<?php
require_once(DIR_WS_CLASSES.'user.class.php');
if ( defined('CLASS_REPLACE_User') && CLASS_REPLACE_User != '' )
	require_once(DIR_WS_TEMP_CUSTOM_CODE.'user.class.php');
else
	require_once(DIR_WS_CLASSES.'user.class.php');
class ticket
{
	var $userid = 0;
	var $owner;
	var $ticketid = '';
	var $fromuserid = '';
	var $variables = array();
	
	function get_info($aTicketid = '', $auserid = 0)
	{
		if ( !empty($aTicketid) )
			$this->ticketid = $aTicketid;
		if ( !empty($auserid) )
			$this->userid = $auserid;
		if ( !empty($this->ticketid) || !empty($this->userid) ) {
			$variables = get_api_value('ticket_read_data', '', array('ticketid' => $this->ticketid, 'for_userid' => $this->userid));
			if ( $variables ) {
				foreach($variables as $akey => $aval) {
					$this->$akey = base64_decode($aval);
					$this->variables[$akey] = base64_decode($aval);
				}
				return true;
			}
		}
		return false;
	}
	
	function init_owner($userid = '')
	{
		if ( !$this->owner ) {
			if ( !empty($userid) )
				$this->userid = $userid;
			if ( defined('CLASS_REPLACE_User') && CLASS_REPLACE_User != '' ) {
				$user_class_name = CLASS_REPLACE_User;
				$this->owner = new $user_class_name();
			}
			else
				$this->owner = new User();
			$this->owner->userid = $this->userid;
			$this->owner->read_data(false);
		}
		return $this->owner;
	}
	
	function open_ticket($subject, $message, $user = null, $userid = '')
	{
		if ( !empty($userid) )
			$this->init_owner($userid);

		if ( isset($user) )
			$this->owner = $user;

		$error_message = get_api_value('open_trouble_ticket', '', ['subject' => base64_encode($subject), 'message' => base64_encode($message)], '', $this->owner);
		return $error_message;
	}

	function get_answers($user_has_permission_view_all_tickets)
	{
		return get_api_value('ticket_get_answers', '', array('ticketid' => $this->ticketid, 'user_has_permission_view_all_tickets' => $user_has_permission_view_all_tickets));
	}
	
	function send_answer($answer_html ='', $answer_text ='', User $manager = NULL)
	{
		return get_api_value('ticket_send_answer', '', array('ticketid' => $this->ticketid, 'answer_html' => base64_encode($answer_html), 'answer_text' => base64_encode($answer_text)));
	}
	
	function parse_answer_from_db($answer, $plain_text_only = false, User $receiver = NULL, $ticketid = '')
	{
		$userid = 0;
		if (isset($receiver) && $receiver != NULL) {
			$userid = $receiver->userid;
		}
		if ( empty($answer) )
			$answer = base64_decode(get_api_value('ticket_get_answer_from_db', '', ['userid' => $userid, 'ticketid' => $ticketid, 'subject' => base64_encode($this->subject), 'question' => base64_encode($this->message)]));
		return $answer;
	}
	
	function delete()
	{
		return get_api_value('ticket_delete', '', array('ticketid' => $this->ticketid));
	}
	
	function close()
	{
		return get_api_value('ticket_close', '', array('ticketid' => $this->ticketid));
	}

}