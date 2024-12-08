<?php
function paging($current_page_number = 0, $total_rows = 10, $rows_per_page = 10, $row_number = 0, $custom_goto_page = '', $print_result = true, $next_word = 'NEXT', $prev_word = 'PREV', $show_total_label = true)
{
	// PAGING
	if ($rows_per_page <= 0)
		return '';
	$result = '';
	if ( $total_rows > $rows_per_page ) {
		$result = $result.'<nav><ul class="pagination" style="margin:0px;">';
		if ( $row_number > 0 ) {
			if ( empty($custom_goto_page) )
				$result = $result.'<li><a href="javascript: GoToPage('.($current_page_number - 1).');" class="sorted_table_paging">&laquo; '.$prev_word.' </a></li>'."\n";
			else
				$result = $result.'<li><a href="'.str_replace('#pagenmb#', ($current_page_number - 1), $custom_goto_page).'" class="sorted_table_paging">&laquo; '.$prev_word.' </a></li>'."\n";
		}
		else {
			if ( empty($custom_goto_page) )
				$result = $result.'<li class="disabled" onclick="return false;"><a href="#">&laquo;</a></li>'."\n";
			else
				$result = $result.'<li class="disabled" onclick="return false;"><a href="#">&laquo;</a></li>'."\n";
		}

		if ( $total_rows / $rows_per_page > 10 ) {
			$end_firstPage = 1;
			$mid_firstPage = $current_page_number - 3;
			if ( $mid_firstPage < 0 ) 
				$mid_firstPage = 0;
			
			if ( $current_page_number > round($total_rows / $rows_per_page) - 2 ) {
				$mid_firstPage = 0;
				$end_firstPage = 6;
				$firstLastPage = 2;
			}
			else
			if ( $mid_firstPage > 0 )
				$firstLastPage = 2;
			else
			    $firstLastPage = 10;
				
			$mid_lastPage = $current_page_number + 3;
			if ($mid_lastPage > $total_rows / $rows_per_page - 1)
				$mid_lastPage = round($total_rows / $rows_per_page) - 1;
			
			for($p = 0; ($p * $rows_per_page) < ($rows_per_page * $firstLastPage); $p++) 
			{
			    if ($p + 1 != $current_page_number) {
					if ( empty($custom_goto_page) )
						$result = $result.'<li><a href="javascript: GoToPage('.($p + 1).');" class="sorted_table_paging">'.($p + 1).'</a></li>'."\n";
					else
						$result = $result.'<li><a href="'.str_replace('#pagenmb#', ($p + 1), $custom_goto_page).'" class="sorted_table_paging">'.($p + 1).'</a></li>'."\n";
				}
				else 
					$result = $result.' <li class="active" onclick="return false;"><a href="#">'.($p + 1).' <span class="sr-only">(current)</span></a></li> ';
					
			}
			$result = $result.'<li class="disabled" onclick="return false;"><a href="#">...</a></li>'."\n";
			if ( $mid_firstPage > 0 ) {
				for($p = $mid_firstPage; ($p * $rows_per_page) < ($rows_per_page * $mid_lastPage); $p++) 
				{
				    if ($p + 1 != $current_page_number) {
						if ( empty($custom_goto_page) )
							$result = $result.'<li><a href="javascript: GoToPage('.($p + 1).');" class="sorted_table_paging">'.($p + 1).'</a></li>'."\n";
						else
							$result = $result.'<li><a href="'.str_replace('#pagenmb#', ($p + 1), $custom_goto_page).'" class="sorted_table_paging">'.($p + 1).'</a></li>'."\n";
					}
					else 
						$result = $result.' <li class="active" onclick="return false;"><a href="#">'.($p + 1).' <span class="sr-only">(current)</span></a></li> ';
				}
				$result = $result.'<li class="disabled" onclick="return false;"><a href="#">...</a></li>'."\n";
			}
			for($p = round($total_rows / $rows_per_page) - $end_firstPage; ($p * $rows_per_page) < $total_rows; $p++) 
			{
			    if ($p + 1 != $current_page_number) {
					if ( empty($custom_goto_page) )
						$result = $result.'<li><a href="javascript: GoToPage('.($p + 1).');" class="sorted_table_paging">'.($p + 1).'</a></li>'."\n";
					else
						$result = $result.'<li><a href="'.str_replace('#pagenmb#', ($p + 1), $custom_goto_page).'" class="sorted_table_paging">'.($p + 1).'</a></li>'."\n";
				}
				else
					$result = $result.' <li class="active" onclick="return false;"><a href="#">'.($p + 1).' <span class="sr-only">(current)</span></a></li> ';
			}
		}
		else {
			for($p = 0; ($p * $rows_per_page) < $total_rows; $p++) 
			{
			    if ($p + 1 != $current_page_number) {
					if ( empty($custom_goto_page) )
						$result = $result.'<li><a href="javascript: GoToPage('.($p + 1).');" class="sorted_table_paging">'.($p + 1).'</a></li>'."\n";
					else
						$result = $result.'<li><a href="'.str_replace('#pagenmb#', ($p + 1), $custom_goto_page).'" class="sorted_table_paging">'.($p + 1).'</a></li>'."\n";
				}
				else 
				   $result = $result.' <li class="active" onclick="return false;"><a href="#">'.($p + 1).' <span class="sr-only">(current)</span></a></li> ';
			}
		}
		if ($total_rows > ($row_number + $rows_per_page) ) {
			if ( empty($custom_goto_page) )
				$result = $result.'<li><a href="javascript: GoToPage('.($current_page_number + 1).');" class="sorted_table_paging">'.$next_word.' &raquo; </a></li>'."\n";
			else
				$result = $result.'<li><a href="'.str_replace('#pagenmb#', ($current_page_number + 1), $custom_goto_page).'" class="sorted_table_paging">&raquo;</a></li>'."\n";
		}
		$result = $result.'</ul>'."\n";
		if ($show_total_label)
			$result = $result.'<br><span class="label label-default sorted_table_paging">total: <strong>'.$total_rows.'</strong> rows</span></nav>'."\n";
		$result = $result.'</nav>'."\n";
	}
	if ($print_result)
		echo $result;
	return $result;
}

