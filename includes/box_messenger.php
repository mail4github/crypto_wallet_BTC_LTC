<?php
function generate_conversation_body_from_array($topic_list, $interlocutorid, $interlocutor_popup, $user_popup)
{
	$res = '';
	$i = 0;
	foreach ($topic_list as $topic) {
		if ( $i == 0 )
			$last_object = $topic['topicid'];
		if ( $topic['userid'] == $interlocutorid  )
			$s = $interlocutor_popup;
		else
			$s = $user_popup;
		foreach ($topic as $key => $value)
			$s = replaceCustomConstantInText($key, $value, $s);
		$res = $res.$s;
	}
	$res = '<div id="topics_list_inner">'.$res.'</div>';
	return $res;
}

function get_messenger_code($user_account = null, $interlocutor_userid = 0, $show_interlocutors = true, $projectid = '', $show_first_messages = 10, $auto_height = false, $global_chat = false, $cannot_post_message = '', $private_chat = 0, $user_popup = '', $interlocutor_popup = '', $messenger_id = 'messenger_container', $placeholder = 'Write your message here...')
{
	if ( $global_chat ) 
		$api_request_suffix = '_global_chat';
	else
		$api_request_suffix = '';
	if (empty($user_popup))
		$user_popup = '
		<div class="row topick_text_popover" style="margin:0 0 10px 0px;">
			<div class="col-md-1 visible_on_big_screen" style="">
				<div style="position:relative; width:100%; height:30px;">
					<div style="position:absolute; width:100%; height:0;">
						<img class="user_image_on_share" src="{$photo}" title="{$firstname} ({$positiontitle})" style="width:50px; height:50px; margin:0; position:relative; right:0; bottom:10px;">
					</div>
				</div>
			</div>
			<div class="col-md-3 col-md-push-8"></div>
			<div class="col-md-8 col-md-pull-3" style="padding-right:60px;">
				<div class="popover right" style="background-color:#'.COLOR2LIGHT.';">
					<div class="arrow"></div>
					<div class="popover-content">
						<div style="position:relative; width:100%; height:0; text-align:right;">
							<div style="position:absolute; width:0; height:0; top:-11px; right:-13px;">
								<button id="topic_delete_{$topicid}" type="button" class="close" aria-label="Delete Topic" onClick="return delete_topic(\'{$topicid}\');" data-toggle="tooltip" data-placement="top" title="Delete Topic"><span class="glyphicon glyphicon-remove-circle" aria-hidden="true" style="font-size:16px; {$show_delete_button}"></span></button>
							</div>
						</div>
						<div class="visible_on_big_screen" style="position:relative; width:100%; height:6px;">
							<div style="position:absolute; width:100%; height:0; top:-15px;">
								<span class="description" style="padding:0;">
									<span class="local_topic_time" style="color:#'.COLOR1BASE.';" unix_time="{$created_since_unix}">
										{$created_time}
										{$created_date}
									</span>
								</span>
							</div>
						</div>
						<span class="visible_on_big_screen" id="text_{$topicid}">{$text}</span>
						<div class="row invisible_on_big_screen">
							<div class="col-sm-3" style="padding:0px 0px 0px 10px; ">
								<div style="position:relative; width:100%; height:30px;">
									<div style="position:absolute; width:100%; height:0;">
										<img class="user_image_on_share" src="{$photo}" title="{$firstname} ({$positiontitle})" style="width:50px; height:50px; margin:0; position:relative; right:0; bottom:20px;"><br>
									</div>
								</div>
								<span class="description" style="padding:0;">
									<span class="local_topic_time" style="color:#'.COLOR1BASE.';" unix_time="{$created_since_unix}">
										{$created_time}
										{$created_date}
									</span>
								</span>
							</div>
							<div class="col-sm-9" style="vertical-align:top;" id="text_{$topicid}">
								{$text}
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>';
	if (empty($interlocutor_popup))
		$interlocutor_popup = '
		<div class="row topick_text_popover" style="margin:0 0 20px 0px;">
			<div class="col-md-3"></div>
			<div class="col-md-8" style="padding-left:60px;">
				<div class="popover left" style="background-color:#'.COLOR1LIGHT.';">
					<div class="arrow" style=""></div>
					<div class="popover-content">
						<div style="position:relative; width:100%; height:0; text-align:right;">
							<div style="position:absolute; width:0; height:0; top:-11px; right:-13px;">
								<button id="topic_delete_{$topicid}" type="button" class="close" aria-label="Delete Topic" onClick="return delete_topic(\'{$topicid}\');" data-toggle="tooltip" data-placement="top" title="Delete Topic"><span class="glyphicon glyphicon-remove-circle" aria-hidden="true" style="font-size:16px; {$show_delete_button}"></span></button>
							</div>
						</div>
						<div class="visible_on_big_screen" style="position:relative; width:100%; height:6px;">
							<div style="position:absolute; width:100%; height:0; top:-15px;">
								<span class="description" style="padding:0;">
									<span class="local_topic_time" style="color:#'.COLOR1BASE.';" unix_time="{$created_since_unix}">
										{$created_time}
										{$created_date}
									</span>
								</span>
							</div>
						</div>
						<span class="visible_on_big_screen" id="text_{$topicid}">{$text}</span>
						<div class="row invisible_on_big_screen">
							<div class="col-sm-3" style="text-align:left; padding:0px 0px 0px 10px;">
								<div style="position:relative; width:100%; height:10px;">
									<div style="position:absolute; width:100%; height:0;">
										<img class="user_image_on_share" src="{$photo}" title="{$firstname} ({$positiontitle})" style="width:30px; height:30px; margin:0; position:relative; right:0; bottom:16px;"><br>
									</div>
								</div>
								<span class="description" style="padding:0;">
									<span class="local_topic_time" style="color:#'.COLOR1BASE.';" unix_time="{$created_since_unix}">
										{$created_time}
										{$created_date}
									</span>
								</span>
							</div>
							<div class="col-sm-9" style="vertical-align:top;" id="text_{$topicid}">
								{$text}
							</div>
						</div>
					</div>
				</div>
			</div>
			<div class="col-md-1 visible_on_big_screen" style="padding:0;">
				<div style="position:relative; width:100%; height:30px;">
					<div style="position:absolute; width:100%; height:0;">
						<img class="user_image_on_share" src="{$photo}" title="{$firstname} ({$positiontitle})" style="width:50px; height:50px; margin:0; position:relative; left:0; bottom:10px;">
					</div>
				</div>
			</div>
		</div>';
	$conversation_body = '';
	
	//$topic_list = get_api_value('get_topics_list'.$api_request_suffix, '', '&interlocutorid='.$interlocutor_userid.'&last_object=&sort_by=1&projectid='.$projectid.'&limit='.$show_first_messages.'&private_chat='.$private_chat );
	//$conversation_body = generate_conversation_body_from_array($topic_list['topic_list'], $interlocutor_userid, $interlocutor_popup, $user_popup);
	$conversation_offest = $topic_list['offest'];
	
	$res = 
	'
	<style type="text/css">
	.popover.left>.arrow:after {border-left-color:#'.COLOR1LIGHT.';}
	.popover.right>.arrow:after {border-right-color:#'.COLOR2LIGHT.';}
	</style>
	<div class="row" style="padding:0 15px 10px 0;">
		'.($show_interlocutors?
			'<div class="col-sm-3 topick_text_popover">
				<div id="interlocutor_info"></div>
				<div style="height:100px; padding:5px 20px 0 20px; overflow:auto;" id="'.$messenger_id.'contacts_list"></div>
			</div>'
			:''
		).'
		<div class="col-sm-'.($show_interlocutors?'9':'12').'" style="'.($auto_height?'':'height:300px; overflow:auto;').' padding:0 20px 0 20px;" id="'.$messenger_id.'">
			<button class="btn btn-link btn-sm" id="conversation_btn_more_topics" style="display:block; margin:2px auto 10px auto;" onclick="conversation_btn_more_topics_clicked();"><span class="glyphicon glyphicon-repeat" aria-hidden="true" style="margin-right:10px;"></span>Previous Topics...
				<div style="width:0px; height:0px; display:block; margin:0px auto 0px auto; position:relative;">
					<img src="/images/wait64x64.gif" width="20" height="20" border="0" alt="" style="position:absolute; left:-10px; top:-20px; display:none;" id="conversation_btn_more_topics_wait_image">
				</div>
			</button>
			
			<div id="'.$messenger_id.'_topics">
				'.$conversation_body.'
			</div>
		</div>
	</div>
	'.(empty($cannot_post_message)?'
		<div class="row">
			<div class="col-md-12">
				<div class="input-group input-group-sm" style="width:100%">
					<textarea wrap="soft" style="height:80px;" class="form-control" rows="2" placeholder="'.$placeholder.'" id="topic_text"></textarea>
					<span class="input-group-btn" style="vertical-align:top;">
						<button class="btn btn-info btn-sm" id="post_btn" onclick="post_message();" style="min-width:60px;"><span id="post_btn_text"><span class="glyphicon glyphicon-send" aria-hidden="true"></span><span class="visible_on_big_screen">&nbsp;&nbsp;&nbsp;&nbsp;Post&nbsp;&nbsp;&nbsp;&nbsp;</span></span><img src="/images/sand_glass.gif" width="20" height="20" border="0" id="post_btn_wait" style="display:none;"></button>
					</span>
				</div>
			</div>
		</div>'
		:'<div class="alert alert-warning">'.$cannot_post_message.'</div>'
	).'
	<script language="JavaScript">
	var check_user_online_timer = 0;
	var first_object = 0;
	var last_object = 0;
	var host_user = '.(isset($user_account) && !empty($user_account->userid)?$user_account->userid:0).';
	var interlocutor_user = '.($interlocutor_userid).';
	var interlocutor_popup = "'.bin2hex($interlocutor_popup).'";
	var user_popup = "'.bin2hex($user_popup).'";
	var conversation_offest = '.(empty($conversation_offest)?0:$conversation_offest).';
	var show_first_messages = '.(empty($show_first_messages)?0:$show_first_messages).';
	var conversation_object_hidden = false;
	
	'.(isset($user_account) && $user_account->is_loggedin()?'
		function post_message()
		{
			var s = $("#topic_text").val();
			if ( s.length == 0 ) {
				show_message_box_box("Error", "Please enter your your text", 2);
				return false;
			}
			$("#post_btn_text").hide();
			$("#post_btn_wait").show();
			$("#post_btn").prop("disabled", true);
			
			s = s.replace(/\n/g, "[#br#]");
			topic_text = string_to_hex32(s);
			try {
				$.ajax({
					method: "POST",
					url: "/api/post_topic'.$api_request_suffix.'",
					data: { userid: "'.$user_account->userid.'", token: "'.$user_account->psw_hash.'", interlocutorid: interlocutor_user, text:topic_text, projectid:"'.$projectid.'" }
				})
				.done(function( ajax__result ) {
					try
					{
						get_conversation();
						$("#topic_text").val("");

						var arr_ajax__result = JSON.parse(ajax__result);
						if ( !arr_ajax__result["success"] )
							show_message_box_box("Error", arr_ajax__result["message"], 2);
						
						$("#post_btn_text").show();
						$("#post_btn_wait").hide();
						$("#post_btn").prop("disabled", false);
					}
					catch(error){'.(!empty($_COOKIE['debug'])?'write_console_log(ajax__result + " --- post_message --- " + error);':'').'}
				});
			}
			catch(error){'.(!empty($_COOKIE['debug'])?'write_console_log("post_message: " + error);':'').'}
		}
		function check_user_online()
		{
			if (interlocutor_user < 1)
				return false;
			'.(!empty($_COOKIE['debug'])?'write_console_log("check_user_online " + interlocutor_user);':'').'
			try {
				$.ajax({
					method: "POST",
					url: "/api/is_user_online'.$api_request_suffix.'/" + interlocutor_user,
					data: { userid: "'.$user_account->userid.'", token: "'.$user_account->psw_hash.'" }
				})
				.done(function( ajax__result ) {
					try
					{
						var arr_ajax__result = JSON.parse(ajax__result);
						if ( arr_ajax__result["success"] ) {
							if ( arr_ajax__result["values"]["is_user_online"] )
								$("#user_online").html("<span style=\'color:#008800;\'><span class=\'glyphicon glyphicon-ok\' aria-hidden=\'true\'></span> online</span>");
							else
								$("#user_online").html("<span style=\'color:#c01103;\'><span class=\'glyphicon glyphicon-ban-circle\' aria-hidden=\'true\'></span> offline</span>");
						}
						else {
							'.(!empty($_COOKIE['debug'])?'write_console_log(ajax__result + " --- check_user_online --- " + arr_ajax__result["message"]);':'').'
						}
					}
					catch(error){'.(!empty($_COOKIE['debug'])?'write_console_log(ajax__result + " --- check_user_online exception --- " + error);':'').'}
					check_user_online_timer = setTimeout( check_user_online, 50000 );
				});
			}
			catch(error){'.(!empty($_COOKIE['debug'])?'write_console_log("Error: check_user_online: " + error);':'').'}
		}
		
		function delete_topic(topicid)
		{
			if ( !confirm("Do you really want to delete this topic?") )
				return false;
			$("#topic_delete_" + topicid).hide();
			
			try {
				$.ajax({
					method: "POST",
					url: "/api/delete_topic'.$api_request_suffix.'/" + topicid,
					data: { userid: "'.$user_account->userid.'", token: "'.$user_account->psw_hash.'" }
				})
				.done(function( ajax__result ) {
					try
					{
						get_conversation();
						
						var arr_ajax__result = JSON.parse(ajax__result);
						if ( !arr_ajax__result["success"] )
							show_message_box_box("Error", arr_ajax__result["message"], 2);
					}
					catch(error){'.(!empty($_COOKIE['debug'])?'write_console_log(ajax__result + " --- delete_topic --- " + error);':'').'}
				});
			}
			catch(error){'.(!empty($_COOKIE['debug'])?'write_console_log("delete_topic: " + error);':'').'}

			return false;
		}
		'
		:''
	).'
	
	function get_conversation()
	{
	'.(isset($user_account) && $user_account->is_loggedin()?'
		if (conversation_object_hidden) {
			get_conversation_timer = setTimeout( get_conversation, 5000 );
			return false;
		}
		try {
			$.ajax({
				method: "POST",
				url: "/api/get_topics_list'.$api_request_suffix.'",
				data: { 
					'.(isset($user_account) && $user_account->is_loggedin()?
						'userid: "'.$user_account->userid.'", token: "'.$user_account->psw_hash.'",'
						:'token: md5( "'.get_api_token_seed().'" + Math.round((new Date().getTime()) / 60000) ),'
					).'
					interlocutorid: interlocutor_user, last_object:last_object, sort_by: 1, projectid:"'.$projectid.'", limit:show_first_messages, private_chat:'.$private_chat.' }
			})
			.done(function( ajax__result ) {
				try
				{
					var arr_ajax__result = JSON.parse(ajax__result);
					if ( arr_ajax__result["success"] ) {
						var generated_topiks_list = "";
						for (var i = 0; i < arr_ajax__result["values"]["topic_list"].length; i++) {
							var topic = arr_ajax__result["values"]["topic_list"][i];
							if ( topic["userid"] == interlocutor_user )
								var s = hex_to_string(interlocutor_popup);
							else
								var s = hex_to_string(user_popup);
							
							for (var name in topic) {
								s = replaceCustomConstantInText(name, topic[name], s);
							}
							generated_topiks_list = generated_topiks_list + s;
						}
						generated_topiks_list = "<div id=topics_list_inner>" + generated_topiks_list + "</div>";

						$("#'.$messenger_id.'_topics").html(generated_topiks_list);
						$("#'.$messenger_id.'_topics").fadeTo( "fast", 1, function() {});

						first_object = arr_ajax__result["values"]["first_object"];
						if (arr_ajax__result["values"]["last_object"] && arr_ajax__result["values"]["last_object"].length > 0 && arr_ajax__result["values"]["last_object"] !== last_object )	{
							$("#'.$messenger_id.'").animate({
								scrollTop: $("#topics_list_inner").height()
							}, "slow");
							last_object = arr_ajax__result["values"]["last_object"];
						}
						$(".local_topic_time").each(function( index ) {
							var d = new Date();
							d.setTime($(this).attr("unix_time") * 1000);
							$(this).html(d.getHours() + ":" + d.getMinutes() + " " + d.getDate() + " " + monthNames[d.getMonth()] + " " + d.getFullYear());
						});
						conversation_offest = arr_ajax__result["values"]["offest"];
						if ( conversation_offest <= 0 )
							$("#conversation_btn_more_topics").hide();
					}
					else {
						
					}
					$("#conversation_btn_more_topics").prop("disabled", false);
					$("#conversation_btn_more_topics_wait_image").hide();
				}
				catch(error){'.(!empty($_COOKIE['debug'])?'write_console_log(ajax__result + " --- get_conversation --- " + error);':'').'}
				get_conversation_timer = setTimeout( get_conversation, 5000 );
			});
		}
		catch(error){'.(!empty($_COOKIE['debug'])?'write_console_log("Error: get_conversation: " + error);':'').'}'
		:''
	).'
	}
	
	'.($show_interlocutors?'
		function get_contacts_list(contacts_list_id)
		{
			try {
				$.ajax({
					method: "POST",
					url: "/api/get_contacts_list'.$api_request_suffix.'",
					data: { userid: "'.$user_account->userid.'", token: "'.$user_account->psw_hash.'", interlocutorid: interlocutor_user, last_object:last_object }
				})
				.done(function( ajax__result ) {
					try
					{
						var list_has_error = false;
						try
						{
							var arr_ajax__result = JSON.parse(ajax__result);
							list_has_error = typeof arr_ajax__result["success"] != "undefined" && !arr_ajax__result["success"];
						}
						catch(error){}
						if ( !list_has_error )
							$("#" + contacts_list_id).html(ajax__result);
					}
					catch(error){'.(!empty($_COOKIE['debug'])?'write_console_log(ajax__result + " --- get_contacts_list --- " + error);':'').'}
					get_contacts_list_timer = setTimeout( get_contacts_list, 10000 );
				});
			}
			catch(error){'.(!empty($_COOKIE['debug'])?'write_console_log("Error: get_contacts_list: " + error);':'').'}
		}

		function change_interlocutor(new_interlocutor)
		{
			interlocutor_user = new_interlocutor;
			get_interlocutor_info();
			get_contacts_list("'.$messenger_id.'contacts_list");
			check_user_online();
			get_conversation();
		}

		function get_interlocutor_info()
		{
			if (interlocutor_user < 1)
				return false;
			'.(!empty($_COOKIE['debug'])?'write_console_log("get_interlocutor_info " + interlocutor_user);':'').'
			try {
				$.ajax({
					method: "POST",
					url: "/api/get_interlocutor_info'.$api_request_suffix.'/" + interlocutor_user,
					data: { userid: "'.$user_account->userid.'", token: "'.$user_account->psw_hash.'" }
				})
				.done(function( ajax__result ) {
					try
					{
						var arr_ajax__result = JSON.parse(ajax__result);
						if ( arr_ajax__result["success"] ) {
							$("#interlocutor_info").html(arr_ajax__result["values"]["text"]);
						}
						else {
							'.(!empty($_COOKIE['debug'])?'write_console_log(ajax__result + " --- get_interlocutor_info --- " + error);':'').'
						}
					}
					catch(error){'.(!empty($_COOKIE['debug'])?'write_console_log(ajax__result + " --- get_interlocutor_info --- " + error);':'').'}
				});
			}
			catch(error){'.(!empty($_COOKIE['debug'])?'write_console_log("Error: get_interlocutor_info: " + error);':'').'}
		}'
		:''
	).'
	
	function conversation_btn_more_topics_clicked()
	{
		$("#conversation_btn_more_topics").prop("disabled", true);
		$("#conversation_btn_more_topics_wait_image").show();
		show_first_messages = show_first_messages + Math.round('.(empty($show_first_messages)?0:$show_first_messages).' / 2);
		$("#'.$messenger_id.'_topics").fadeTo( "fast", 0.1, function() {});
		get_conversation();
	}

	$(document).ready(function(){
		'.($show_interlocutors?
			'
			if ( $(window).width() > 750 ) {
				var contacts_list_height = Math.round(window.innerHeight / 4);
				if (contacts_list_height < 50)
					contacts_list_height = 50;
				$("#'.$messenger_id.'contacts_list").css("height", contacts_list_height);
			}
			get_contacts_list("'.$messenger_id.'contacts_list");
			get_interlocutor_info();
			check_user_online();
			'
			:''
		).'
		'.(!$auto_height?'
			var messenger_container_height = Math.round(window.innerHeight / 3);
			if (messenger_container_height < 100)
				messenger_container_height = 100;
			$("#'.$messenger_id.'").css("height", messenger_container_height);
			'
			:''
		).'
		'.(isset($user_account) && $user_account->is_loggedin() && empty($cannot_post_message) || defined('DEBUG_MODE')?
			'get_conversation_timer = setTimeout( get_conversation, 5000 );'
			:''
		).'
		if ( conversation_offest <= 0 )
			$("#conversation_btn_more_topics").hide();
	});
	</script>';
	return $res;
	
}
?>
