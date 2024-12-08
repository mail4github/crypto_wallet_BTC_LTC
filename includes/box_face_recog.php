<script src="/javascript/face_api/face-api.js"></script>
<script src="/javascript/CameraVideCapture.js"></script>

<style>
#face_scanner_aim{width:300px; height:300px; position:absolute; border:10px solid #ffffff; left:50%; top:50%; opacity:0.1; border-radius:200px; margin:-150px;}
.face_scanner_box_video {width: 100%; height: 100%; min-height:400px; display:block; margin:auto; border-width:20px}
@media(max-width: 600px) {
	.face_scanner_box_video {min-height:300px;}
	#face_scanner_aim{width:200px; height:200px; margin:-100px;}
}
</style>
<div id="hidden_face_scanner_box_video" style="position:absolute; width:160px; height:120px; left:0px; top:0px; z-index:-1; opacity:1;"></div>

<!-- Face Scanner box -->  
<div class="modal fade" id="face_scanner_box" role="dialog" style="display:none;">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header" style="border-bottom:none;">
				<button type="button" class="close" data-dismiss="modal">&times;</button>
				<div id="list_of_cameras_div" style="display:none;"><span class="visible_on_big_screen">Select Camera: </span><select class="form-control" id="list_of_cameras" style="font-size:90%; padding:4px; max-width:200px; height:auto; display:inline-block;" onchange="face_scanner_camera_changed(this)"></select></div>
			</div>
			<div class="modal-body" style="position:relative;">
				<div id="face_scanner_box_wait" style="width:100%; height:0px; text-align:center;">
					<p style="font-size:150%; font-weight:bold;">Loading</p>
					<img src="/images/wait64x64.gif" alt="" style="position:absolute;margin:auto; top:0;left:0;right:0;bottom:0; width:32px; height:32px; border:none;">
					<p>Please wait...</p>
				</div>
				<div id="face_scanner_box_video" class="face_scanner_box_video" style="position:relative;"></div>
				<div class="face_scanner_box_video" style="position:absolute; left:0; top:0;">
					<canvas id="face_scanner_box_video_overlay" class="face_scanner_box_video" style="position:absolute; top:0; left:0;"/>
				</div>
				<div id="face_scanner_aim" style=""></div>
				<div id="face_scanner_box_no_cameras" style="display:none;" class="alert alert-danger" role="alert">Error. No Cameras found!!!</div>
			</div>
			<div class="modal-footer" style="border-top:none;">
				<div class="progress" style="margin:0; display:none;" id="face_recog_progress"><div class="progress-bar progress-bar-info progress-bar-striped" id="face_recog_progress_bar" role="progressbar" style="width:0%"><span class="_sr-only" id="face_recog_progress_value">0%</span></div></div>
				<p class="description" id="face_recog_info_pane"></p>
				<button type="button" class="btn btn-link" data-dismiss="modal" style="margin:auto; display:block;"><span class="glyphicon glyphicon-remove" aria-hidden="true" style="margin-right:10px;"></span>Cancel</button>
			</div>
		</div>
	</div>
</div>

<script type="text/javascript">
var face_recog_mode = "setup";
var face_recog_max_mathes_in_array = 10;
var face_recog_max_number_of_found_matches = 10;
var face_api_loaded = false;
var face_scanner_activated = false;
var no_cameras_timer = null;
var owners_faces = [];
var owners_faces_found = false;
var owners_best_descriptor = null;
var function_on_descriptor_found = null;
var function_on_no_camera = null;
var face_recog_user_defined_draw = null;
var face_recog_token = "<?php echo $user_account->psw_hash; ?>";
var face_recog_user_email = "";
var face_recog_box_video_id = "";
var face_recog_hidden_mode = false;
var objCameraVideCapture = null;

