<?php
require_once(DIR_WS_CLASSES.'job.class.php');
require_once(DIR_WS_CLASSES.'user.class.php');

class Job_sent
{
	var $sentid = 0;
	var $variables = array();
	var $job;
	
	function read_data($sentid = '', $submitted_domain = '', $userid = '')
	{
		if ( !empty($sentid) && empty($submitted_domain) && empty($userid) )
			$this->sentid = $sentid; 
		$this->variables = get_api_value('job_sent_read_data', '', array('sentid' => $this->sentid, 'submitted_domain' => $submitted_domain));
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
	
	function start_do_job($jobid, $userid, $job_description = '', $display_text = '', $var_text1 = '', $var_text2 = '', $var_int1 = 0, $var_int2 = 0, $var_int3 = 0, $job_is_done = false)
	{
		if ( !empty($jobid) )
			$this->jobid = $jobid;
		if ( empty($this->jobid) )
			return 'Error: empty jobid.';
		if ( !empty($userid) )
			$this->userid = $userid;
		if ( empty($this->userid) )
			return 'Error: empty userid.';
		
		return get_api_value('job_start_do_job', '', array('jobid' => $jobid, 'job_description' => $job_description, 'display_text' => $display_text, 'var_text1' => $var_text1, 'var_text2' => $var_text2, 'var_int1' => $var_int1, 'var_int2' => $var_int2, 'var_int3' => $var_int3, 'job_is_done' => $job_is_done));
	}
	
	function search_unfinished_job($additional = 0)
	{
		return get_api_value('job_search_unfinished_job', '', array('additional' => $additional, 'userid' => $this->userid, 'jobid' => $this->jobid));
	}
	
	function get_job()
	{
		if ( !$this->job ) {
			$this->job = new job();
			$this->job->read_data($this->jobid);
			$this->job->var_text1 = $this->var_text1; 
			$this->job->var_text2 = $this->var_text2; 
			$this->job->var_int1 = $this->var_int1; 
			$this->job->var_int2 = $this->var_int2; 
			$this->job->var_int3 = $this->var_int3; 
		}
		return $this->job;
	}
	
	function job_due_date()
	{
		return get_api_value('job_due_date', '', array('sentid' => $this->sentid));
	}
	
	function approve_job($status = 'AP', $transaction_note = 'Reward for job', $result, $force_action = false, $delay = '', $paid = '')
	{
		return get_api_value('job_approve', '', array('sentid' => $this->sentid, 'status' => $status, 'transaction_note' => $transaction_note, 'result' => $result, 'force_action' => $force_action, 'delay' => $delay, 'paid' => $paid));
	}
	
	function get_job_description()
	{
		$s = $this->description;
		$s = str_replace('{$sentid}', $this->sentid, $s);
		$s = str_replace('{$status}', $this->status, $s);
		return $s;
	}
	
	function get_job_text($sentid)
	{
		$s = $this->display_text;
		$s = str_replace('{$sentid}', $this->sentid, $s);
		$s = str_replace('{$status}', $this->status, $s);
		return $s;
	}
	
	function search_positive_and_negative_words($mask, &$positive_words, &$negative_words)
	{
		$positive_words = array();
		$negative_words = array();
		if ( empty($mask) )
			return false;
		if ( is_integer(strpos($mask, '+(')) || is_integer(strpos($mask, '-(')) ) {
			$must_sub_str = $mask;
			$s = get_text_between_tags($must_sub_str, '+(', ')');
			delete_text_between_tags($must_sub_str, '+(', ')');
			while ( !empty($s) ) {
				$positive_words[] = $s;
				$s = get_text_between_tags($must_sub_str, '+(', ')');
				delete_text_between_tags($must_sub_str, '+(', ')');
			}
			$s = get_text_between_tags($must_sub_str, '-(', ')');
			delete_text_between_tags($must_sub_str, '-(', ')');
			while ( !empty($s) ) {
				$negative_words[] = $s;
				$s = get_text_between_tags($must_sub_str, '-(', ')');
				delete_text_between_tags($must_sub_str, '-(', ')');
			}
			if ( !empty($must_sub_str) )
				$positive_words[] = $must_sub_str;
		}
		else
			$positive_words[] = $mask;
	}
	
	function done_job($submit_skipped = 0, $result = '', $status = 'AP', $as_is = false, $var_text1 = '', $var_text2 = '', $var_int1 = null, $var_int2 = null, $var_int3 = null, $force_action = false)
	{
		if ( !$force_action && ( $this->status == 'DC' || $this->status == 'AP' || $this->skipped ) ) 
			return 'Error: job already done.';

		if ( $submit_skipped )
			$status = '';
		
		$message = get_api_value('job_done', '', array('sentid' => $this->sentid, 'submit_skipped' => $submit_skipped, 'result' => $result, 'status' => $status, 'as_is' => $as_is, 'var_text1' => $var_text1, 'var_text2' => $var_text2, 'var_int1' => $var_int1, 'var_int2' => $var_int2, 'var_int3' => $var_int3, 'force_action' => $force_action));
		$this->read_data();
		return $message;
	}

	function make_experts_decision(Job_sent $reviewed_job = null)
	{
		return get_api_value('job_make_experts_decision', '', array('sentid' => $this->sentid, 'reviewed_jobid' => $reviewed_job->jobid));
	}

}
?>