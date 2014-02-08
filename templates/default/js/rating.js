$(document).ready(function(){

	$(".bt-positivar, .bt-negativar").live('click', function(){
		var $parent = $(this).parent();
		var $el = $(this);
		
		var data = {
			rating: $el.hasClass('bt-positivar') ? 1 : -1
		};
		
		if($el.siblings('.meu-voto').length > 0){
			data.action = 'change_rate'
			data.current_vote = ($el.siblings('.meu-voto').hasClass('bt-positivar')) ? '1' : '-1';
		}else if($el.hasClass('meu-voto')){
			data.action = 'unrate';
			data.current_vote = ($el.hasClass('bt-positivar')) ? '1' : '-1';
		} else {
			data.action = 'rate';
		}
		
		if($parent.hasClass('dados')){
			data.comment_id = $parent.attr("rel"); 
		} else if($parent.hasClass('balao')){
			data.post_id = $parent.attr("rel");
		}
		
		$.ajax({
			type: "POST",
			url: HOST+"perfil/avaliar",
			data: data,
			success: function(response){
				var o = $.parseJSON(response);
				if(o.status == true){
					if(data.action == 'rate'){
						$el.addClass('meu-voto');
					} else if(data.action == 'unrate') {
						$el.removeClass('meu-voto');
					} else if(data.action == 'change_rate'){
						$el.addClass('meu-voto').siblings().removeClass("meu-voto");
					}
					
					$el.parent().find(".res-positivar").html("<span></span>"+o.quantity.megaboga)
					$el.parent().find(".res-negativar").html("<span></span>"+o.quantity.whatever)
				}
			}
		});
	});
	
	
	$(".bt-favoritar").live("click", function(){
		var Action = ($(this).hasClass('favorito')) ? 'unfavorite' : 'favorite';
		var PostID = $(this).parent().attr("rel")
		$(this).toggleClass("favorito");
		
		$.post(HOST+"perfil/favoritar", {
			post_id: PostID,
			action: Action
		}, function(server_response){
			
		});
	});
	
	
	$(".bt-reblog").live("click", function(){
		var PostID = $(this).data("post-id");
		var url = ($(this).hasClass("reblogged")) ? "perfil/desblogar" : "perfil/reblogar";
		
		var $el = $(this);
		
		$.ajax({
			url: HOST+url,
			async: false,
			data: {
				postID: PostID
			},
			success: function(response){
				$el.toggleClass("reblogged");
			}
		})
	});
	
	
	$(".res-positivar, .res-negativar").live("click", function(){
		var $el = $(this);
		var $parent = $(this).parent();
		
		var data = {
			rating: $el.hasClass('res-positivar') ? 1 : -1
		};
		
		if($parent.hasClass('dados')){
			data.comment_id = $parent.attr("rel"); 
		} else if($parent.hasClass('balao')){
			data.post_id = $parent.attr("rel");
		}
		
		$.ajax({
			type: "POST",
			url: HOST+"perfil/avaliacoes",
			data: data,
			beforeSend: function(){
				if(data.post_id) title = (data.rating == '1') ? "Nerds que marcaram este post como MEGABOGA" : "Nerds que marcaram este post como WHATEVER";
				else	title = (data.rating == '1') ? "Nerds que marcaram este comentário como MEGABOGA" : "Nerds que marcaram este comentário como WHATEVER";
				var HTML = "<h1 style='padding:4px 0px 8px'>"+title+"</h1>"
				+ "<div style='background-color:#fff;box-shadow:0px 0px 3px #444 inset;padding:15px;width:500px;'>"
				+ "		<p>Aguarde.. Carregando..</p>";
				+ "</div>"
				
				$("#overlay .content").html(HTML).css({"margin-left": -265});
				$("#overlay").fadeIn();
			},
			success: function(server_response){
				var json = $.parseJSON(server_response);
				var i = 0;
				
				if(data.post_id) title = (data.rating == '1') ? "Nerds que marcaram este post como MEGABOGA" : "Nerds que marcaram este post como WHATEVER";
				else	title = (data.rating == '1') ? "Nerds que marcaram este comentário como MEGABOGA" : "Nerds que marcaram este comentário como WHATEVER";
				
				var HTML = ''
				+ "<h1 style='padding:4px 0px 8px'>"+title+"</h1>"
				+ "<div style='background-color:#fff;box-shadow:0px 0px 3px #444 inset;padding:15px;width:500px;'>"
				+ "	<ul class='rating'>";
					for(i in json){
						HTML += ""
						+ "	<li>"
						+ "		<a href='"+HOST+"perfil/"+json[i].login+"'>"
						+ "			<img src='"+AVATAR_DIR+"small/"+json[i].avatar+"' />"
						+ "			<h4>"+json[i].login+"</h4>"
						+ "		</a>"
						+ "	</li>"
					}
				HTML += '</ul>'
				+ "</div>"
				+ "<div class='clearfix'></div>"
				+ "<div style='text-align:right;min-width:505px;padding-top:10px;'>"
				+ "		<button class='cancel'>Já vi o que queria por aqui. Obrigado :)</button>"
				+ "</div>";
				$("#overlay .content").html(HTML).css({"margin-left": -265});
				$("#overlay").fadeIn();
			}
		})
		
	})
})
