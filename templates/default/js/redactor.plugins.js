if (typeof RedactorPlugins === 'undefined') var RedactorPlugins = {};

RedactorPlugins.youtube_video = {

	init: function(){
		this.addBtnAfter('image', 'youtube_video', 'Vídeos', this.handleClick);
	},
	
	handleClick: function(obj){
		
		obj.saveSelection();
		
		var clickCallback = $.proxy(function(){
			var url = $("#redactor_insert_video_url").val();
			var parsed_url = parseUri(url);
			var HTML = '';
			
			switch(parsed_url.authority){
				case "youtube.com":
				case "www.youtube.com":
					if(typeof parsed_url.queryKey.v != 'undefined'){
						HTML = '<iframe width="100%" height="315" src="https://www.youtube.com/embed/'+parsed_url.queryKey.v+'" frameborder="0" allowfullscreen></iframe>';
					}
				break;
				
				case "youtu.be":
					HTML = '<iframe width="100%" height="315" src="https://www.youtube.com/embed/'+parsed_url.directory+'" frameborder="0" allowfullscreen></iframe>';
					break;
			}
			
			if(HTML != ''){
				data = this.stripTags(HTML);
				this.restoreSelection();
				this.execCommand('inserthtml', data);
				this.modalClose();
			}
			
		}, obj);
		
		var overlayHTML = ''+
		'<div id="redactor_modal_content">' +
		'	<form id="redactorInsertVideoForm">' +
		'		<label>URL do vídeo:</label>' +
		'		<input type="text" id="redactor_insert_video_url" style="width: 99%;font-family:Helvetica, Arial;padding:4px 3px;" autocomplete="off" />' +
		'	</form>' +
		'</div>'+
		'<div id="redactor_modal_footer">' +
		'	<span class="redactor_btns_box">'+
		'		<a href="javascript:void(null);" class="redactor_modal_btn redactor_btn_modal_close">cancelar</a>' +
		'		<input type="button" class="redactor_modal_btn" id="redactor_insert_video_btn" value="Adicionar vídeo" />' +
		'	</span>'+
		'</div>'
		
		
		obj.modalInit('Adicionar vídeo', overlayHTML, 500);
		
		setTimeout(function(){
			$("#redactor_insert_video_url").focus();
			
			$("#redactor_insert_video_btn").click(clickCallback);
			
			$("#redactorInsertVideoForm").submit(function(e){
				e.preventDefault();
				e.stopPropagation();
				$("#redactor_insert_video_btn").trigger("click");
			});
		}, 250)
	}
}

RedactorPlugins.convert_video_urls = {
	init: function(){
		
		editor = this;
		editor.$editor[0].redactor_instance = editor;
		
		$(this.$editor[0]).bind("keypress keydown change", function(e){
			var charCode = (e.which) ? e.which : e.keyCode
			if(charCode == 13){
				$(this).find("a[href*=youtu]").each(function(){
					e.preventDefault();
					var $youtube_link = $(this);
					var parsed_url = parseUri($youtube_link.attr("href"));
					var HTML = '';
			
							switch(parsed_url.authority){
						case "youtube.com":
						case "www.youtube.com":
							if(typeof parsed_url.queryKey.v != 'undefined'){
								HTML = '<iframe width="100%" height="315" src="https://www.youtube.com/embed/'+parsed_url.queryKey.v+'" frameborder="0" allowfullscreen></iframe>';
							}
						break;
						
						case "youtu.be":
							HTML = '<iframe width="100%" height="315" src="https://www.youtube.com/embed/'+parsed_url.directory+'" frameborder="0" allowfullscreen></iframe>';
							break;
					}
					
					if(HTML != ''){
						$youtube_link.replaceWith(HTML);
						editor.syncCode();
					}
				});
			}
		});
	}
}

RedactorPlugins.spoiler = {
	
	init: function(){
		this.addBtn('spoiler', 'Spoiler', this.handleClick);
	},
	
	handleClick: function(obj){
		obj.saveSelection();
		
		var parentNode = obj.getCurrentNode();
		var selectedText = obj.getSelectedHtml();
		
		obj.setBuffer()
		
		var ReplacedNodeText = $(parentNode).html().replace(selectedText, "[spoiler]" + selectedText + "[/spoiler]");
		$(parentNode).html(ReplacedNodeText);
		obj.syncCode();
	}
	
}