$sorted_table_page_name = $_SERVER['SCRIPT_NAME'];
$sorted_table_page_name = str_replace('\\', '', $sorted_table_page_name);
$sorted_table_page_name = str_replace('/', '', $sorted_table_page_name);
$sorted_table_page_name = str_replace('.', '', $sorted_table_page_name);

function print_sorted_table(
	$table_name = '', 
	$header = '', 
	$additional_params = NULL,
	$row_html = '', 
	$max_ros = '', 
	&$current_page_number = 0,
	&$row_number = 0,
	&$total_rows = 0,
	&$output_str = 0,
	$row_html_odd = '',
	$table_tag = '<table cellspacing="0" cellpadding="0" border="0" style="width:100%;">',
	$td_html = '',
	$td_html_odd = '',
	$default_sort_column = 0, 
	$default_sort_order = 'ASC',
	$whole_row_html = '',
	$whole_header_html = '',
	$sorted_column_label_mask = '',
	$not_sorted_column_label_mask = '',
	$read_page_nmb_from_cookie = true,
	$row_eval = '',
	$data_array = NULL,
	$whole_footer_html = '',
	$always_use_default_sort = false,
	$number_of_grid_columns = 0,
	$grid_row_prefix = '<div class="row">',
	$grid_row_suffix = '</div>',
	&$sort_column = 0,
	&$sort_order = 0,
	$user = null
	)
{
	global $showDebugMessages;
	global $sorted_table_page_name;
	$table_printed = false;
	$output_str = '';
	if ( !empty($_COOKIE['sort_order'.$sorted_table_page_name]) && !$always_use_default_sort )
		$sort_order = $_COOKIE['sort_order'.$sorted_table_page_name];
	else
		$sort_order = $default_sort_order;
		
	if ( $sort_order == 'ASC' ) {
		$sort_order_text_name = 'A...Z';
		$sort_order_numb_name = '0...9';
	}
	else {
		$sort_order_text_name = 'Z...A';
		$sort_order_numb_name = '9...0';
	}
	
	if ( !empty($sorted_column_label_mask) ) {
		$sorted_column_label_mask = str_replace('#sort_order_text_name#', $sort_order_text_name, $sorted_column_label_mask);
		$sorted_column_label_mask = str_replace('#sort_order_numb_name#', $sort_order_numb_name, $sorted_column_label_mask);
		$not_sorted_column_label_mask = str_replace('#sort_order_text_name#', $sort_order_text_name, $not_sorted_column_label_mask);
		$not_sorted_column_label_mask = str_replace('#sort_order_numb_name#', $sort_order_numb_name, $not_sorted_column_label_mask);
	}
	
	if ( isset($_COOKIE['sort_column'.$sorted_table_page_name]) && !$always_use_default_sort )
		$sort_column = $_COOKIE['sort_column'.$sorted_table_page_name];
	else
		$sort_column = $default_sort_column;
	$current_page_number = 1;
	if ( $read_page_nmb_from_cookie ) {
		if ( !empty($_COOKIE['page_number'.$sorted_table_page_name]) )
			$current_page_number = $_COOKIE['page_number'.$sorted_table_page_name];
	}
	else {
		$current_page_number = @$_GET['pagenumber'];
	}
	
	if (empty($current_page_number))
		$current_page_number = 1;

	$row_number = 0;
	
	if ($current_page_number > 1 && $max_ros > 0) 
		$row_number = $row_number + ($current_page_number - 1) * $max_ros;
	else {
		$row_number = 0;
		$current_page_number = 1;
	}
	if ( !isset($data_array) ) {
		$params = array('table_name' => $table_name, 'sort_column' => $sort_column, 'sort_order' => $sort_order, 'row_number' => $row_number, 'current_page_number' => $current_page_number, 'max_ros' => $max_ros);
		if ( isset($additional_params) && is_array($additional_params) && count($additional_params) > 0 )
			$params = array_merge($params, $additional_params);
		$data = make_api_request('get_sorted_table', '', $params, '', $user);
		
		//if (!empty(@$_GET['debug'])) var_dump($data); exit;

		if ( $data['success'] ) {
			$res = $data['values'];
			$data_array = $res['table'];
			$current_page_number = $res['current_page_number'];
			$total_rows = $res['total_rows'];
			$row_number = $res['row_number'];
		}
		else {
			return false;
		}
	}
	if ( isset($data_array) && count($data_array) > 0 ) {
		$output_str = $output_str.$table_tag;
		if ( empty($whole_header_html) ) {
			$output_str = $output_str."<tr>\r\n";
			for ($i = 0; $i < count($header); $i++) {
				$output_str = $output_str."<td>\r\n";
				$s = $header[$i];
				
				if ( !empty($sorted_column_label_mask) && $sort_column == $i )
					$s = str_replace('#sort_label#', $sorted_column_label_mask, $s);
				else
					$s = str_replace('#sort_label#', $not_sorted_column_label_mask, $s);
					
				$s = '<a class=sorted_table_link href="javascript: RedrawWithSort('.$i.');">' . $s . '</a>';
				
				$output_str = $output_str.$s."\r\n";
				$output_str = $output_str."</td>\r\n";
			}
			$output_str = $output_str."</tr>\r\n";
		}
		else {
			$s = $whole_header_html;
			if ( !empty($sorted_column_label_mask) ) {
				for ($i = 0; $i < 20; $i++) {
					if ( $sort_column == $i )
						$s = str_replace('#sort_label'.($i).'#', $sorted_column_label_mask, $s);
					else
						$s = str_replace('#sort_label'.($i).'#', $not_sorted_column_label_mask, $s);
				}
			}
			$output_str = $output_str.$s;
		}
		if ( $number_of_grid_columns > 0 )
			$output_str = $output_str.$grid_row_prefix;
		$col_count = 0;
		for ($j = 0; $j < count($data_array); $j++) {
			$row = $data_array[$j];
			$i = 0;
			$this_whole_row_html = $whole_row_html;
			if ( empty($this_whole_row_html) ) 
				$output_str = $output_str."<tr>\r\n";
			if ( !empty($row_eval) ) {
				try {
					eval($row_eval);
				}
				catch (Exception $e) {}
			}
			foreach ($row as $key => $value) {
				if ( !empty($this_whole_row_html) ) {
					$this_whole_row_html = str_replace( '#'.$key.'#', $value, $this_whole_row_html );
					$this_whole_row_html = str_replace( 'hex%'.$key.'%', bin2hex(urlencode($value)), $this_whole_row_html );
					if ( !empty($td_html_odd) ) {
						if ( $j & 1 ) {
							$this_whole_row_html = str_replace( '$html_odd$', $td_html_odd, $this_whole_row_html );
							$this_whole_row_html = str_replace( '$html_even$', '', $this_whole_row_html );
						}
						else {
							$this_whole_row_html = str_replace( '$html_even$', $td_html, $this_whole_row_html );
							$this_whole_row_html = str_replace( '$html_odd$', '', $this_whole_row_html );
						}
					}
				}
				else {
					if ( !empty($td_html_odd) ) {
						if ( $j & 1 )
							$output_str = $output_str."<td ".$td_html_odd." >\r\n";
						else
							$output_str = $output_str."<td ".$td_html." >\r\n";
					}
					else 
						$output_str = $output_str."<td ".$td_html." >\r\n";
					
					if ( !empty($row_html_odd) ) {
						if ( $j & 1 )
							$s = str_replace( '#value#', $value, $row_html_odd[$i] );
						else
							$s = str_replace( '#value#', $value, $row_html[$i] );
					}
					else
						$s = str_replace( '#value#', $value, $row_html[$i] );
					$output_str = $output_str.$s."\r\n";
					$output_str = $output_str."</td>\r\n";
				}
				$i = $i + 1;
			}
			if ( !empty($this_whole_row_html) ) 
				$output_str = $output_str.translate_str($this_whole_row_html);
			else
				$output_str = $output_str."</tr>\r\n";
			$col_count++;
			if ( $col_count >= $number_of_grid_columns && $number_of_grid_columns > 0 ) {
				$output_str = $output_str.$grid_row_suffix.$grid_row_prefix;
				$col_count = 0;
			}
		}

		if ( $number_of_grid_columns > 0 )
			$output_str = $output_str.$grid_row_suffix;

		$output_str = $output_str.$whole_footer_html.(!empty($table_tag)?"</table>":"")."\r\n";
		$table_printed = true;
	}
	return $table_printed;
}
?>
<script Language="JavaScript">

