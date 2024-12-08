/**
 * Camera capture library.
*/
class CameraVideCapture {
    /*static DEFAULT_HEIGHT = 250;
    static DEFAULT_HEIGHT_OFFSET = 2;
    static DEFAULT_WIDTH = 300;
    static DEFAULT_WIDTH_OFFSET = 2;
    static SCAN_DEFAULT_FPS = 2;
    static VERBOSE = false;*/
	/*DEFAULT_HEIGHT: 250,
    DEFAULT_HEIGHT_OFFSET: 2,
    DEFAULT_WIDTH: 300,
    DEFAULT_WIDTH_OFFSET: 2,
    SCAN_DEFAULT_FPS: 2,
    VERBOSE: false,*/

    /**
     * Initialize QR Code scanner.
     * 
     * @param {String} elementId - Id of the HTML element. 
     */
    constructor(elementId) {
		this.DEFAULT_HEIGHT = 250;
		this.DEFAULT_HEIGHT_OFFSET = 2;
		this.DEFAULT_WIDTH = 300;
		this.DEFAULT_WIDTH_OFFSET = 2;
		this.SCAN_DEFAULT_FPS = 2;
		this.VERBOSE = false;

        this._elementId = elementId;
        this._foreverScanTimeout = null;
        this._localMediaStream = null;
        this._shouldScan = true;
        this._url = window.URL || window.webkitURL || window.mozURL || window.msURL;
        this._userMedia = navigator.getUserMedia || navigator.webkitGetUserMedia 
            || navigator.mozGetUserMedia || navigator.msGetUserMedia;
    }

    /**
     * Start Code for given camera.
     * 
     * @param {String} cameraId Id of the camera to use.
     * @param {Object} config extra configurations.
     *  Supported Fields:
     *      - fps: expected framerate of qr code scanning. example { fps: 2 }
     *          means the scanning would be done every 500 ms.
     * @param {Function} camVidCaptFrameReceived callback on QR Code found.
     *  Example:
     *      function(camVidCaptMessage) {}
     * @param {Function} camVidCaptErrorCallback callback on QR Code parse error.
     *  Example:
     *      function(errorMessage) {}
     * 
     * @returns Promise for starting the scan. The Promise can fail if the user
     * doesn't grant permission or some API is not supported by the browser.
     */
    start(cameraId,
        configuration,
        camVidCaptFrameReceived,
        camVidCaptErrorCallback) {
        if (!cameraId) {
            throw "cameraId is required";
        }

        if (!camVidCaptFrameReceived || typeof camVidCaptFrameReceived != "function") {
            throw "camVidCaptFrameReceived is required and should be a function."
        }

        if (!camVidCaptErrorCallback) {
            camVidCaptErrorCallback = console.log;
        }

        const $this = this;

        // Create configuration by merging default and input settings.
        const config = configuration ? configuration : {};
        config.fps = config.fps ? config.fps : CameraVideCapture.SCAN_DEFAULT_FPS;


        const element = document.getElementById(this._elementId);
        const width = element.clientWidth ? element.clientWidth : CameraVideCapture.DEFAULT_WIDTH;
        const height = element.clientHeight ? element.clientHeight : CameraVideCapture.DEFAULT_HEIGHT;
        const videoElement = this._createVideoElement(width, height);
        const canvasElement = this._createCanvasElement(width, height);
        const context = canvasElement.getContext('2d');
        context.canvas.width = width;
        context.canvas.height = height;

		context.canvas.width = width;
        context.canvas.height = height;

        element.append(videoElement);
        element.append(canvasElement);

        // save local states
        this._element = element;
        this._videoElement = videoElement;
        this._canvasElement = canvasElement;

        this._shouldScan = true;
		$this.callback = camVidCaptFrameReceived;

        // Method that scans forever.
        const foreverScan = () => {
            if (!$this._shouldScan) {
                // Stop scanning.
                return;
            }
            if ($this._localMediaStream) {
                var videoElementWidth = 0;
				var videoElementHeight = 0;
				var left = 0;
				var top = 0;
				if (videoElement.videoWidth > 0 && videoElement.videoHeight > 0) {
					var canvas_aspect_ratio = videoElement.clientWidth / videoElement.clientHeight;
					var video_aspect_ratio = videoElement.videoWidth / videoElement.videoHeight;
					
					if (canvas_aspect_ratio > video_aspect_ratio) {
						videoElementHeight = videoElement.clientHeight;
						videoElementWidth = Math.round(videoElement.videoWidth * videoElementHeight / videoElement.videoHeight );
						top = 0;
						left = Math.round((videoElement.clientWidth - videoElementWidth) / 2);
					}
					else {
						videoElementWidth = videoElement.clientWidth;
						videoElementHeight = Math.round(videoElementWidth * videoElement.videoHeight / videoElement.videoWidth );
						left = 0;
						top = Math.round((videoElement.clientHeight - videoElementHeight) / 2);
					}
					
					context.drawImage(videoElement, left, top, videoElementWidth, videoElementHeight);
					
					try {
						$this.callback({video:videoElement, canvas:context, canvas_element:this._canvasElement, video_frame:{left:left, top:top, width:videoElementWidth, height:videoElementHeight}});
					} catch (exception) {
						camVidCaptErrorCallback(`QR code parse error, error = ${exception}`);
					}
				}
            }
            $this._foreverScanTimeout = setTimeout(foreverScan, CameraVideCapture._getTimeoutFps(config.fps));
        }

        // success callback when user media (Camera) is attached.
        const getUserMediaSuccessCallback = stream => {
            videoElement.srcObject = stream;
            videoElement.play();
            $this._localMediaStream = stream;
            foreverScan();
        }

        return new Promise((resolve, reject) => {
            if (navigator.mediaDevices && navigator.mediaDevices.getUserMedia) {
                navigator.mediaDevices.getUserMedia(
                    { audio: false, video: { deviceId: { exact: cameraId }}})
                    .then(stream => {
                        getUserMediaSuccessCallback(stream);
                        resolve();
                    })
                    .catch(err => {
                        reject(`Error getting userMedia, error = ${err}`);
                    });
            } else if (navigator.getUserMedia) {
                const getCameraConfig = { video: { optional: [{ sourceId: cameraId }]}};
                navigator.getUserMedia(getCameraConfig,
                    stream => {
                        getUserMediaSuccessCallback(stream);
                        resolve();
                    }, err => {
                        reject(`Error getting userMedia, error = ${err}`);
                    });
            } else {
                reject("Web camera streaming not supported by the browser.");
            }
        });
    }

