/*
filedrag.js - HTML5 File Drag & Drop demonstration
Featured on SitePoint.com
Developed by Craig Buckler (@craigbuckler) of OptimalWorks.net
*/
(function(window) {
	var xhr = new XMLHttpRequest();
	var formData = new FormData();

	// getElementById
	function $id(id) {
		var el = document.getElementById(id);
		return el;
	}

/*
	// output information
	function Output(msg) {
		var m = $id("messages");
		m.innerHTML = msg + m.innerHTML;
	}
*/

	// file drag hover
	function FileDragHover(e) {
		e.stopPropagation();
		e.preventDefault();
		e.target.className = (e.type == "dragover" ? "hover" : "");
	}


	// file selection
	function FileSelectHandler(e) {

		// cancel event and hover styling
		FileDragHover(e);

		// fetch FileList object
		var files = e.target.files || e.dataTransfer.files;

		// process all File objects
		for (var i = 0, f; f = files[i]; i++) {
			ParseFile(f);
			formData.append('upload-file[]', f);
		}

		$('#filedrag').html( i + ' File(s) selected' );

		if ( pin_no != '' )
			UploadFile( user_no, markerInfo, 'EDIT_PIN' );
	}


	// output file information
	function ParseFile(file) {

		console.log(
			"File information: " + file.name +
			" type: " + file.type +
			" size: " + file.size +
			"bytes</p>"
		);

	}

	// upload file
	function UploadFile( user_no, pin_info, type ) {
/*
		var xhr = new XMLHttpRequest();
		xhr.onreadystatechange=function()
		{
			if (xhr.readyState==4 && xhr.status==200)
			{
				alert( xhr.responseText );
			}
		}

		if (xhr.upload && file.size <= $id("MAX_FILE_SIZE").value) {
			// start upload

			xhr.open("POST", "ajax.php", true);
//			xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
//			xhr.setRequestHeader("Content-length", params.length);
//			xhr.setRequestHeader("X_FILENAME", file.name);

			xhr.send(formData);
		}
*/

		formData.append('action', 'DROPDOWN_UPLOAD');
		formData.append('user-no', user_no);
		formData.append('pin-no', pin_info.no);

		xhr.onreadystatechange=function()
		{
			if (xhr.readyState==4 && xhr.status==200)
			{
				var img_list = $.parseJSON( xhr.response );
				pin_info.img_cnt = img_list.length;
				pin_info.img_list = img_list;
				
				if ( type == 'ADD_PIN' )
				{
					parent.arrMarker.push( pin_info );
					parent.makeMarker( pin_info );
					parent.show_loading( false );
					parent.JqueryDialog.Close();
				}
				else
				{
					parent.changeMarker( markerInfo );
					document.location.href = document.location.href;
					parent.show_loading( false );
				}
			}
		}

		xhr.upload.addEventListener("progress", function(e) {
			var pc = parseInt( e.loaded / e.total * 100 );
			console.log( "Percentage: " + pc + "%" );
			$('.progress-bar').css("width", pc + "%");
		}, false);

		if ( xhr.upload ) {
			// start upload
			parent.show_loading( true );
			xhr.open("POST", "ajax.php", true);
			xhr.send(formData);
		}
	}

	// initialize
	function Init() {

/*
		var fileselect = $id("fileselect"),
			filedrag = $id("filedrag"),
			submitbutton = $id("submitbutton");
*/

		var fileselect = $id("fileselect");
		var filedrag = $id("filedrag");

		// file select
		fileselect.addEventListener("change", FileSelectHandler, false);

		// is XHR2 available?
		var xhr = new XMLHttpRequest();
		if (xhr.upload) {

			// file drop
			filedrag.addEventListener("dragover", FileDragHover, false);
			filedrag.addEventListener("dragleave", FileDragHover, false);
			filedrag.addEventListener("drop", FileSelectHandler, false);
			filedrag.style.display = "block";

			// remove submit button
//			submitbutton.style.display = "none";
		}

		$('#filedrag').click(function(){
			$('#fileselect').click();
		});
	}

	// call initialization file
	if (window.File && window.FileList && window.FileReader) {
		Init();
	}

	window.UploadFile = UploadFile;

})(window);

