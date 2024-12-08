<style>
#qr_scanner_aim{width:280px; height:280px; position:absolute; border: 2px dashed #ffffff; left: 160px; top: 84px; opacity:0.3; }
#qr_scanner_box_video,
#qr {width: 600px; height: 450px; display:block; margin:auto;}
@media(max-width: 600px) {
	#qr_scanner_aim{width:160px; height:160px; left:70px; top:20px;}
	#qr_scanner_box_video,
    #qr {width: 300px; height: 200px;}
}
button:disabled,
button[disabled]{
  opacity: 0.5;
}
</style>
<!-- QR Scanner box -->  
<div class="modal fade" id="qr_scanner_box" role="dialog" style="display:none;">
	<div class="modal-dialog" id="qr_scanner_box_modal_dialog" style="margin-left:10px; margin-right:10px; padding:0; width:100%;">
		<div class="modal-content">
			<div class="modal-header" style="border-bottom:none;">
				<button type="button" class="close" data-dismiss="modal">&times;</button>
				<div id="list_of_cameras_div" style="display:none;"><span class="visible_on_big_screen">Select Camera: </span><select class="form-control" id="list_of_cameras" style="font-size:90%; padding:4px; max-width:200px; height:auto; display:inline-block;" onchange="qr_scanner_camera_changed(this)"></select></div>
			</div>
			<div class="modal-body" style="padding:10px 0 20px 0px;">
				<div id="qr_scanner_box_wait" style="width:100%; height:0px;">
					<img src="/images/wait64x64.gif" alt="" style="position:absolute;margin:auto; top:0;left:0;right:0;bottom:0; width:32px; height:32px; border:none;">
				</div>
				<div id="qr_scanner_box_video" style="position:relative;">
					<div id="qr_scanner_aim" style=""></div>
				</div>
				<div id="qr_scanner_box_no_cameras" style="display:none;" class="alert alert-danger" role="alert">Error. No Cameras found!!!</div>
			</div>
		</div>
	</div>
</div>

<script src="/javascript/qr-scanner/html5qrcode.min.js"></script>

<script type="text/javascript">

var qr_scanner_code_found = "";
var qr_scanner_on_code_found = null;
var qr_scanner_activated = false;
var no_cameras_timer = null;

const qrCodeSuccessCallback = qrCodeMessage => {
	setStatus("Pattern Found");
    qr_scanner_code_found = qrCodeMessage;
	if (typeof qr_scanner_on_code_found === "function")
		qr_scanner_on_code_found(qrCodeMessage);
	setTimeout(function() { $("#qr_scanner_box").modal("hide"); }, 100);
}

function setStatus(status) {
	console.log(status);
}

function setFeedback(message) {
    console.log(status);
}

const qrCodeErrorCallback = message => {
    setStatus("Scanning process");
}
const videoErrorCallback = message => {
    setFeedback(`Video Error, error = ${message}`);
}

const html5QrCode = new Html5Qrcode("qr_scanner_box_video");

function start_camera()
{
	const cameraId = $("#list_of_cameras").val();
	setStatus("start scanning");
	html5QrCode.start(
		cameraId, 
		{fps: 10},
		qrCodeSuccessCallback,
		qrCodeErrorCallback)
		.catch(videoErrorCallback);
}

function start_scan()
{
	if ( !qr_scanner_activated ) {
		no_cameras_timer = setTimeout(function() { 
			$("#qr_scanner_box_wait").hide();
			$("#qr_scanner_box_video").hide();
			$("#qr_scanner_box_no_cameras").show();
		}, 3000);
		Html5Qrcode.getCameras().then(cameras => {
			clearTimeout(no_cameras_timer);
			if (cameras.length == 0) {
				setFeedback("Error: Zero cameras found in the device");
				$("#qr_scanner_box_wait").hide();
				$("#qr_scanner_box_video").hide();
				$("#qr_scanner_box_no_cameras").show();
				return;
			}
			for (var i = 0; i < cameras.length; i++) {
				const camera = cameras[i];
				const value = camera.id;
				const name = camera.label == null ? value : camera.label;
				$("#list_of_cameras").append(`<option value="${value}"` + (name.toLowerCase().indexOf("back") > -1?" selected " : "") + `>${name}</option>`);
			}
			if (cameras.length == 1) 
				$("#list_of_cameras_div").hide();
			else
				$("#list_of_cameras_div").show();
			
			$("#qr_scanner_box_wait").show();
			$("#qr_scanner_box_video").show();
			$("#qr_scanner_box_no_cameras").hide();

			qr_scanner_activated = true;
			setTimeout(function() { 
				start_camera();
			}, 1000);
		}).catch(err => {
			setFeedback(`Error: Unable to query any cameras. Reason: ${err}`);
		});
	}
	else 
		start_camera();
}

function stop_scan()
{
	html5QrCode.stop().then(ignore => {
		
	}).catch(err => {
		setFeedback('Error');
		setFeedback("Race condition, unable to close the scan.");
	});
}

function qr_scanner_camera_changed(selected_camera)
{
	stop_scan();
	setTimeout(function() { start_scan(); }, 1000);
}

$("#qr_scanner_box").on("shown.bs.modal", function () {
	start_scan();
});

$("#qr_scanner_box").on("hide.bs.modal", function () {
	stop_scan();
});

function show_qr_scanner_box(on_code_found)
{
	qr_scanner_on_code_found = on_code_found;
	$("#qr_scanner_box").modal("show");
	//$("qr_scanner_box_modal_dialog").
}

</script>
