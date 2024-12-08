<?php
function get_timeline_code($user_account, $projectid = '0', $editable = 1, $visible = 1, $timeline_xml = '', $update_timeline_stockid = '', $funded = 0, $number_of_grid_columns = 8, $show_photo = false)
{
	$can_be_edited = $editable && isset($user_account) && $user_account->is_loggedin();
	$task_max_char = 256;
	$res = '
	<style type="text/css">
	.timeline_job_box{background-color:#FFFFFF; background-image:none; padding:4px 4px 6px 4px; margin:0 0 0px 0; height:auto; border:1px solid #dedede;
		-moz-border-radius:'.BOX_TYPE1_RADIUS.'px; border-radius:'.BOX_TYPE1_RADIUS.'px;
		box-shadow:1px 1px 3px #d8d8d8;
		-moz-box-shadow:1px 1px 3px #d8d8d8;
		-webkit-box-shadow:1px 1px 3px #d8d8d8;}
	.timeline_job_task{background-color:#'.COLOR1LIGHT.'; background-image:none; 
		padding:4px 8px 6px 8px; 
		margin:0 0 2px 0; 
		height:auto; border:1px solid '.adjustBrightness(COLOR1LIGHT, -10).';
		-moz-border-radius:'.(BOX_TYPE1_RADIUS > 0?BOX_TYPE1_RADIUS - 1:0).'px; border-radius:'.(BOX_TYPE1_RADIUS > 0?BOX_TYPE1_RADIUS - 1:0).'px;
		box-shadow:inset 1px 1px 8px '.adjustBrightness(COLOR1LIGHT, -20).';
		-moz-box-shadow:inset 1px 1px 8px '.adjustBrightness(COLOR1LIGHT, -20).';
		-webkit-box-shadow:inset 1px 1px 8px '.adjustBrightness(COLOR1LIGHT, -20).';
		}
	.item_duration{background-color:#C0FFC0; 
		background-image:none; 
		padding:2px 6px 2px 6px;
		margin:0; 
		height:auto; 
		border:1px solid #00FF00;
		-moz-border-radius:4px; border-radius:4px;
		position:relative;
	}
	.arrow{
		color:#'.COLOR1BASE.'; font-size:16px; cursor:pointer;
	}
	.arrow:hover{color:#00FF00; text-shadow:1px 1px 3px #585858;}
	.remove_button{
		color:#B00000; font-size:12px; cursor:pointer;
	}
	.remove_button:hover{color:#FF0000; text-shadow:1px 1px 3px #585858;}
	.total_number{font-size:14px; font-weight:bold; padding-left:10px;}
	.spent_bar{
		width:40px;
		background: #f7ea31; 
		background: -moz-linear-gradient(left, #f7ea31 0%, #f9eb6d 19%, #207cca 19%, #207cca 19%, #fcee28 19%, #d3ab28 75%, #e8c87d 100%);
		background: -webkit-linear-gradient(left, #f7ea31 0%,#f9eb6d 19%,#207cca 19%,#207cca 19%,#fcee28 19%,#d3ab28 75%,#e8c87d 100%);
		background: linear-gradient(to right, #f7ea31 0%,#f9eb6d 19%,#207cca 19%,#207cca 19%,#fcee28 19%,#d3ab28 75%,#e8c87d 100%);
		filter: progid:DXImageTransform.Microsoft.gradient( startColorstr="#f7ea31", endColorstr="#e8c87d",GradientType=1 );
	}
	.spent_bar_text_div{position:absolute; min-width:10px; height:auto; left:20px; top:4px;
		background-color:#FFFF00;
		background-image:none; 
		padding:2px 4px 2px 4px;
		margin:0;
		border:1px solid #C0C000;
		-moz-border-radius:4px; border-radius:4px;
		opacity:0.7;
		box-shadow:4px 4px 8px #bbb;
		z-index:100;
	}
	.spent_bar_text{}
	.current_task{background-color:#'.COLOR2LIGHT.'}
	.active_task_row{background-color:#'.COLOR3LIGHT.'}
	</style>
	<img src="/images/wait64x64.gif" width="12" height="12" border="0" alt="" style="position:absolute; left:0px; top:0px; display:none;" id="wait_image">
	'.($can_be_edited?'
		<table class="table table-striped" style="margin:0;">
		<tr id="timeline_buttons_bar">
			<td class="timeline_column">
				<button class="btn btn-success btn-xs" onclick="show_edit_item_box(this.id, \''.bin2hex('Task:').'\', undefined, \''.bin2hex('Minimum 10 symbols, maximum '.$task_max_char.' symbols. Please use English language only.').'\', \'min_len=3&max_len='.$task_max_char.'&no_chars=\', new_task);"><span class="glyphicon glyphicon-plus-sign" aria-hidden="true"></span>&nbsp;Add Task</button>
			</td>
		</tr>
		</table>'
		:''
	).'
	<canvas class="visible_on_big_screen" id="canvas" style="width:1px; height:1px; position:absolute; left:0px; top:0px; display:none;"></canvas>
	<table class="table table-borderless" style="margin:0; display:none;" id="tasks_table">
	';
	$task_row = '
		<tr style="border-bottom:1px solid #'.COLOR1LIGHT.';" id="task_row{$taskid}" class="{$selected_class}">
			<td style="padding:10px 0 0 2px; vertical-align:top;">
				'.($can_be_edited?'
					<span class="glyphicon glyphicon-arrow-up arrow" aria-hidden="true" style="margin-bottom:20px;" title="move up" onclick="move_timeline_task(\'up\', {$taskid}, this.id);" id="up{$taskid}"></span><br>
					<span class="glyphicon glyphicon-arrow-down arrow" aria-hidden="true" style="" title="move down" onclick="move_timeline_task(\'down\', {$taskid}, this.id);" id="down{$taskid}"></span>'
					:''
				).'
			</td>
			<td class="timeline_column" style="padding:8px; vertical-align:top;" id="task_column">
				<div class="timeline_job_box {$selected_class}" style="" id="task_col{$taskid}">
					<div class="timeline_job_task 
						'.($can_be_edited?'
							editable_item" onclick="show_edit_item_box(this.id, \''.bin2hex('Task:').'\', undefined, \''.bin2hex('Minimum 10 symbols, maximum '.$task_max_char.' symbols. Please use English language only.').'\', \'min_len=3&max_len='.$task_max_char.'&no_chars=\', edit_task);"'
							:'"'
						).'
						id="task{$taskid}" taskid="{$taskid}" value_name="task">{$task}
					</div>
					<table class="table table-borderless" style="margin:0; background-color:transparent;">
						<tr>
							<td>
								'.($show_photo?
									'
									<img src="{$photo}" class="user_image_on_share '.($can_be_edited?'
										editable_item" onclick="show_edit_item_box(this.id, \''.bin2hex('User who is on charge for completing this task:').'\', string_to_hex(\'{$userid}\'), \''.bin2hex('').'\', \'\', edit_task, \'\', \'number\', \''.bin2hex('<span class="glyphicon glyphicon-user" aria-hidden="true"></span>').'\', null, 0);" taskid="{$taskid}" value_name="userid" id="userid{$taskid}" '
										:'"'
									).' style="width:30px; height:30px; margin:0; {$hide_photo}" title="{$firstname}">'
									:''
								).'
							</td>
							<td style="width:100%; padding-bottom:0;">
								Starts: <b '.($can_be_edited?'class="editable_item" onclick="show_edit_item_box(this.id, \''.bin2hex('Start Date:').'\', undefined, \''.bin2hex('').'\', \'\', edit_task, \'\', \'date\');" id="starts{$taskid}" taskid="{$taskid}" value_name="starts"':'').'>{$starts}</b><br>
								Duration: <b '.($can_be_edited?'class="editable_item" onclick="show_edit_item_box(this.id, \''.bin2hex('Task Duration:').'\', undefined, \''.bin2hex('').'\', \'\', edit_task, \'\', \'number\', \'\', \'\', \'\', \''.bin2hex('days').'\');" id="duration{$taskid}" taskid="{$taskid}" value_name="duration"':'').'>{$duration}</b> days (ends {$task_ends})<br>
								Cost: '.DOLLAR_SIGN.'<b '.($can_be_edited?'class="editable_item" onclick="show_edit_item_box(this.id, \''.bin2hex('Cost a day:').'\', undefined, \''.bin2hex('').'\', \'\', edit_task, \'\', \'number\', \''.bin2hex(DOLLAR_SIGN).'\', \'\', \'\', \''.bin2hex('per day').'\');" id="cost{$taskid}" taskid="{$taskid}" value_name="cost"':'').' >{$cost}</b>/day<br>
								Total: <b>{$total}</b><br>
							</td>
						</tr>
					</table>
					<div class="description" style="text-align:right;">Funded: {$funded_percent}%</div>
					<div class="progress" style="margin:0; height:4px;">
						<div class="progress-bar progress-bar-success" role="progressbar" aria-valuenow="{$funded_percent}" aria-valuemin="0" aria-valuemax="100" style="width:{$funded_percent}%"></div>
					</div>
				</div>
			</td>
			<td style="padding:10px 0 0 0px; vertical-align:top;">
				'.($can_be_edited?'
					<span class="glyphicon glyphicon-remove remove_button" aria-hidden="true" style="margin-bottom:20px;" title="delete task" onclick="delete_timeline_task({$taskid}, this.id);" id="delete{$taskid}"></span>'
					:''
				).'
			</td>
			<td class="visible_on_big_screen" style="width:100%; padding:0; vertical-align: middle;" id="timeline_cell">
				<div class="item_duration" style="width:{$item_duration_width}; left:{$item_left};">
					<table style="border:none;">
					<tr>
						<td style="width:100%;">
							<div style="width:100%; max-height:20px; overflow:hidden;" title="{$duration_string}, ends on {$task_ends}">{$duration_string}, ends on {$task_ends}</div>
						</td>
						<td style="text-align:right;">
							<span class="glyphicon glyphicon-arrow-right" aria-hidden="true" style="color:#00FF00; font-size:16px;" title="{$duration_string}, ends on {$task_ends}"></span>
						</td>
					</tr>
					</table>
				</div>
			</td>
		</tr>
	';

	$spent_bar = '
		<div class="spent_bar" style="height:0px; position:absolute; left:{$spent_bar_x}; bottom:0; display:none;" value="{$spent_bar_val}" id="spent_bar_{$spent_bar_number}">
			<div style="position:relative; width:0; height:0;">
				<div class="spent_bar_text_div">
					<span class="spent_bar_text">{$spent_bar_amount}</span>
				</div>
			</div>
		</div>
	';
	if (empty($timeline_xml)) {
		//$tasks = get_api_value('timeline_tasks', '', '&projectid='.$projectid);
	}
	else
		$tasks = json_decode($timeline_xml, true);
	
	$funds_left = $funded;
	$i = 0;
	if (isset($tasks['tasks'])) {
		for ($j = 0; $j < count($tasks['tasks']); $j++) {
			$row = $tasks['tasks'][$j];
			$s = $task_row;
			if ($tasks['tasks'][$j]['no_photo'] && !$can_be_edited)
				$s = replaceCustomConstantInText('hide_photo', 'display:none;', $s);
			else
				$s = replaceCustomConstantInText('hide_photo', '', $s);
			
			if ( $i == 0 ) {
				if ($tasks['tasks'][$j]['starts'] == '' || $tasks['tasks'][$j]['starts'] == NULL) {
					$tasks['unix_timeline_starts'] = time();
					$tasks['timeline_starts'] = date("j M. Y", $tasks['unix_timeline_starts']);
					$last_unix_task_ends = $tasks['unix_timeline_starts'] + $tasks['tasks'][$j]['duration'] * 60 * 60 * 24;
					$tasks['tasks'][$j]['task_starts'] = date("Y-m-d", $tasks['unix_timeline_starts']);
					$tasks['tasks'][$j]['unix_task_starts'] = $tasks['unix_timeline_starts'];
				}
				else {
					$last_unix_task_ends = $tasks['tasks'][$j]['unix_task_starts'] + $tasks['tasks'][$j]['duration'] * 60 * 60 * 24;
				}
			}
			else {
				if ($tasks['tasks'][$j]['starts'] == '' || $tasks['tasks'][$j]['starts'] == NULL)
					$tasks['tasks'][$j]['unix_task_starts'] = $last_unix_task_ends;
				$tasks['tasks'][$j]['task_starts'] = date("Y-m-d", $tasks['tasks'][$j]['unix_task_starts']);
				$last_unix_task_ends = $tasks['tasks'][$j]['unix_task_starts'] + $tasks['tasks'][$j]['duration'] * 60 * 60 * 24;
			}
			if ($tasks['tasks'][$j]['starts'] == '' || $tasks['tasks'][$j]['starts'] == NULL)
				$end = $last_unix_task_ends;
			else
				$end = $tasks['tasks'][$j]['unix_task_starts'] + $tasks['tasks'][$j]['duration'] * 60 * 60 * 24;
			
			if ( $end > $tasks['unix_timeline_ends'] )
				$tasks['unix_timeline_ends'] = $end;

			if (time() >= $tasks['tasks'][$j]['unix_task_starts'] && time() <= $last_unix_task_ends)
				$tasks['tasks'][$j]['current_task'] = 1;
			else
				$tasks['tasks'][$j]['current_task'] = 0;
			$tasks['tasks'][$j]['task_ends'] = date("j M. Y", $last_unix_task_ends);

			$s = replaceCustomConstantInText('starts', $tasks['tasks'][$j]['task_starts'], $s);
			$s = replaceCustomConstantInText('total', currency_format($tasks['tasks'][$j]['task_cost']), $s);
			$s = replaceCustomConstantInText('item_duration_width', round($tasks['tasks'][$j]['duration'] / $tasks['total_duration'] * 100).'%', $s);
			$s = replaceCustomConstantInText('item_left', round(($tasks['tasks'][$j]['unix_task_starts'] - $tasks['unix_timeline_starts']) / 60 / 60 / 24 / $tasks['total_duration'] * 100).'%', $s);

			if ( $funds_left >= $tasks['tasks'][$j]['task_cost'] )
				$funded_percent = 100;
			else
			if ( $funds_left < 0 )
				$funded_percent = 0;
			else
				$funded_percent = round($funds_left / $tasks['tasks'][$j]['task_cost'] * 100);
			$s = replaceCustomConstantInText('funded_percent', $funded_percent, $s);
			
			$funds_left = $funds_left - $tasks['tasks'][$j]['task_cost'];

			foreach ($tasks['tasks'][$j] as $key => $value)
				$s = replaceCustomConstantInText($key, $value, $s);

			if ( (int)$tasks['tasks'][$j]['current_task'] )
				$selected_class = 'current_task';
			else
				$selected_class = '';
			$s = replaceCustomConstantInText('selected_class', $selected_class, $s);
			$res = $res.$s;
			$i++;
		}
	}
	$tasks['total_duration'] = round(($tasks['unix_timeline_ends'] - $tasks['unix_timeline_starts']) / 60 /60 / 24);
	$tasks['timeline_ends'] = date("j M. Y", $tasks['unix_timeline_ends']);
	$tasks['total_duration_string'] = get_interval($tasks['total_duration'] * 60 * 60 * 24);

	$res = $res.
	'
	</table>
	<table class="table table-striped" style="margin:2px 0 0 0; display:none;" id="totals_table">
	<tr style="background-color:#'.COLOR3LIGHT.';" >
		<td></td>
		<td class="timeline_column" style="padding:8px;" id="total_td">
			<table id="total_table" style="width:100%;">
				<tr><td>Total Duration:</td><td><span class="total_number" id="total_duration">'.$tasks['total_duration_string'].'</span></td></tr>
				<tr><td>Total Cost:</td><td><span class="total_number" id="total_cost">'.currency_format($tasks['total_cost']).'</span></td></tr>
				<tr><td>Starts:</td><td><span class="total_number" id="timeline_starts">'.$tasks['timeline_starts'].'</span></td></tr>
				<tr><td>Ends:</td><td><span class="total_number" id="timeline_ends">'.$tasks['timeline_ends'].'</span></td></tr>
			</table>
		</td>
		<td></td>
		<td class="visible_on_big_screen" style="width:100%; padding:0 0 1px 0; vertical-align:bottom;">
			<div style="width:100%; height:100px; position:relative; bottom:0;" id="spent_bars">
		';
	$i = 0;
	if ( isset($tasks['tasks']) && count($tasks['tasks']) > $number_of_grid_columns ) {
		$timeline_step = round($tasks['total_duration'] * 24 * 60 * 60 / $number_of_grid_columns);
		$spent_bars = array();
		foreach($tasks['tasks'] as $row) {
			for ($i = 0; $i < $number_of_grid_columns; $i++) {
				if ( $row['unix_task_starts'] >= $tasks['unix_timeline_starts'] + $i * $timeline_step && $row['unix_task_starts'] <= $tasks['unix_timeline_starts'] + ($i + 1) * $timeline_step ) {
					if ( $row['task_cost'] > 0 ) {
						$spent_bars[$i]['task_cost'] = $spent_bars[$i]['task_cost'] + $row['task_cost'];
						if ( $tasks['spend_max_value'] < $spent_bars[$i]['task_cost'] )
							$tasks['spend_max_value'] = $spent_bars[$i]['task_cost'];
						if ( empty($spent_bars[$i]['unix_task_starts']) )
							$spent_bars[$i]['unix_task_starts'] = $row['unix_task_starts'];
					}
					break;
				}
			}
		}
		$tasks['tasks'] = $spent_bars;
	}
	$i = 0;
	if (isset($tasks['tasks'])) {
		foreach($tasks['tasks'] as $row) {
			if ( $row['task_cost'] > 0 ) {
				$s = $spent_bar;
				$s = replaceCustomConstantInText('spent_bar_number', $i, $s);
				$s = replaceCustomConstantInText('spent_bar_amount', currency_format($row['task_cost']), $s);
				$s = replaceCustomConstantInText('spent_bar_val', $row['task_cost'], $s);
				$s = replaceCustomConstantInText('spent_bar_x', round(($row['unix_task_starts'] - $tasks['unix_timeline_starts']) / 60 / 60 / 24 / $tasks['total_duration'] * 100).'%', $s);
				$res = $res.$s;
				$i++;
			}
		}
	}
	$res = $res.'	</div>	
		</td>
	</tr>
	<tr>
		<td colspan="4"></td>
	</tr>
	</table>
	'.($can_be_edited?'
		<table class="table table-striped" style="margin:0; display:none;" id="bottom_add_tasks_button">
		<tr>
			<td class="timeline_column">
				<button class="btn btn-success btn-xs" onclick="show_edit_item_box(this.id, \''.bin2hex('Task:').'\', undefined, \''.bin2hex('Minimum 10 symbols, maximum '.$task_max_char.' symbols. Please use English language only.').'\', \'min_len=3&max_len='.$task_max_char.'&no_chars=\', new_task);"><span class="glyphicon glyphicon-plus-sign" aria-hidden="true"></span>&nbsp;Add Task</button>
			</td>
		</tr>
		</table>'
		:''
	).'
	';
	$res = $res.'
	<script language="JavaScript">
	var spend_max_value = '.($tasks['spend_max_value']?$tasks['spend_max_value']:0).';
	var task_row = "'.bin2hex($task_row).'";
	var spent_bar = "'.bin2hex($spent_bar).'";
	var start_unix_time = '.($tasks['unix_timeline_starts']?$tasks['unix_timeline_starts']:time()).';
	var end_unix_time = '.($tasks['unix_timeline_ends']?$tasks['unix_timeline_ends']:time()).';
	var active_taskid = "";
	var active_itemid = "";
	var number_of_grid_columns = '.$number_of_grid_columns.';
	var draw_grid_timer = 0;
	var funded = '.($funded?$funded:0).';
	var last_task_userid = '.($user_account->userid?$user_account->userid:0).';

	function draw_grid()
	{
		draw_grid_timer = 0;
		var position = $("#timeline_cell").position();
		if (!position)
			return false;
		var canvas_width = $("#timeline_cell").width();
		var canvas_height = $("#tasks_table").height() + $("#totals_table").height() + 4;
		$("#canvas").attr("width", canvas_width + "px");
		$("#canvas").attr("height", canvas_height + "px");
		$("#canvas").css({ position: "absolute", top: position.top, left: position.left, width: canvas_width, height:canvas_height });
		var d = new Date();
		var canvas = document.getElementById("canvas");
		
		if (canvas.getContext) {
			var ctx = canvas.getContext("2d");
			ctx.lineWidth = 0.5;
			ctx.setLineDash([5, 5]);
			ctx.strokeStyle = "#'.COLOR1BASE.'";
			var i = 1;
			for (var x = Math.round(canvas_width / number_of_grid_columns); x < canvas_width; x = x + Math.round(canvas_width / number_of_grid_columns) + 1 ) {
				ctx.moveTo(x, 12);
				ctx.lineTo(x, canvas_height - 10);
				ctx.font = "9px arial";
				d.setTime((start_unix_time + (end_unix_time - start_unix_time) / number_of_grid_columns * i ) * 1000);
				var str = d.getDate() + " " + monthNames[d.getMonth()] + " " + d.getFullYear();
				ctx.fillText(str, x - 30, 10);
				ctx.fillText(str, x - 30, canvas_height - 4);
				if (i == 1){
					d.setTime( start_unix_time * 1000);
					ctx.fillText(d.getDate() + " " + monthNames[d.getMonth()] + " " + d.getFullYear(), 0, 10);
					ctx.fillText(d.getDate() + " " + monthNames[d.getMonth()] + " " + d.getFullYear(), 0, canvas_height - 4);
				}
				i++;
			}
			
			d.setTime(end_unix_time * 1000);
			var end_date_str = d.getDate() + " " + monthNames[d.getMonth()] + " " + d.getFullYear();
			ctx.fillText(end_date_str, canvas_width - ctx.measureText(end_date_str).width, 10);
			ctx.fillText(end_date_str, canvas_width - ctx.measureText(end_date_str).width, canvas_height - 4);
			
			ctx.stroke();
			
			var now_unix_time = Math.round(Date.now() / 1000);
			if (now_unix_time > start_unix_time && now_unix_time < end_unix_time ) {
				x = Math.round( canvas_width * ((now_unix_time - start_unix_time) / ( end_unix_time - start_unix_time )) );
				ctx.beginPath();
				ctx.lineWidth = 0.5;
				ctx.setLineDash([0, 0]);
				ctx.strokeStyle = "#'.COLOR3BASE.'";
				ctx.moveTo(x, 12);
				ctx.lineTo(x, canvas_height - 10);
				ctx.closePath();
			}
			ctx.stroke();
		}
	}

	function animate_spends()
	{
		if ($("#spent_bars").inView() && $("#'.$parent_container.'").parent().css("display") != "none" ) {
			$( ".spent_bar" ).animate(
				{
					progress: 100
				}, 
				{
					step: function( now, fx ) {
						var max_val = $("#" + fx.elem.id).attr("value") / spend_max_value * 98;
						$("#" + fx.elem.id).height( Math.round(max_val * now / 100) );
					}
				}
			);
		}
		else {
			animate_spends_timer = setTimeout( animate_spends, 500 );
		}
	}
	
	function replaceCustomConstantInText(code, value, text)
	{
		var find = "{$" + code + "}";
		while ( text.indexOf(find) >= 0 )
			text = text.replace(find, value);
		return text;
	}
	
	'.($can_be_edited?'
		function new_task(task_text)
		{
			try {
				$.ajax({
					method: "POST",
					url: "/api/add_timeline_task",
					data: { 
						userid: "'.$user_account->userid.'", token: "'.$user_account->psw_hash.'",
						projectid: "'.$projectid.'", 
						task: task_text, task_userid: last_task_userid
					}
				})
				.done(function( ajax__result ) {
					try
					{
						var arr_ajax__result = JSON.parse(ajax__result);
						if ( arr_ajax__result["success"] ) {
							active_taskid = arr_ajax__result["values"]["new_taskid"];
						}
						get_timeline();
					}
					catch(error){'.(!empty($_COOKIE['debug'])?'write_console_log(ajax__result + " --- new_task --- " + error);':'').'}
				});
			}
			catch(error){'.(!empty($_COOKIE['debug'])?'write_console_log("Error: new_task: " + error);':'').'}
		}

		function edit_task(text, item_id)
		{
			if ( $("#" + item_id).attr("value_name") == "userid" )
				last_task_userid = text;

			active_taskid = $("#" + item_id).attr("taskid");
			try {
				$.ajax({
					method: "POST",
					url: "/api/change_timeline_task",
					data: { 
						userid: "'.$user_account->userid.'", token: "'.$user_account->psw_hash.'",
						taskid: active_taskid, 
						value_name: $("#" + item_id).attr("value_name"), 
						value: text
					}
				})
				.done(function( ajax__result ) {
					try
					{
						get_timeline();
					}
					catch(error){'.(!empty($_COOKIE['debug'])?'write_console_log(ajax__result + " --- edit_task --- " + error);':'').'}
				});
			}
			catch(error){'.(!empty($_COOKIE['debug'])?'write_console_log("Error: edit_task: " + error);':'').'}
		}

		function get_timeline()
		{
			try {
				$.ajax({
					method: "POST",
					url: "/api/timeline_tasks",
					data: { 
						userid: "'.$user_account->userid.'", token: "'.$user_account->psw_hash.'",
						projectid: "'.$projectid.'"
					}
				})
				.done(function( ajax__result ) {
					try
					{
						var arr_ajax__result = JSON.parse(ajax__result);
						if ( arr_ajax__result["success"] ) {
							'.(!empty($update_timeline_stockid)?
								'
								try{
									$.ajax({
										method: "POST",
										url: "/api/update_share_timeline_xml",
										data: { 
											userid: "'.$user_account->userid.'", token: "'.$user_account->psw_hash.'",
											stockid: "'.$update_timeline_stockid.'", projectid: "'.$projectid.'"
										}
									})
									.done(function( res ) {
										//write_console_log(res);
									});
								}
								catch(error){}'
								:''
							).'
							var tasks = "";
							var spent_bars = "";
							var i = 0;
							var timeline_step = Math.round(arr_ajax__result["values"]["total_duration"] * 24 * 60 * 60 / number_of_grid_columns);
							var spent_bars_arr = [];
							var funds_left = funded;
							if (arr_ajax__result["values"]["tasks"].length > 0) {
								$("#canvas").show();
								$("#tasks_table").show();
								$(".spent_bar").show();
								$("#totals_table").show();
								$("#bottom_add_tasks_button").show();
							}
							arr_ajax__result["values"]["tasks"].forEach(function(row) {
								var s = hex_to_string(task_row);
								s = replaceCustomConstantInText("starts", row["task_starts"], s);
								s = replaceCustomConstantInText("total", currency_format(row["task_cost"], "'.DOLLAR_SIGN.'"), s);
								s = replaceCustomConstantInText("item_duration_width", Math.round(row["duration"] / arr_ajax__result["values"]["total_duration"] * 100) + "%", s);
								s = replaceCustomConstantInText("item_left", Math.round((row["unix_task_starts"] - arr_ajax__result["values"]["unix_timeline_starts"]) / 60 / 60 / 24 / arr_ajax__result["values"]["total_duration"] * 100) + "%", s);
								
								if ( funds_left >= row["task_cost"] )
									funded_percent = 100;
								else
								if ( funds_left < 0 )
									funded_percent = 0;
								else
									funded_percent = Math.round(funds_left / row["task_cost"] * 100);
								s = replaceCustomConstantInText("funded_percent", funded_percent, s);

								for (var name in row)
									s = replaceCustomConstantInText(name, row[name], s);
								
								if (Number(row["current_task"]))
									var selected_class = "current_task";
								else
									var selected_class = "";
								s = replaceCustomConstantInText("selected_class", selected_class, s);
								tasks = tasks + s;
							});

							if ( arr_ajax__result["values"]["tasks"].length > number_of_grid_columns ) {
								for (var i = 0; i < number_of_grid_columns; i++) {
									spent_bars_arr.splice(i, 0, []);
									spent_bars_arr[i]["task_cost"] = 0;
									spent_bars_arr[i]["unix_task_starts"] = 0;
								}
								
								arr_ajax__result["values"]["tasks"].forEach(function(row) {
									for (i = 0; i < number_of_grid_columns; i++) {
										if ( (Number(row["unix_task_starts"]) >= Number(arr_ajax__result["values"]["unix_timeline_starts"]) + i * timeline_step) && (Number(row["unix_task_starts"]) <= Number(arr_ajax__result["values"]["unix_timeline_starts"]) + (i + 1) * timeline_step) ) {
											if ( row["task_cost"] > 0 ) {
												spent_bars_arr[i]["task_cost"] = Number(spent_bars_arr[i]["task_cost"]) + Number(row["task_cost"]);
												if ( arr_ajax__result["values"]["spend_max_value"] < spent_bars_arr[i]["task_cost"] )
													arr_ajax__result["values"]["spend_max_value"] = spent_bars_arr[i]["task_cost"];

												if ( spent_bars_arr[i]["unix_task_starts"] == 0 )
													spent_bars_arr[i]["unix_task_starts"] = Number(row["unix_task_starts"]);
											}
											break;
										}
									}
								});
								arr_ajax__result["values"]["tasks"] = spent_bars_arr;
							}

							i = 0;
							arr_ajax__result["values"]["tasks"].forEach(function(row) {
								if ( row["task_cost"] > 0 ) {
									s = hex_to_string(spent_bar);
									s = replaceCustomConstantInText("spent_bar_number", i, s);
									s = replaceCustomConstantInText("spent_bar_amount", currency_format(row["task_cost"], "'.DOLLAR_SIGN.'"), s);
									s = replaceCustomConstantInText("spent_bar_val", row["task_cost"], s);
									s = replaceCustomConstantInText("spent_bar_x", Math.round((row["unix_task_starts"] - arr_ajax__result["values"]["unix_timeline_starts"]) / 60 / 60 / 24 / arr_ajax__result["values"]["total_duration"] * 100) + "%", s);
									spent_bars = spent_bars + s;
								}
								i++;
							});
							$("#tasks_table").html(tasks);
							$("#spent_bars").html(spent_bars);

							spend_max_value = Number(arr_ajax__result["values"]["spend_max_value"]);
							start_unix_time = Number(arr_ajax__result["values"]["unix_timeline_starts"]);
							end_unix_time = Number(arr_ajax__result["values"]["unix_timeline_ends"]);

							$("#total_duration").html(arr_ajax__result["values"]["total_duration_string"]);
							$("#total_cost").html(currency_format(arr_ajax__result["values"]["total_cost"], "'.DOLLAR_SIGN.'"));
							$("#timeline_starts").html(arr_ajax__result["values"]["timeline_starts"]);
							$("#timeline_ends").html(arr_ajax__result["values"]["timeline_ends"]);
							if ( $(window).width() > 974 ) {
								draw_grid();
								animate_spends();
							}
							make_changes_after_list_received();
							$(window).scrollTop( $("#task_row" + active_taskid).position().top - Math.round($(window).height() / 2) );
						}
						else {
							
						}
					}
					catch(error){'.(!empty($_COOKIE['debug'])?'write_console_log(ajax__result + " --- get_timeline --- " + error);':'').'}
				});
			}
			catch(error){'.(!empty($_COOKIE['debug'])?'write_console_log("Error: get_timeline: " + error);':'').'}
		}

		function move_timeline_task(direction, taskid, item_id)
		{
			active_taskid = taskid;
			$("#" + item_id).css("opacity", 0);
			var position = $("#" + item_id).position();
			
			$("#wait_image").css({ position: "absolute", top: position.top + "px", left: position.left + "px", width: 12, height:12 });
			$("#wait_image").show();
			
			try {
				$.ajax({
					method: "POST",
					url: "/api/move_timeline_task_" + direction,
					data: { 
						userid: "'.$user_account->userid.'", token: "'.$user_account->psw_hash.'",
						taskid: taskid
					}
				})
				.done(function( ajax__result ) {
					try
					{
						get_timeline();
					}
					catch(error){'.(!empty($_COOKIE['debug'])?'write_console_log(ajax__result + " --- move_timeline_task_up --- " + error);':'').'}
					$("#wait_image").hide();
				});
			}
			catch(error){'.(!empty($_COOKIE['debug'])?'write_console_log("Error: move_timeline_task_up: " + error);':'').'}
		}

		function delete_timeline_task(taskid, item_id)
		{
			active_taskid = taskid;
			active_itemid = item_id;
			show_box_yesno_box("Do you realy want to delete this task?", "delete_timeline_task_confirmed", "", "<span class=\'glyphicon glyphicon-ok\' aria-hidden=\'true\'></span> Yes");
		}

		function delete_timeline_task_confirmed()
		{
			taskid = active_taskid;
			item_id = active_itemid;
			$("#" + item_id).css("opacity", 0);
			var position = $("#" + item_id).position();
			
			$("#wait_image").css({ position: "absolute", top: position.top + "px", left: position.left + "px", width: 12, height:12 });
			$("#wait_image").show();
			
			try {
				$.ajax({
					method: "POST",
					url: "/api/delete_timeline_task",
					data: { 
						userid: "'.$user_account->userid.'", token: "'.$user_account->psw_hash.'",
						taskid: taskid
					}
				})
				.done(function( ajax__result ) {
					try
					{
						get_timeline();
					}
					catch(error){'.(!empty($_COOKIE['debug'])?'write_console_log(ajax__result + " --- move_timeline_task_up --- " + error);':'').'}
					$("#wait_image").hide();
				});
			}
			catch(error){'.(!empty($_COOKIE['debug'])?'write_console_log("Error: move_timeline_task_up: " + error);':'').'}
		}
		'
		:''
	).'
	function make_changes_after_list_received()
	{
		$("#task_row" + active_taskid).addClass("active_task_row");
		$("#total_table").css({ width: $("#task_column").width() });
	}
	
	$.fn.inView = function(){
		if(!this.length) return false;
		var rect = this.get(0).getBoundingClientRect();
		return (
			rect.top >= 0 &&
			rect.left >= 0 &&
			rect.bottom <= (window.innerHeight || document.documentElement.clientHeight) &&
			rect.right <= (window.innerWidth || document.documentElement.clientWidth)
		);
	};

	$( document ).ready(function() 
	{
		'.($visible?'
			if ( $(window).width() > 974 ) {
				draw_grid();
				animate_spends();
			}
			make_changes_after_list_received();
			'
			:''
		).'
	});
	$( document ).scroll(function() {
		if ( $(window).width() > 974 ) {
			if ( !draw_grid_timer )
				draw_grid_timer = setTimeout( draw_grid, 500 );
		}
	});	

	$( window ).resize(function() {
		if ( $(window).width() > 974 ) {
			if ( !draw_grid_timer )
				draw_grid_timer = setTimeout( draw_grid, 500 );
		}
	});

	</script>
	';
	return $res;
}

require_once(DIR_WS_INCLUDES.'box_edit_item.php');
require_once(DIR_WS_INCLUDES.'box_yes_no.php');
?>