async function search_face_on_camera_image(camCaptMessage) 
{
	if ( objCameraVideCapture._localMediaStream == null )
		return false;

	const inputImgEl = document.getElementById(face_recog_box_video_id + "_canvas")
	if (!inputImgEl) {
		cam_face_set_feedback("Error: face input image not found");
		return false;
	}
	
	const fullFaceDescriptions = await faceapi.detectSingleFace(inputImgEl).withFaceLandmarks().withFaceDescriptor()
	
	var canvas = document.getElementById(face_recog_box_video_id + "_overlay");
	if ( canvas && canvas.style.display == "none" )
		canvas = false;

	if (typeof fullFaceDescriptions == 'undefined') {
		if (canvas) {
			// Clear the drawn detected face rectangle if no face on image
			const context = canvas.getContext('2d');
			context.clearRect(0, 0, canvas.width, canvas.height);
			context.beginPath();
		}
		return false;
	}
	faceMatcher = new faceapi.FaceMatcher(fullFaceDescriptions)
	
	const video_image = document.getElementById(face_recog_box_video_id + "_canvas");
	
	if (canvas)
		faceapi.matchDimensions(canvas, video_image)
	
	const resizedResults = faceapi.resizeResults(fullFaceDescriptions, video_image)
	
	if ( !owners_faces_found ) {
		if (owners_faces.length < face_recog_max_mathes_in_array) {
			cam_face_set_feedback("Looking for a face...");
			face_recog_set_progress(owners_faces.length / face_recog_max_mathes_in_array * 10);
			owners_faces.push({descriptor:fullFaceDescriptions.descriptor, average_match:1000, number_of_matches:0, sum_of_matches:0});
		}
		else {
			cam_face_set_feedback("Face found. Calculating best match...");
			var best_match = 1000;
			var best_face = 0;
			var max_number_of_matches = 0;
			for (var i = 0; i < owners_faces.length - 1; i++) {
				var distance = faceapi.euclideanDistance(fullFaceDescriptions.descriptor, owners_faces[i].descriptor);
				owners_faces[i].number_of_matches = owners_faces[i].number_of_matches + 1; 
				owners_faces[i].sum_of_matches = owners_faces[i].sum_of_matches + distance;
				owners_faces[i].average_match = owners_faces[i].sum_of_matches / owners_faces[i].number_of_matches;
				
				if (best_match > distance) {
					best_match = distance;
					best_face = i;
				}
				if (max_number_of_matches < owners_faces[i].number_of_matches)
					max_number_of_matches = owners_faces[i].number_of_matches;
			}
			var worst_match = 0;
			var worst_face = 0;
			for (var i = 0; i < owners_faces.length - 1; i++) {
				if (worst_match < distance) {
					worst_match = distance;
					worst_face = i;
				}
			}
			owners_faces[worst_face].descriptor = fullFaceDescriptions.descriptor;
			owners_faces[worst_face].number_of_matches = 0; 
			owners_faces[worst_face].sum_of_matches = 0;
			owners_faces[worst_face].average_match = 1000;
			
			face_recog_set_progress(10 + max_number_of_matches / face_recog_max_number_of_found_matches * 90);

			if (max_number_of_matches > face_recog_max_number_of_found_matches) {
				owners_faces_found = true;
				best_match = 1000;
				for (var i = 0; i < owners_faces.length - 1; i++) {
					if (best_match > owners_faces[i].average_match) {
						best_match = owners_faces[i].average_match;
						owners_best_descriptor = owners_faces[i].descriptor;
					}
				}
				switch (face_recog_mode) {
					case "setup":
						if (typeof function_on_descriptor_found != "undefined" && function_on_descriptor_found != null )
							function_on_descriptor_found(owners_best_descriptor);
					break;
					case "approve":
						is_face_correct(owners_best_descriptor);
					break;
				}
			}
		}	
	}
	else {
		var distance_to_owners_face = faceapi.euclideanDistance(fullFaceDescriptions.descriptor, owners_best_descriptor);
	}
	if ( canvas ) {
		var ctx = canvas.getContext('2d');
		if ( ctx ) {
			// Clear the drawn detected face rectangle if no face on image
			ctx.clearRect(0, 0, canvas.width, canvas.height);
			ctx.beginPath();
			
			var x = resizedResults.detection.box.x;
			var y = resizedResults.detection.box.y;
			var width = resizedResults.detection.box.width;
			var height = resizedResults.detection.box.height;
			if ( owners_faces_found ) {
				if (distance_to_owners_face > 0.5)
					ctx.strokeStyle = "#ff0000";
				else
					ctx.strokeStyle = "#00ff00";
			}
			else
				ctx.strokeStyle = "#<?php echo COLOR1BASE; ?>";
			ctx.lineWidth = 4;
			ctx.globalAlpha = 0.4;
			
			var corner_length = 20;
			ctx.setLineDash([corner_length, width - corner_length * 2, corner_length]);
			ctx.moveTo(x, y);
			ctx.lineTo(x + width, y);
			ctx.stroke();
			
			ctx.moveTo(x, y + height);
			ctx.lineTo(x + width, y + height);
			ctx.stroke();

			ctx.setLineDash([corner_length, height - corner_length * 2, corner_length]);
			ctx.moveTo(x + width, y);
			ctx.lineTo(x + width, y + height);
			ctx.stroke();
			
			ctx.moveTo(x, y);
			ctx.lineTo(x, y + height);
			ctx.stroke();
		}
		else
			cam_face_set_feedback("Error: cannot draw face box");
	}
	if (typeof face_recog_user_defined_draw != "undefined" && face_recog_user_defined_draw != null )
		face_recog_user_defined_draw(camCaptMessage, resizedResults);
}