    /**
     * Stops streaming video. 
     * 
     * @returns Promise for safely closing the video stream.
     */
    stop() {
        // TODO(mebjas): fail fast if the start() wasn't called.
        this._shouldScan = false;
        clearTimeout(this._foreverScanTimeout);

        const $this = this;
        return new Promise((resolve, reject) => {
            const tracksToClose = $this._localMediaStream.getVideoTracks().length;
            var tracksClosed = 0;

            const onAllTracksClosed = () => {
                $this._localMediaStream = null;
                $this._element.removeChild($this._videoElement);
                $this._element.removeChild($this._canvasElement);
                resolve(true);
            }

            $this._localMediaStream.getVideoTracks().forEach(videoTrack => {
                videoTrack.stop();
                ++tracksClosed;

                if (tracksClosed >= tracksToClose) {
                    onAllTracksClosed();
                }
            });
        });
    }

    /**
     * Returns a Promise with list of all cameras supported by the device.
     * 
     * The returned object is a list of result object of type:
     * [{
     *      id: String;     // Id of the camera.
     *      label: String;  // Human readable name of the camera.
     * }]
     */
    static getCameras() {
        return new Promise((resolve, reject) => {
            if (navigator.mediaDevices 
                && navigator.mediaDevices.enumerateDevices
                && navigator.mediaDevices.getUserMedia) {
                this._log("navigator.mediaDevices used");
                navigator.mediaDevices.getUserMedia({audio: false, video: true}).then(ignore => {
                    navigator.mediaDevices.enumerateDevices()
                    .then(devices => {
                        const results = [];
                        for (var i = 0; i < devices.length; i++) {
                            const device = devices[i];
                            if (device.kind == "videoinput") {
                                results.push({
                                    id: device.deviceId,
                                    label: device.label
                                });
                            }
                        }
                        this._log(`${results.length} results found`);
                        resolve(results);
                    })
                    .catch(err => {
                        reject(`${err.name} : ${err.message}`);
                    });
                }).catch(err => {
                    reject(`${err.name} : ${err.message}`);
                })
            } else if (MediaStreamTrack && MediaStreamTrack.getSources) {
                this._log("MediaStreamTrack.getSources used");
                const callback = sourceInfos => {
                    const results = [];
                    for (var i = 0; i !== sourceInfos.length; ++i) {
                        const sourceInfo = sourceInfos[i];
                        if (sourceInfo.kind === 'video') {
                            results.push({
                                id: sourceInfo.id,
                                label: sourceInfo.label
                            });
                        }
                    }
                    this._log(`${results.length} results found`);
                    resolve(results);
                }
                MediaStreamTrack.getSources(callback);
            } else {
                this._log("unable to query supported devices.");
                reject("unable to query supported devices.");
            } 
        });
    }

    _createCanvasElement(width, height) {
        //const canvasWidth = "100%";// - CameraVideCapture.DEFAULT_WIDTH_OFFSET;
        //const canvasHeight = "100%";// - CameraVideCapture.DEFAULT_HEIGHT_OFFSET;
        const canvasElement = document.createElement('canvas');
        //canvasElement.style.width = `${canvasWidth}px`;
        //canvasElement.style.height = `${canvasHeight}px`;
		canvasElement.style.width = "100%";
        canvasElement.style.height = "100%";
        //canvasElement.style.display = "none";
		canvasElement.style.position = "absolute";
		canvasElement.style.opacity = "0";
        // This id is set by lazarsoft/jscamVidCapt
        canvasElement.id = this._elementId + '_canvas';
        return canvasElement;
    }

    _createVideoElement(width, height) {
        const videoElement = document.createElement('video');
        //videoElement.style.height = `${height}px`;
        //videoElement.style.width = `${width}px`;
		videoElement.style.width = "100%";
        videoElement.style.height = "100%";
        videoElement.style.position = "absolute";
		//videoElement.style.opacity = "0";
		videoElement.id = this._elementId + '_video';
        return videoElement;
    }

    static _getTimeoutFps(fps) {
        return 1000 / fps;
    }

    static _log(message) {
        if (CameraVideCapture.VERBOSE) {
            console.log(message);
        }
    }
}