function SetCookie(name, value) {  
 var argv = SetCookie.arguments;  
 var argc = SetCookie.arguments.length;  
 var expire_days = (argc > 2) ? argv[2] : null;
 if ( expire_days != null ) {
 	var expDate = new Date();
	expDate.setTime(expDate.getTime() +  (24 * 60 * 60 * 1000 * expire_days)); 
 }
 
 var path = (argc > 3) ? argv[3] : null;  
 var domain = (argc > 4) ? argv[4] : null;  
 var secure = (argc > 5) ? argv[5] : false;  
 document.cookie = name + "=" + escape (value) + 
    ((expire_days == null) ? "" : ("; expires=" + expDate.toUTCString())) + 
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

function RedrawWithSort(sort_column, sort_order, reload_page)
{
	if ( typeof(reload_page) == 'undefined' )
		reload_page = 1;
	s = get_cookie('sort_column<?php echo $sorted_table_page_name; ?>');
	if ( s == sort_column ) {
		s = get_cookie('sort_order<?php echo $sorted_table_page_name; ?>');
		if ( s == 'ASC' )
			SetCookie('sort_order<?php echo $sorted_table_page_name; ?>', 'DESC', 1);
		else
			SetCookie('sort_order<?php echo $sorted_table_page_name; ?>', 'ASC', 1);
	}
	else
		SetCookie('sort_column<?php echo $sorted_table_page_name; ?>', sort_column, 1);
	if ( sort_order )
		SetCookie('sort_order<?php echo $sorted_table_page_name; ?>', sort_order, 1);
	
	SetCookie('page_number<?php echo $sorted_table_page_name; ?>', 1, 1);

	if ( reload_page )
		location.reload(true);
}
function GoToPage(page_number, reload_page)
{
	if ( typeof(reload_page) == 'undefined' )
		reload_page = 1;
	SetCookie('page_number<?php echo $sorted_table_page_name; ?>', page_number, 1);
	if ( reload_page )
		location.assign(location.href);
}
</script>