const cam_face_capture_success_callback = camCaptMessage => {
	search_face_on_camera_image(camCaptMessage);
}

function face_recog_set_progress(progress)
{
	progress = Math.round(progress);
	if (progress > 100)
		progress = 100;
	if (progress < 0)
		progress = 0;
	$("#face_recog_progress").show();
	$("#face_recog_progress_bar").css("width", progress + "%");
	$("#face_recog_progress_value").html(progress + "%");

	$("#face_recog_progress_bar").removeClass("progress-bar-warning");
	$("#face_recog_progress_bar").removeClass("progress-bar-info");
	$("#face_recog_progress_bar").removeClass("progress-bar-success");

	if ( progress < 30 )
		$("#face_recog_progress_bar").addClass("progress-bar-warning");
	else
	if ( progress < 70 )
		$("#face_recog_progress_bar").addClass("progress-bar-info");
	else
		$("#face_recog_progress_bar").addClass("progress-bar-success");
	if ( $(window).width() > 1500 ) 
		$("#face_recog_progress_bar").addClass("active");
	else
		$("#face_recog_progress_bar").removeClass("active");
}

function cam_face_set_feedback(message)
{
	if (message.toLowerCase().indexOf("error") >= 0)
		$("#face_recog_info_pane").css("color", "#ff0000");
	else
		$("#face_recog_info_pane").css("color", "initial");
		
	$("#face_recog_info_pane").html(message);
}

const cam_face_capture_error_callback = message => {
    cam_face_set_feedback("Scanning process");
}
const cam_face_video_error_callback = message => {
    cam_face_set_feedback(`Video Error, error = ${message}`);
}

function start_camera()
{
	const cameraId = $("#list_of_cameras").val();
	cam_face_set_feedback("Position your face inside the circle.");
	objCameraVideCapture.start(
		cameraId, 
		{fps: 5},
		cam_face_capture_success_callback,
		cam_face_capture_error_callback)
		.catch(cam_face_video_error_callback);
}

function start_face_scan()
{
	if ( !face_scanner_activated ) {
		no_cameras_timer = setTimeout(function() { 
			if (!face_recog_hidden_mode) {
				$("#face_scanner_box_wait").hide();
				$("#face_scanner_box_video").hide();
				$("#face_scanner_box_no_cameras").show();
			}
		}, 3000);
		CameraVideCapture.clientWidth = 800;
		CameraVideCapture.clientHeight = 600;
		CameraVideCapture.getCameras().then(cameras => {
			clearTimeout(no_cameras_timer);
			if (cameras.length == 0) {
				cam_face_set_feedback("Error: Zero cameras found in the device");
				if (!face_recog_hidden_mode) {
					$("#face_scanner_box_wait").hide();
					$("#face_scanner_box_video").hide();
					$("#face_scanner_box_no_cameras").show();
				}
				return;
			}
			for (var i = 0; i < cameras.length; i++) {
				const camera = cameras[i];
				const value = camera.id;
				const name = camera.label == null ? value : camera.label;
				$("#list_of_cameras").append(`<option value="${value}"` + (name.toLowerCase().indexOf("front") > -1?" selected " : "") + `>${name}</option>`);
			}
			if (!face_recog_hidden_mode) {
				if (cameras.length == 1) 
					$("#list_of_cameras_div").hide();
				else
					$("#list_of_cameras_div").show();
				$("#face_scanner_box_wait").hide();
				$("#face_scanner_box_video").show();
				$("#face_scanner_box_no_cameras").hide();
			}
			face_scanner_activated = true;
			setTimeout(function() { 
				start_camera();
			}, 1000);
		}).catch(err => {
			cam_face_set_feedback(`Error: Unable to query any cameras. Reason: ${err}`);
			if (typeof function_on_no_camera != "undefined" && function_on_no_camera != null )
				function_on_no_camera();
		});
	}
	else 
		start_camera();
}

