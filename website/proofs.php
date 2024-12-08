<?php
require('../includes/application_top.php');
require(DIR_WS_INCLUDES.'account_common_top.php');
$display_in_short = !empty($_GET['display_in_short']);

$page_header = 'Payment Proofs';
$page_title = $page_header;
if ( $display_in_short )
	$_GET['noheader'] = 1;
require(DIR_WS_INCLUDES.'header.php');

if ( is_file_variable_expired(VAR_PAYMENT_PROOFS_NAME, 10) || $user_account->is_loggedin() || defined('DEBUG_MODE') ) {
	include_once(DIR_COMMON_PHP.'print_sorted_table.php');
	$rows_per_page = 10;
	$current_page_number = 1;
	$total_rows = 0;
	$number_of_rows = 0;
	$row_eval = '
		global $number_of_rows;
		$number_of_rows++;
		$processor_name = "";
		global $payout_options;
		foreach ( $payout_options as $value ) {
			if ( $value["id"] == $row["c_payoutoption"] ) {
				$processor_name = $value["name"];
				$processor_website = $value["processor_website"];
				$logo = !empty($value["logo"])?$value["logo"]:"/images/".$value["id"].".gif";
			}
		}
		$row["c_description"] = "
				<a href=\"".$processor_website."\" target=\"_blank\"><img src=\"".$logo."\" width=\"40\" height=\"40\" border=\"0\" alt=\"".$processor_name."\"></a>
		";
		$user = new User();
		$user->userid = $row["c_userid"];
		if ( $user->read_data(false) )
			$row["c_photo"] = $user->get_photo();
		else
			$row["c_photo"] = DIR_WS_WEBSITE_IMAGES_DIR."no_photo_60x60".($row["c_gender"] == "F"?"girl":"boy").".png";
		$row["c_amount"] = currency_format($row["c_amount"]);
	';

	$whole_header_html = '
		<thead>
			<tr>
				<th></th>
				<th></th>
				<th></th>
			</tr>
		</thead>
		<tbody>
	';
	$whole_row_html = '
		<tr>
			<td id="photo_td3#c_userid#" >
				<a href="/acc_wall.php?userid=#c_userid#">
					<img src="#c_photo#" border="0" class="first_page_image" style="width:40px; height:40px;">
				</a>
			<td valign="middle" style="text-align:left; padding-left:4px; padding-right:0px;" id="cell_row_2#c_userid#"><a href="/acc_wall.php?userid=#c_userid#" target="_top" id="wall_link#c_userid#">#c_first_name#</a></td>
			<td valign="middle" style="text-align:right; padding-left:4px; padding-right:4px;" id="cell_row_4#c_userid#">#c_date#</td>
			<td valign="middle" style="text-align:right; padding-left:4px; padding-right:4px;" id="cell_row_5#c_userid#"><font color="#008800">#c_amount#</font></td>
			<td valign="middle" style="" id="cell_row_6#c_userid#">#c_description#</td>
		</tr>
	';
	$row_html = array();
	$table_tag = '<table class="table table-hover" id="main_table" style="">';
	$output_str = '';
	if ( print_sorted_table(
			'payment_proof', 
			$header, 
			NULL, 
			$row_html, 
			$rows_per_page, 
			$current_page_number, 
			$row_number, 
			$total_rows, 
			$output_str,
			'', 
			$table_tag, 
			'class=sorted_table_cell',
			'', 
			0, 
			'DESC', 
			$whole_row_html, 
			$whole_header_html,
			'&nbsp;<img src="/'.DIR_WS_WEBSITE_IMAGES_DIR.'sort_#sort_order_text_name#.png" border="0" alt="">', // $sorted_column_label_mask
			'', //$not_sorted_column_label_mask
			true, // $read_page_nmb_from_cookie
			$row_eval,
			NULL,
			false, // $perform_if_query_not_equal
			'</tbody>' // $whole_footer_html
			)
		)
	{
		if ( !$user_account->is_loggedin() ) {
			hide_show_menu_item($_SERVER['SCRIPT_NAME'], 0);
			update_file_variable(VAR_PAYMENT_PROOFS_NAME, $output_str);
		}
		echo $output_str;
	}
	else {
		$bitcoin = new Bitcoin();
		$row_eval = '
			global $number_of_rows;
			global $bitcoin;
			$number_of_rows++;
			$processor_name = "";
			global $payout_options;
			foreach ( $payout_options as $value ) {
				if ( $value["id"] == $row["c_payoutoption"] ) {
					$processor_name = $value["name"];
					$processor_website = $value["processor_website"];
					$logo = !empty($value["logo"])?$value["logo"]:"/images/".$value["id"].".gif";
				}
			}
			$row["c_description"] = "
					<a href=\"".$processor_website."\" target=\"_blank\"><img src=\"".$logo."\" width=\"40\" height=\"40\" border=\"0\" alt=\"".$processor_name."\"></a>
			";
			$row["c_photo"] = get_text_between_tags($row["c_photo"], "<photo>", "</photo>");
			$row["c_amount"] = currency_format($bitcoin->get_exchange_rate() * $row["c_amount_in_BTC"]);
		';
		$whole_row_html = '
			<tr>
				<td id="photo_td3#c_userid#" >
					<img src="#c_photo#" border="0" class="first_page_image" style="width:40px; height:40px;">
				<td valign="middle" style="text-align:left; padding-left:4px; padding-right:0px;" id="cell_row_2#c_userid#">#c_first_name#</td>
				<td valign="middle" style="text-align:right; padding-left:4px; padding-right:4px;" id="cell_row_4#c_userid#">#c_date#</td>
				<td valign="middle" style="text-align:right; padding-left:4px; padding-right:4px;" id="cell_row_5#c_userid#"><font color="#008800">'./*DOLLAR_SIGN.*/'#c_amount#</font></td>
				<td valign="middle" style="" id="cell_row_6#c_userid#">#c_description#</td>
			</tr>
		';
		$output_str = '';
		if ( print_sorted_table(
				'payment_proof_fake', 
				$header, 
				NULL, 
				$row_html, 
				$rows_per_page, 
				$current_page_number, 
				$row_number, 
				$total_rows, 
				$output_str,
				'', 
				$table_tag, 
				'',
				'', 
				0, 
				'DESC', 
				$whole_row_html, 
				$whole_header_html,
				'', // $sorted_column_label_mask
				'', //$not_sorted_column_label_mask
				true, // $read_page_nmb_from_cookie
				$row_eval
				)
			)
		{
			if ( !$user_account->is_loggedin() ) {
				hide_show_menu_item($_SERVER['SCRIPT_NAME'], 0);
				update_file_variable(VAR_PAYMENT_PROOFS_NAME, $output_str);
			}
			echo $output_str;
		}
		else {
			if ( !$user_account->is_loggedin() ) {
				hide_show_menu_item($_SERVER['SCRIPT_NAME']);
				echo get_file_variable(VAR_PAYMENT_PROOFS_NAME);
			}
			echo '<br><br><br>';
		}
	}
}
else {
	if ( defined('DEBUG_MODE') ) { echo 'from file $$$'.VAR_PAYMENT_PROOFS_NAME.'.txt:<br>'; }
	echo get_file_variable(VAR_PAYMENT_PROOFS_NAME);
}
echo '<br>';
if ( !$display_in_short ) {
	require(DIR_WS_INCLUDES.'footer.php');
	require(DIR_COMMON_PHP.'box_message.php');
	if ( !empty($box_message) ) {
		if ( is_integer(strpos($box_message, 'Error:')) )
			echo '<script language="JavaScript">show_message_box_box("Error", "'.$box_message.'", 2);</script>'."\r\n";
		else
			echo '<script language="JavaScript">show_message_box_box("Success", "'.$box_message.'", 1);</script>'."\r\n";
	}
}
echo '
</body>
</html>
';
?>

