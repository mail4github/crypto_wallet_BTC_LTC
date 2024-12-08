<?php
class Widget
{
	var $widgetid = '';
	var $variables = array();
	var $code = '';
	/*
	function read_data($widgetid = '')
	{
		if ( ! tep_db_is_connected() ) 
			tep_db_connect();
		if ( !empty($widgetid) )
			$this->widgetid = $widgetid;
		if ( !empty($this->widgetid) ) {
			$q = 'SELECT *, 
				TIMESTAMPDIFF( MINUTE, modified, NOW() ) AS modified_minutes_ago
				FROM '.TABLE_WIDGETS.' WHERE widgetid = "'.tep_sanitize_string(remove_injections($this->widgetid), 10).'" LIMIT 1 ';
			if ( $this->variables = tep_db_perform_one_row($q, !empty($_COOKIE['debug'])) ) {
				foreach( $this->variables as $akey => $aval ) 
					$this->$akey = $aval;
				$this->code = '';
				if ( defined('DIR_WS_TEMP_CUSTOM_CODE') ) {
					$file_code = DIR_WS_TEMP_CUSTOM_CODE.'$$$widget_'.strtolower($this->widgetid).'.php';
					if ( file_exists($file_code) )
						$this->code = file_get_contents($file_code);
				}
				if ( empty($this->code) ) {
					$file_code = DIR_WS_INCLUDES.'$$$widget_'.strtolower($this->widgetid).'.php';
					if ( file_exists($file_code) )
						$this->code = file_get_contents($file_code);
				}
				return true;
			}
		}
		return false;
	}

	function delete()
	{
		if ( empty($this->widgetid) )
			return 'Error: cannot delete Widget.';
		if ( !$this->disabled )
			return 'Error: cannot delete Widget. Widget should be disabled first';
		if ( ! tep_db_is_connected() ) 
			tep_db_connect();
		tep_db_perform_one_row('DELETE FROM '.TABLE_WIDGETS.' WHERE widgetid = "'.$this->widgetid.'"');
	}*/
	
	function read_from_data($data)
	{
		$this->variables = $data;
		foreach( $this->variables as $akey => $aval ) 
			$this->$akey = $aval;
		$this->code = '';
		if ( defined('DIR_WS_TEMP_CUSTOM_CODE') ) {
			$file_code = DIR_WS_TEMP_CUSTOM_CODE.'$$$widget_'.strtolower($this->widgetid).'.php';
			if ( file_exists($file_code) )
				$this->code = file_get_contents($file_code);
		}
		if ( empty($this->code) ) {
			$file_code = DIR_WS_INCLUDES.'$$$widget_'.strtolower($this->widgetid).'.php';
			if ( file_exists($file_code) )
				$this->code = file_get_contents($file_code);
		}
	}

	function get_list_of_widgets($sort_by = 1, $condition = 1, $limit = 0)
	{
		$widget_arrays = get_api_value('user_get_list_of_widgets', '', array('sort_by' => $sort_by, 'condition' => $condition, 'limit' => $limit));
		//var_dump($widget_arrays); 
		$widgets_objects = array();
		if ( $widget_arrays ) {
			foreach ($widget_arrays as $widget_array) {
				$widget = new Widget();
				$widget->read_from_data($widget_array);
				$widgets_objects[] = $widget;
				//var_dump($widget); 
				//exit;
				//break;
			}
		}
		return $widgets_objects;
	}
}
?>