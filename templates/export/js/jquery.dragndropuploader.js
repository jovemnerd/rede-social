(function ($) {
	$.fn.dragndropuploader = function (options) {

		options = options ? options : {};

		var $elements = $(this);

		var default_options = {
			allowed_extensions: [],
			upload_url: '',
			upload_request_filename: '',
			allow_multiple_upload: true,
			invalid_extension_error_handler: function (filename) {
				alert("ERROR!\n" + filename + " does not have an allowed extension.");
			},
			before_upload_handler: function () {},
			upload_finished_handler: function () {},
			proccess_server_response_handler: function (server_response) {},
			fallback: function () {}
		};

		$.extend(default_options, options);

		$elements.each(function () {
			if (!window.FileReader) {
				options.fallback();
				return;
			}

			var current_element = $(this).get(0);

			if (!current_element) return;

			current_element.addEventListener('drop', function (event) {
				event.stopPropagation();
				event.preventDefault();

				var data = event.dataTransfer;
				var files = data.files;
				var file = files[0];

				if (!file) {
					return false;
				}

				var filename = $.trim(file.name);
				var file_extension = (filename.split(".")[filename.split(".").length - 1]).toLowerCase();
				var valid_extension = $.inArray(file_extension, options.allowed_extensions);

				if (valid_extension === false) {
					options.invalid_extension_error_handler(filename);
					return false;
				}

				if (file.name) {

					var reader = new FileReader();
					reader.original_file = file;
					reader.onloadend = function (evt) {
						if (evt.target.readyState == FileReader.DONE) {
							var fileName = (this.original_file.name) ? this.original_file.name : this.original_file.fileName,
								fileSize = (this.original_file.size) ? this.original_file.size : this.original_file.fileSize,
								fileData = evt.currentTarget.result,
								boundary = "xxxxxxxxx",
								uri = options.upload_url,
								xhr = new XMLHttpRequest();

							xhr.open("POST", uri, true);
							xhr.setRequestHeader("Content-Type", "multipart/form-data, boundary=" + boundary);

							xhr.onreadystatechange = function () {
								options.upload_finished_handler();
								if (xhr.readyState == 4) {
									if ((xhr.status >= 200 && xhr.status <= 200) || xhr.status == 304) {
										if (xhr.responseText != "") {
											options.proccess_server_response_handler(xhr.responseText);
										}
									}
								}
							}

							var body = "--" + boundary + "\r\n";
							body += "Content-Disposition: form-data; name='" + options.upload_request_filename + "'; filename='" + fileName + "'\r\n";
							body += "Content-Type: application/octet-stream\r\n\r\n";
							body += fileData + "\r\n";
							body += "--" + boundary + "--";

							if (!xhr.sendAsBinary) {
								XMLHttpRequest.prototype.sendAsBinary = function (datastr) {
									var ui8a = new Uint8Array(datastr.length);
									for (var i = 0; i < datastr.length; i++)
									ui8a[i] = (datastr.charCodeAt(i) & 0xff);
									this.send(ui8a.buffer);
								}
							}

							xhr.sendAsBinary(body);
							return true;
						}
					};
					if (file.webkitSlice) {
						var blob = file.webkitSlice(0, file.size);
					} else if (file.mozSlice) {
						var blob = file.mozSlice(0, file.size);
					}

					options.before_upload_handler(file.name);
					reader.readAsBinaryString(blob);
				}


			}, false);

			current_element.addEventListener('dragover', function (event) {
				event.stopPropagation();
				event.preventDefault();
			}, false);

		});
		return $elements;
	}
}(jQuery));