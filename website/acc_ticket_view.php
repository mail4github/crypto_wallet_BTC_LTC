<?php
if ( !empty($_GET['ticketid']) ) 
	$ticketid = $_GET['ticketid'];

if ( !empty($_POST['ticketid']) ) 
	$ticketid = $_POST['ticketid'];
	
if ( empty($ticketid) )	{
	header('Location: /acc_tickets.php', true, 301);
	exit;
}

require('../includes/application_top.php');
require(DIR_WS_INCLUDES.'account_common_top.php');
include_once(DIR_WS_CLASSES.'ticket.inc.php');

if ( $_POST['page'] == 'delete_ticket' ) {
	$ticket = new ticket();
	$ticket->get_info($ticketid, $user_account->userid);
	$ticket->delete();
}

if ( $_POST['page'] == 'close_ticket' ) {
	$ticket = new ticket();
	$ticket->get_info($ticketid, $user_account->userid);
	$ticket->close();
}
$box_message = '';
$ticket = new ticket();
if ( !$ticket->get_info($ticketid, $user_account->userid) )
	$box_message = 'Error: Cannot read this ticket';

$receiver_id = '';
if ( defined('CLASS_REPLACE_User') && CLASS_REPLACE_User != '' ) {
	$user_class_name = CLASS_REPLACE_User;
	$receiver = new $user_class_name();
}
else
	$receiver = new User();

$receiver->userid = $ticket->fromuserid;

if ( $receiver->read_data(false) )
	$receiver_id = $receiver->userid;

$error_message = '';
if ( !empty($_POST['respoderid']) ) {
	$userid = $_POST['userid'];
	$subject = base64_decode($_POST['subject']);
	$ticketid = $_POST['ticketid'];
	$respoderid = $_POST['respoderid'];
	$ticket->init_owner($receiver_id);
	$error_message = $ticket->send_answer(str_replace( "\r\n", '<br>', $_POST['ticket_answer'] ), '', $user_account);
	echo $error_message;
	if ( !empty($_POST['ajax']) )
		exit;
	else
		$ticket->get_info();
}
$page_header = 'Ticket #'.$ticket->ticketid;
$page_title = $page_header;
$page_desc = 'Track and answer questions come from your customers.';
require(DIR_WS_INCLUDES.'header.php');
?>
<style type="text/css" media="all"> 
#table_div p{padding-left:20px; padding-right:0px;}
#table_div textarea{margin-left:20px;}
</style>
<table class="table" cellspacing="0" cellpadding="0" border="0">
<tr>
	<td>
		<strong>From:</strong> 
		<?php
			if ( $user_account->is_manager() ) {
				if ( !empty($ticket->fromuserid) )
					echo '<a href="/acc_viewuser.php?userid='.$ticket->fromuserid.'" target="_blank">'.$ticket->fromname.'</a>';
				else
					echo $ticket->fromname.',&nbsp;&nbsp;'.$ticket->fromemail;
			}
			else
				echo $ticket->fromname;
		?>
		<form action="" method="post" style="display: inline;">
		<input type="hidden" name="page" value="delete_ticket">
		<input type="hidden" name="ticketid" value="<?php echo $ticket->ticketid; ?>">
		<button class="btn btn-danger btn-xs" style="float:right; padding-right:10px; <?php echo ($ticket->status != "C"?'display:none;':''); ?>" name="delete_post" onClick="return ( confirm('Do you really want to delete this ticket?') );">Delete Ticket</button>
		</form>
		<form action="" method="post" style="display: inline;">
		<input type="hidden" name="page" value="close_ticket">
		<input type="hidden" name="ticketid" value="<?php echo $ticket->ticketid; ?>">
		<button class="btn btn-success btn-xs" style="float:right; padding-right:10px; <?php echo ($ticket->status == "C"?'display:none;':''); ?>" name="close_post" onClick="return ( confirm('Do you really want to close this ticket?') );">Close Ticket</button>
		</form>
	</td>
</tr>
<tr>
	<td>
		<p><strong>Ticket Status:</strong> <?php echo $ticket->t_status; ?><span style="float:right; padding-right:10px;"><strong>Created:</strong> <span class="local_pline_date_and_hour" unix_time="<?php echo $ticket->unix_t_created; ?>"><?php echo $ticket->t_created; ?></span></span></p>
	</td>
</tr>
<tr>
	<td>
		<p>Subject: <strong><?php echo $ticket->subject; ?></strong></p>
	</td>
</tr>
<tr>
	<td>
		<p><strong>Question:</strong></p>
		<p><?php 
			echo $ticket->message; 
		?></p>
		<?php
		if ( $answers = $ticket->get_answers(true) ) {
			foreach($answers as $akey => $aval) {
				echo '
				<p><strong>Answer:</strong> <span style="float:right; padding-right:10px;" class="local_pline_date_and_hour" unix_time="'.$aval['unix_t_created'].'">'.$aval['a_created'].'</span></p>
				<div style="overflow:auto">
					<p style="padding-top:0px;">'.str_replace( "\r\n", '', $aval['answer'] ).'</p>
				</div>';
			}
			echo '<p><strong>New Answer:</strong>'."\r\n";
		}
		else {
			$answer = $ticket->parse_answer_from_db('', false, $receiver, $ticketid);
			if ( !empty($answer) ) {
				echo '
				<p><strong>Suggested Answer:</strong></p>
				<div class="account_webpages_cell" style="overflow:auto; margin-left:20px; margin-right:20px;">
					<p style="padding-top:10px;"><div id="sug_answer">'.$answer.'</div></p>
				</div>
				<button class="btn btn-link" onclick="copy_to_answer(); return false;"><span class="glyphicon glyphicon-arrow-down" aria-hidden="true"></span>&nbsp;&nbsp; Copy Answer</button>';
			}
			echo '<p><strong>Answer:</strong>'."\r\n";
		}
		?>
		</p>
		<form name="user_frm" method="post">
			<input type="hidden" name="ticketid" value="<?php echo $ticketid; ?>">
			<input type="hidden" name="userid" value="<?php echo $ticket->userid; ?>">
			<input type="hidden" name="subject" value="<?php echo base64_encode($ticket->subject); ?>">
			<input type="hidden" name="respoderid" value="<?php echo $user_account->userid; ?>">
			<textarea class="form-control" rows="6" name="ticket_answer" id="answer_textarea"  minlength="10" required ></textarea>
			<div align="center">
				<button class="btn btn-primary btn-lg" name="answer_btn" style="margin-top:10px;">Send Answer</button>
			</div>
		</form>
	</td>
</tr>
</table>
<SCRIPT language="JavaScript">
function copy_to_answer()
{
	var tag = document.createElement("div");
	var s = $("#sug_answer").html();
	while ( s.indexOf("\r") >= 0 )
		s = s.replace("\r", "");
	while ( s.indexOf("\n") >= 0 )
		s = s.replace("\n", "");
    tag.innerHTML = s;
    $("#answer_textarea").html( tag.innerText );
}
</script>
<button class="btn btn-link btn-lg" style="margin:0px 0 40px 0;" onclick="document.location.replace('/acc_tickets.php');"><span class="glyphicon glyphicon-backward" aria-hidden="true"></span>&nbsp;&nbsp; Back to Trouble Tickets</button>
<br>
<?php
require(DIR_WS_INCLUDES.'footer.php');
require(DIR_WS_INCLUDES.'box_message.php');
?>
</body>
</html>