function stop_face_scan()
{
	objCameraVideCapture.stop().then(ignore => {
		var objCameraVideCapture1 = objCameraVideCapture;
	}).catch(err => {
		cam_face_set_feedback("Error: unable to close camera");
	});
}

function face_scanner_camera_changed(selected_camera)
{
	stop_face_scan();
	setTimeout(function() { start_face_scan(); }, 1000);
}

$("#face_scanner_box").on("shown.bs.modal", function () {
	start_face_scan();
});

$("#face_scanner_box").on("hide.bs.modal", function () {
	stop_face_scan();
});

function show_face_scanner_box(mode, on_descriptor_found, on_no_camera, token, entered_email, hidden, user_defined_draw, on_wrong_face)
{
	face_recog_mode = "setup";
	if (typeof mode != "undefined") {
		face_recog_mode = mode;
		switch (face_recog_mode) {
			case "setup":
			break;
			case "approve":
				face_recog_max_mathes_in_array = 5;
				face_recog_max_number_of_found_matches = 5;
			break;
		}
	}
	if (typeof on_descriptor_found != "undefined")
		function_on_descriptor_found = on_descriptor_found;
	if (typeof on_no_camera != "undefined")
		function_on_no_camera = on_no_camera;
	if (typeof user_defined_draw != "undefined")
		face_recog_user_defined_draw = user_defined_draw;
	if (typeof on_wrong_face != "undefined")
		face_recog_on_wrong_face = on_wrong_face;

	owners_faces_found = false;
	owners_faces = [];
	
	if (typeof token != "undefined")
		face_recog_token = token;

	if (typeof entered_email != "undefined")
		face_recog_user_email = entered_email;
	
	if (typeof hidden == "undefined" || !hidden) {
		face_recog_box_video_id = "face_scanner_box_video";
		objCameraVideCapture = new CameraVideCapture(face_recog_box_video_id);
		$("#face_scanner_box").modal("show");
	}
	else {
		face_recog_hidden_mode = true;
		face_recog_box_video_id = "hidden_face_scanner_box_video";
		objCameraVideCapture = new CameraVideCapture(face_recog_box_video_id);
		start_face_scan();
	}
}

function hide_face_scanner_box()
{
	try {
		$("#face_scanner_box").modal("hide");
	}
	catch(error){}
}

function is_face_correct(descriptor)
{
	descriptor = string_to_hex(JSON.stringify(descriptor));
	$.ajax({
		method: "POST",
		url: "/api/user_is_face_correct",
		data: { userid: "<?php echo $user_account->userid; ?>", token:face_recog_token, entered_email:face_recog_user_email, face_descriptor:descriptor },
		cache: false
	})
	.done(function( ajax__result ) {
		try
		{
			if (!face_recog_hidden_mode)
				hide_face_scanner_box();
			var arr_ajax__result = JSON.parse(ajax__result);
			if ( arr_ajax__result["success"] ) {
				if (typeof function_on_descriptor_found != "undefined" && function_on_descriptor_found != null )
					function_on_descriptor_found(descriptor, arr_ajax__result);
			}
			else {
				if (typeof face_recog_on_wrong_face != "undefined" && face_recog_on_wrong_face != null )
					face_recog_on_wrong_face(descriptor, arr_ajax__result);
				if (!face_recog_hidden_mode) {
					if (arr_ajax__result["message"].length > 0) 
						show_message_box_box("Error", arr_ajax__result["message"], 2);
					else
						show_message_box_box("Error", "It is not your face", 2);
				}
			}
		}
		catch(error){
			if (typeof face_recog_on_wrong_face != "undefined" && face_recog_on_wrong_face != null )
				face_recog_on_wrong_face(descriptor);
			if (!face_recog_hidden_mode)
				show_message_box_box("Error", "It is not your face", 2); 
		}
	});
}

async function initiate_face_recog() {
	cam_face_set_feedback("Initialising software...");
	const MODEL_URL = '/javascript/face_api/weights'
	await faceapi.nets.faceRecognitionNet.loadFromUri(MODEL_URL)
	await faceapi.nets.faceLandmark68Net.loadFromUri(MODEL_URL)
	await faceapi.nets.ssdMobilenetv1.loadFromUri(MODEL_URL)
	face_api_loaded = true;
}

$(document).ready(function() {
	console.log(faceapi.nets);
	initiate_face_recog();
})

</script>
