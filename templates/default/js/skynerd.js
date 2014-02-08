$(document).ready(function() {

	$(document).keyup(function(e) {
		if (e.keyCode === 27 && $("#overlay").is(":visible"))
			$("#overlay").fadeOut();
	});

	// Laaaaaaaaazy loading :)
	$("img.lazy").lazyload();

	//================================================
	//=======	Seção de post
	//================================================
	$(".info-lista div > a").click(function(e) {
		$(this).next().slideToggle("fast");
	});

	var last_loaded_list = ($.cookie("active_list")) ? $.cookie("active_list") : '';
	$(".info-lista div ul li a:not(.criarLista)").live('click', function(e) {
		e.stopPropagation();
		var ListID = $.trim($(this).attr("rel"));

		if (last_loaded_list == ListID) {
			$(".info-lista div ul").slideUp("fast");
			return;
		}

		$(".info-lista h3").html($(this).text());
		$(".info-lista div > a:first-child").html($(this).text());
		$(".info-lista div ul").slideUp("fast");

		var ListJSON = eval("(" + $(this).attr("ref") + ");");

		$(".lista .categorias ul").empty();
		if (ListJSON && (ListJSON.categories || ListJSON.social_networks)) {
			for (i in ListJSON.categories)
			$(".lista .categorias ul").append("<li><a href='javascript:void(0);'>" + ListJSON.categories[i].name + "</a>, </li>");
			for (i in ListJSON.social_networks)
			$(".lista .categorias ul").append("<li><a href='javascript:void(0);'>" + ListJSON.social_networks[i].name + "</a>, </li>");
		} else {
			if (ListID == 'all_posts')
				$(".lista .categorias ul").append("<li><a href='javascript:void(0);'>Todos os posts públicos da Skynerd</a></li>");
			else
				$(".lista .categorias ul").append("<li><a href='javascript:void(0);'>Todos os posts dos meus aliados</a></li>");
		}

		last_loaded_list = ListID;
		if ($(".lista .categorias ul li:last-child").get(0))
			$(".lista .categorias ul li:last-child").html($(".lista .categorias ul li:last-child").html().replace(',', ''));

		$.cookie("active_list", ListID, {
			path : "/",
			expires : 1
		});
		$.ajax({
			type : "POST",
			url : HOST + 'perfil/timeline',
			data : {
				list_id : ListID
			},
			beforeSend : function() {
				$("#posts-container").empty();
			},
			success : function(response) {
				$response = $(response);
				$response.find(".tooltip").tipsy({
					html : true,
					gravity : 'n'
				});
				$("#posts-container").html($response);
				RedefinirUltimoComentario();
			}
		});
	});

	if ($.cookie("active_list")) {
		var $el = $(".info-lista div a[rel=" + $.cookie("active_list") + "]");
		var JSON = "(" + $el.attr("ref") + ");";
		var ListJSON = eval(JSON);

		var text = $el.text();
		if (text != ""){

		$(".info-lista h3").html(text);

		if (ListJSON && (ListJSON.categories || ListJSON.social_networks)) {
			for (i in ListJSON.categories)
			$(".lista .categorias ul").append("<li><a href='javascript:void(0);'>" + ListJSON.categories[i].name + "</a>, </li>");
			for (i in ListJSON.social_networks)
			$(".lista .categorias ul").append("<li><a href='javascript:void(0);'>" + ListJSON.social_networks[i].name + "</a>, </li>");
		} else {
			var MSG;
			if ($el.attr("rel") == 'bookmarks')
				MSG = 'Meus posts favoritos';
			else if ($el.attr("rel") == 'all_posts')
				MSG = 'Todos os posts pÃºblicos da Skynerd';
			else if ($el.attr("rel") == '')
				MSG = 'Todos os posts dos meus aliados';
			$(".lista .categorias ul").append("<li><a href='javascript:void(0);'>" + MSG + "</a></li>");
		}
	} //fechar aqui

	} else {
		$(".lista .categorias ul").append("<li><a href='javascript:void(0);'>Todos os posts dos meus aliados</a></li>");
	}

	$("a.apagar.post").live('click', function(e) {
		e.preventDefault();
		var PostID = $(this).attr('rel');
		var HTML = '' + "<div style='background-color:#fff;box-shadow:0px 0px 3px #444 inset;padding:15px;width:500px;'>" + "		<h1>Tem certeza que quer apagar esse post?</h1>" + "		<span style='width:auto;padding:10px 0px;'>Que fique bem claro que isso não tem volta!</span>" + "		<div class='clearfix'></div>" + "</div>" + "<div class='clearfix'></div>" + "<div style='text-align:right;min-width:505px;padding-top:10px;' id='msg_server_response'>" + "		<button class='cancel'>Cancela isso vai</button>" + "		<button class='confirm-delete-post' rel='" + PostID + "'>Apaga logo antes que alguém veja!</button>" + "</div>";

		$("#overlay .content").html(HTML).css({
			"margin-left" : -250
		});
		$("#overlay").fadeIn();
	});

	$(".confirm-delete-post").live('click', function(e) {
		var PostID = $(this).attr('rel');
		$.ajax({
			type : "POST",
			url : HOST + "perfil/apagar-post",
			data : {
				posts_id : PostID
			},
			success : function(response) {
				if (response == 'SUCCESS') {
					$("#overlay").fadeOut();

					$("div.skynerd_post_" + PostID).remove();
					$(".skynerd_post_" + PostID + "_comments").remove();
					$(".skynerd_post_" + PostID + "_area_comentario").remove();
					$(".margem_post_" + PostID).remove();
					$("[rel=skynerd_post_" + PostID + "_comments]").remove();

					if (window.location.href.indexOf("post") > 1)
						window.location.href = HOST;
				} else {
					alert('Ocorreu um erro ao processar esse pedido.');
				}
			}
		});
	});

	$("div.post-content.nsfw a.show-nsfw-content").live('click', function(){
		$(this).parent().parent().hide().next().removeClass("hidden").hide().fadeIn();
	});
	
	$("div.post-content .spoiler-alert a").live("click", function(){
		$(this).parent().hide().next().fadeIn("slow");
	});
	
	

	$("a.apagar.comentario").live('click', function(e) {
		e.preventDefault();

		var CommentID = $(this).attr('rel');
		var $Element = $(this);

		$.ajax({
			type : "POST",
			url : HOST + "perfil/apagar-comentario",
			data : {
				comment_id : CommentID
			},
			success : function(response) {
				if (response == 'SUCCESS') {
					$Element.parent().remove();
					$(".replies_" + CommentID).remove();
				} else {
					alert('Ocorreu um erro ao processar esse pedido.');
				}
				RedefinirUltimoComentario();
			}
		});
	});

	$("#conteudo div.lista .categorias a").live('click', function() {
		$("div.post-container").show();
		$("div.post-container").not("." + $(this).text()).hide();
	});

	$("div.trigger-comentarios > a").live('click', function() {
		$(this).toggleClass('seta-direita');
		$(this).parent().next().find("> div").toggle();
	});

	$("#post_privacy_controller").click(function() {
		if ($(this).hasClass("aberto")) {
			$(this).removeClass("aberto").addClass("fechado");
			$("input[name=post_privacy]").val('1');
		} else {
			$(this).removeClass("fechado").addClass("aberto");
			$("input[name=post_privacy]").val('0');
		}
	});

	$(".opcaoPost li.facebook:not(.associar)").click(function() {
		if ($(this).hasClass("ativo")) {
			$(this).removeClass("ativo");
			$("#post_content input[name=post_to_facebook]").val('0');
		} else {
			$(this).addClass("ativo");
			$("#post_content input[name=post_to_facebook]").val('1');
		}
	});

	$(".opcaoPost li.twitter:not(.associar)").click(function() {
		if ($(this).hasClass("ativo")) {
			$(this).removeClass("ativo");
			$("#post_content input[name=post_to_twitter]").val('0');
		} else {
			$(this).addClass("ativo");
			$("#post_content input[name=post_to_twitter]").val('1');
		}
	});

	comment_placeholder = 'Comenta aí nerd!';
	$("div.comentar form textarea").autogrow({
		minHeight : 14,
		resizeParentElement : true
	});
	
	$("textarea[name=comment]").val(comment_placeholder).focus(function(e) {
		if ($(this).val() == comment_placeholder) {
			$(this).val('');
		}
	}).live("blur", function(e) {
		if ($(this).val() == "") {
			$(this).val(comment_placeholder);
		}
	}).live("keydown", function(e) {
		if (e.which == 13 && !e.shiftKey) {
			if ($(this).val().trim().length > 0) {
				e.preventDefault();
			}
		}
	}).live("keyup", function(e) {
		if (e.which == 13 && !e.shiftKey) {
			var $form_el = $(this).parent().parent();
			var $el = $(this);

			e.preventDefault();

			if ($.trim($(this).val()) != "") {
				
				$el.parent().find("span > img").show()
				
				$.ajax({
					type : $form_el.attr("method"),
					data : $form_el.serialize(),
					url : $form_el.attr("action"),
					beforeSend: function(){
						$el.attr("disabled", true);
					},
					success : function(response) {
						if(response.status == 0){
							alert(response.message);
						} else {
							var $comment = $("#newCommentTemplate").tmpl(response);
							var in_reply_to = $form_el.find("input[name=in_reply_to]").val();
							var post_id = $form_el.find("input[name=post_id]").val();
							
							$(".skynerd_post_" + post_id + "_comments").append($comment);
							$el.removeAttr("disabled").val(comment_placeholder);
							$el.parent().find("span > img").hide();
							
							
							
							RedefinirUltimoComentario();
						}
					}
				});
			}

		}
	});


	$("#frm-find-friends input[type=text]").focus(function() {
		var value = $.trim($(this).val());
		if (value == "Localizar aliados")
			$(this).val('');
	}).blur(function() {
		var value = $.trim($(this).val());
		if (value == "")
			$(this).val('Localizar aliados');
	}).bind('keyup', function(e) {
		var $el = $(this);
		var SearchText = $.trim($el.val());
		if (SearchText.length < 3) {
			$("#lateral .box.aliados form div.resultado-busca-aliados").fadeOut('moderate');
			return;
		}

		$.ajax({
			async : true,
			type : "POST",
			data : {
				username : SearchText
			},
			url : HOST + "perfil/procurar-aliados",
			success : function(response) {
				if (response.status == 1) {
					var $content = $("#findFriendList").tmpl(response);
					$("#lateral .box.aliados form div.resultado-busca-aliados").html($content).slideDown('fast');
					$("#lateral .box.aliados form").unbind("submit").attr("action", $content.find("li:first a").attr("href"));
				} else {
					$("#lateral .box.aliados form div.resultado-busca-aliados").fadeOut('moderate');
					$("#lateral .box.aliados form").bind("submit", function(e){
						e.preventDefault();
						return false;

					});
				}
			}
		})
	});

	//================================================
	//=======	REPLY DE COMENTARIOS
	//================================================
	$("li.reply a").live('click', function() {
		$(this).hide().parent().parent().parent().find(".reply-area-container").show().find("textarea").data("in-reply-to", $(this).attr('rel')).focus().autogrow({
			resizeParentElement : false,
			minHeight : 14
		});
	});

	$(".reply-area-container textarea").live("keyup keypress keydown", function(e) {
		keycode = (e.which) ? e.which : e.keyCode;
		if (keycode == 27) {
			$(this).data("in-reply-to", '').val(comment_placeholder).blur().parent().hide().parent().find("li.reply a").show();
		}
	});
	

	
	
	$("textarea[name=reply]").val(comment_placeholder).focus(function(e){
		if ($(this).val() == comment_placeholder){
			$(this).val('');
		}
	}).live("blur", function(e){
		if ($(this).val() == ""){
			$(this).val(comment_placeholder);
		}
	}).live("keydown", function(e){
		if (e.which == 13 && !e.shiftKey){
			if ($(this).val().trim().length > 0){
				e.preventDefault();
			}
		}
	}).live("keyup", function(e){
		if (e.which == 13 && !e.shiftKey) {
			if($.trim($(this).val()) == "")
				return;
			
			var $el = $(this);
			
			$.ajax({
				url: HOST+"comentar-post",
				type: "post",
				data: {
					in_reply_to: $el.data("in-reply-to"),
					post_id: $el.data("post-id"),
					comment: $el.val()
				},
				success: function(response){
					
					if(response.status == 1){
						var $reply = $("#newCommentTemplate").tmpl(response);
						var $comment = $(".comentario_" + $.trim($el.data("in-reply-to")));
						
						console.log($reply)
						
						if($comment.hasClass("cinza"))
							$reply.addClass("cinza");
						
						if($(".replies_" + $el.data("in-reply-to")).length > 0){
							$reply.insertAfter($(".replies_" + $el.data("in-reply-to") + ":last"));
						} else {
							$reply.insertAfter($comment);	
						}
						
						RedefinirUltimoComentario();
	
						var e = $.Event("keydown");
						e.which = 27;
						$el.trigger(e);
					} else {
						alert(response.message)
					}
				}
			});
		}
	});
	
	

	$("#conteudo .comentar:last-child").addClass("ultimo");

	//================================================
	//=======	Pedidos de amizade/Exclusão de amizade
	//================================================
	$(".request-friendship").live('click', function(e) {
		e.preventDefault();

		$element = $(this);
		user_id = $(this).attr('rel');
		$.ajax({
			url : HOST + 'perfil/adicionar-aliado',
			type : "POST",
			data : {
				uid : user_id
			},
			success : function(response) {
				if ($element.is("button")) {
					if (response == 'SUCCESS') {
						$element.html('pedido de amizade pendente');
					} else {
						$element.html('Oooops! Ocorreu um erro ao processar esse pedido.');
					}
					$element.removeClass('request-friendship').addClass('sem-acao');
				} else {
					$element.remove();
					if (response == 'SUCCESS') {
						$parent.append('<span>pedido de amizade pendente</span>');
					} else {
						$parent.append('<span>Oooops! Ocorreu um erro ao processar esse pedido.</span>');
					}
				}
			}
		});
	});

	$("#remove-friend").click(function() {
		$element = $(this);
		user_id = $(this).attr('rel');
		$.ajax({
			url : HOST + 'perfil/remover-aliado',
			type : "POST",
			data : {
				uid : user_id
			},
			success : function(response) {
				if (response == 'SUCCESS') {
					$element.html('Removido com sucesso!');
				} else {
					$element.html('Oooops! Ocorreu um erro ao processar esse pedido.');
				}
				$element.removeClass('request-friendship').addClass('sem-acao');
			}
		});
	});

	//================================================
	//=======	Botões para gerenciar pedidos de amizade
	//================================================
	$('.allow-friendship-request').live('click', function() {
		var friend_id = $(this).attr('ref');
		var $element = $(this);
		$.post(HOST + 'perfil/aprovar-pedido-de-amizade', {
			friend_id : friend_id
		}, function(response) {
			if (response == 'SUCCESS') {
				$element.parent().html("<h6>Agora vocês são aliados!</h6>");
			} else {
				$element.parent().html("<h6>Fuéin... Alguma coisa deu errado..</h6>");
			}
			reduzirNotificacoesaliados();
		});
	});

	$('.deny-friendship-request').live('click', function() {
		var friend_id = $(this).attr('ref');
		var $element = $(this);
		$.post(HOST + 'perfil/reprovar-pedido-de-amizade', {
			friend_id : friend_id
		}, function(response) {
			if (response == 'SUCCESS') {
				$element.parent().html("<h6>Ok, fica pra amanhã. Ou não.</h6>");
			} else {
				$element.parent().html("<h6>Fuéin... Alguma coisa deu errado..</h6>");
			}
			reduzirNotificacoesaliados();
		});
	});

	$('.block-friendship-request').live('click', function() {
		var friend_id = $(this).attr('ref');
		var $element = $(this);
		$.post(HOST + 'perfil/bloquear-pedido-de-amizade', {
			friend_id : friend_id
		}, function(response) {
			if (response == 'SUCCESS') {
				$element.parent().html("<h6></h6>");
			} else {
				$element.parent().html("<h6>Fuéin... Alguma coisa deu errado..</h6>");
			}
			reduzirNotificacoesaliados();
		});
	});

	//================================================
	//=======	PÁGINA DE CONFIGURAÇÕES
	//================================================
	$("form.configuracoes input").bind('change keypress', function() {
		var $el = $(this).parent().parent();
		if ($el.find("input[type=submit]").length == 0)
			$el = $el.parent().parent();

		$el.find("input[type=submit]").removeAttr("disabled");
	});

	$("div.pagina-configuracoes .coluna.listas fieldset .seletor-listas > a").click(function() {
		$(this).next().slideToggle("fast");
	});

	$("div.pagina-configuracoes .coluna.listas fieldset .seletor-listas ul li a").click(function() {
		var list_data = $.parseJSON($(this).attr("rel"));

		var scrollpane_api = $(".itens-lista").data('jsp');
		if (scrollpane_api)
			scrollpane_api.destroy();

		$("div.pagina-configuracoes .coluna.listas fieldset .seletor-listas > a").html($(this).html());
		$(".list-name").html($(this).html());
		$("div.pagina-configuracoes .coluna.listas fieldset .seletor-listas ul").slideUp("fast");
		$(".itens-lista").empty();

		for (i in list_data.categories)
		$(".itens-lista").append("<li>" + list_data.categories[i].name + "</li>");

		for (i in list_data.social_networks)
		$(".itens-lista").append("<li>" + list_data.social_networks[i].name + "</li>");

		$("#frm-remove-list input[name=list_id]").val(list_data.id);
		$(".itens-lista").jScrollPane()
	});
	$("div.pagina-configuracoes .coluna.listas fieldset .seletor-listas ul li:first-child a").trigger("click");

	$("div.pagina-configuracoes .coluna.listas fieldset .seletor-redes-sociais a").click(function() {
		$("#redes-sociais-nova-lista").slideToggle("fast");
		$("#categorias-nova-lista").fadeOut("fast");
	});

	$("#redes-sociais-nova-lista li button").click(function() {
		$("#redes-sociais-nova-lista").slideUp("fast");
	});

	$("div.pagina-configuracoes .coluna.listas fieldset .seletor-categorias a").click(function() {
		$("#categorias-nova-lista").slideToggle("fast");
		$("#redes-sociais-nova-lista").fadeOut("fast");
	});

	$("#categorias-nova-lista li a").click(function(e) {
		e.stopPropagation();

		$("#frm-add-list input[name^=new_list_categories]").remove();
		$("#categorias-nova-lista li input:checkbox:checked").each(function() {
			$("#frm-add-list").append("<input type='hidden' name='new_list_categories[]' value='" + $(this).val() + "' />");
		});

		var list_name = $("#frm-add-list input[name=list_title]").val();
		if (($("#frm-add-list input[name^=new_list_social_networks]").length > 0) || ($("#frm-add-list input[name^=new_list_categories]").length > 0) && (list_name != '' && list_name != 'Título')) {
			$("#frm-add-list input[type=submit]").removeAttr("disabled");
		} else {
			$("#frm-add-list input[type=submit]").attr("disabled", "disabled");
		}
	});

	$("#redes-sociais-nova-lista li a").click(function(e) {
		e.stopPropagation();

		$("#frm-add-list input[name^=new_list_social_networks]").remove();
		$("#redes-sociais-nova-lista li input:checkbox:checked").each(function() {
			$("#frm-add-list").append("<input type='hidden' name='new_list_social_networks[]' value='" + $(this).val() + "' />");
		});

		var list_name = $("#frm-add-list input[name=list_title]").val();
		if (($("#frm-add-list input[name^=new_list_social_networks]").length > 0) || ($("#frm-add-list input[name^=new_list_categories]").length > 0) && (list_name != '' && list_name != 'Título')) {
			$("#frm-add-list input[type=submit]").removeAttr("disabled");
		} else {
			$("#frm-add-list input[type=submit]").attr("disabled", "disabled");
		}
	});

	$("#categorias-nova-lista li button").click(function() {
		$("#categorias-nova-lista").slideUp("fast");
	});

	$("#frm-add-list input[name=list_title]").focus(function() {
		if ($.trim($(this).val()) == 'Título')
			$(this).val('');
	}).blur(function() {
		if ($.trim($(this).val()) == '')
			$(this).val('Título');
	}).bind("keypress keyup keydown", function() {
		var list_name = $(this).val();
		if (($("#frm-add-list input[name^=new_list_social_networks]").length > 0) || ($("#frm-add-list input[name^=new_list_categories]").length > 0) && (list_name != '' && list_name != 'Título')) {
			$("#frm-add-list input[type=submit]").removeAttr("disabled");
		} else {
			$("#frm-add-list input[type=submit]").attr("disabled", "disabled");
		}
	});

	//================================================
	//=======	Funções Genéricas
	//================================================
	function reduzirNotificacoesaliados() {
		var notify_count = parseInt($("#nav .avisos .aliados span").text());
		if (notify_count - 1 == 0) {
			$("#nav .avisos .aliados span").empty().parent().parent().removeClass("ativo");
			setTimeout(function() {
				$("#notificacoes-container").slideToggle("fast");
			}, 700);
		} else {
			var new_notify_value = notify_count - 1;
			$("#nav .avisos .aliados span").html(new_notify_value);
		}
	}


	$("#post-form input[name=title]").focus(function() {
		var value = $.trim($(this).val());
		if (value == "Título do post" || value == 'Você precisa definir um título para seu post, nerd!' || value == 'Já disse pra por um título no post.' || value == 'Posso ficar aqui o dia todo, mas você ainda vai ter que colocar um título no seu post.')
			$(this).val('');
	}).blur(function() {
		var value = $.trim($(this).val());
		if (value == "")
			$(this).val('Título do post');
	});

	$(".tooltip.east").tipsy({
		html : true,
		gravity : 'e'
	});
	$(".tooltip.south").tipsy({
		html : true,
		gravity : 's'
	});
	$(".tooltip.north").tipsy({
		html : true,
		gravity : 'n'
	});
	$(".tooltip:not(.south,.north,.east)").tipsy({
		html : true,
		gravity : 'w'
	});

	$("#nav .avisos .aliados").click(function(event) {
		if ($(this).hasClass("ativo") && $("#notificacoes-container").is(':visible')) {

			if ((!$(this).find("span.ativo")) || $(this).find('span').text() == '')
				$(this).removeClass("ativo");
			$("#notificacoes-container").fadeOut("fast");
			return;
		}

		if ($("#notificacoes-container").is(":hidden"))
			$("#notificacoes-container").fadeIn("fast");

		$("#amizades-pendentes").fadeIn('fast').siblings().hide();
		$("#nav .avisos .aliados").addClass('ativo').siblings().each(function() {
			if (! $(this).find("span.ativo") || $(this).find('span').text() == '')
				$(this).removeClass("ativo");
		});
	});

	$("#nav .avisos .notificacoes").click(function(event) {

		if ($(this).hasClass("ativo") && $("#notificacoes-container").is(':visible')) {
			if (! $(this).find("span.ativo") || $(this).find('span').text() == '') {
				$(this).removeClass("ativo");
			} else {
				$("#notificacoes ul li a.nao-lida").each(function() {
					$(this).trigger("click");
				});
			}
			$("#notificacoes-container").fadeOut("fast");
			return;
		}

		if ($("#notificacoes-container").is(":hidden"))
			$("#notificacoes-container").fadeIn("fast");

		$("#notificacoes").fadeIn('fast').siblings().hide();
		$("#nav .avisos .notificacoes").addClass('ativo').siblings().each(function() {
			if (! $(this).find("span.ativo") || $(this).find('span').text() == '')
				$(this).removeClass("ativo");
		});
	});

	$("#notificacoes ul li a.nao-lida").live("click", function(e) {
		$.post(HOST + "perfil/notificacoes/ler", {
			notify_id : $(this).attr("rel")
		})
	});

	$("#nav .avisos .mensagens").click(function(event) {
		if ($(this).hasClass("ativo") && $("#notificacoes-container").is(':visible')) {
			if (! $(this).find("span.ativo") || $(this).find('span').text() == '')
				$(this).removeClass("ativo");
			$("#notificacoes-container").fadeOut("fast");
			return;
		}

		if ($("#notificacoes-container").is(":hidden"))
			$("#notificacoes-container").fadeIn("fast");

		$("#mensagens").fadeIn('fast').siblings().hide();
		$("#nav .avisos .mensagens").addClass('ativo').siblings().each(function() {
			if (! $(this).find("span.ativo") || $(this).find('span').text() == '')
				$(this).removeClass("ativo");
		});

		if ($("#nav .avisos .mensagens span").text() != '') {
			var msgs_id = new Array();
			$("#mensagens li[ref='U']").each(function() {
				msgs_id.push($(this).attr('rel'))
			});

			$.post(HOST + "mensagens/marcar-como-lida", {
				id : msgs_id
			}, function() {
			});
		}
	});

	$(".mais-posts").click(function() {
		if ($(this).hasClass('inactive'))
			return;

		total_times_auto_loaded = 0;
		obterMaisPosts($(this));
	});

	$(".mais-posts-profile").click(function() {
		if ($(this).hasClass('inactive'))
			return;

		var profile = $(this).attr('rel');

		if (window.loading_posts == true)
			return;

		window.loading_posts = true;

		$(this).hide().next().show();
		$.post(HOST + 'perfil/posts-antigos', {
			profile : profile,
			max_id : $("div.post:first").data("post-id"),
			min_id : $("div.post:last").data("post-id")
		}, function(response) {
			if (response.length > 0) {
				window.loading_posts = false;
				$("#posts-container").append(response);
				$(".mais-posts-profile").show().next().hide();
			} else {
				$(".mais-posts-container").empty();
			}
		});
	});
	
	
	$(".load-more-allies").click(function(){
		if($(this).hasClass("inactive"))
			return;
		
		var $button = $(this);	
		var page = $button.data("page");
		var user = $button.data("login");
		
		$.ajax({
			url: HOST + "perfil/" + user + "/aliados/pagina",
			data: {
				p: page
			},
			beforeSend: function(){
				$button.addClass("inactive").hide().next().show();
			},
			success: function(response){
				$button.show().next().hide();
				if(response.allies){
					var $allies = $("#friendListPage").tmpl(response);
					$(".lista-de-aliados.aliados").append($allies);
					$button.data("page", ++page).removeClass("inactive");
				} else {
					$button.remove();
				}
			}
		})
	});
	
	function obterMaisPosts($button_instance) {
		if (window.loading_posts == true)
			return;

		window.loading_posts = true;

		$button_instance.hide().next().show();

		$.post(HOST + 'perfil/timeline/posts-antigos', {
			list_id : last_loaded_list,
			max_id : $("div.post:first").data("post-id"),
			min_id : $("div.post:last").data("post-id")
		}, function(response) {
			if (response.length > 0) {
				window.loading_posts = false;

				var $response = $(response);
				$response.find("img.lazy").lazyload();
				$("#posts-container").append($response);
				$(".mais-posts").show().next().hide();
			} else {
				$(".mais-posts-container").empty();
			}
		});
	}

	function obterNotificacoes() {
		if ($("#notificacoes-container").is(":visible"))
			return;

		$.ajax({
			type : "POST",
			url : HOST + 'perfil/notificacoes',
			success : function(response) {
				var o = eval(response);
				if (!o)
					return;

				var qtde_notificacoes;
				if (o.friends.length > 0) {
					$("#amizades-pendentes > ul").empty();

					$("#nav .avisos .aliados").unbind('click').click(function(event) {
						$("#notificacoes-container").slideToggle("fast");
						setTimeout(function() {
							$("#amizades-pendentes").show().siblings().hide();
						}, 301);
					}).addClass("ativo").find("span").addClass('ativo').html(o.friends.length);

					$.each(o.friends, function(idx, value) {
						qtde_notificacoes += 1;
						$("#amizades-pendentes > ul").append("<li><img src='" + AVATAR_DIR + "square/" + value.avatar + "' width='44' height='75' /><div><h5><a target='_blank' href='" + HOST + "perfil/" + value.login + "'>" + value.login + "</a></h5><h6>" + value.name + "</h6><div><button class='allow-friendship-request' ref='" + value.id + "'>Confirmar</button><button class='deny-friendship-request' ref='" + value.id + "'>Agora não</button><button class='block-friendship-request' ref='" + value.id + "'>Bloquear</button></div></div></li>")
					});
				}

				if (o.notifications.length > 0) {
					$("#notificacoes > ul").removeClass("sem-novas").empty();

					var notificacoes = 0;
					$.each(o.notifications, function(idx, value) {
						var classname = '';
						if (value.readed == 0) {
							classname = 'nao-lida';
							qtde_notificacoes += 1;
							notificacoes += 1;
						}
						var HTML = "<li class='" + value.classname + "'>" + "	<a href='" + HOST + value.link + "' class='" + classname + "' rel='" + value.id + "'>";
						if (value.image != '')
							HTML += "<img src='" + value.image + "' />";
						HTML += "		<span>" + value.description + " <span>" + value.when + "</span></span>" + "	</a>" + "</li>";
						$("#notificacoes > ul").append(HTML);
					});
					$("#notificacoes > ul").append('<div class="clearfix"></div>');
					if (notificacoes > 0)
						$("#nav .avisos .notificacoes").addClass('ativo').find("span").addClass('ativo').html(notificacoes);
				}

				if (o.messages.length > 0) {
					var messages = 0;
					$("#mensagens > ul").removeClass("sem-novas").empty();
					$.each(o.messages, function(idx, value) {
						if (value.status == 'U') {
							messages += 1;
							qtde_notificacoes += 1;
						}

						var REL = '({\"uid\": \"' + value.user_id + '\", \"avatar\": \"' + value.avatar + '\", \"username\": \"' + value.login + '\"});';

						var HTML = "" + "<li ref='" + value.status + "' rel='" + value.id + "'>" + "		<a href='" + HOST + '/perfil/' + value.login + "'>" + "			<img src='" + AVATAR_DIR + 'square/' + value.avatar + "' />" + "		</a>" + "		<span><b>" + value.login + "</b></span>" + "		<span><h6>" + value.title + "</h6></span>" + "		<span>" + value.message + "</span>" + "		<span class='date'>" + value.date + "</span>" + "		<div class='actions'>" + "			<a href='" + HOST + "apagar-mensagem/" + value.id + "' class='trash-it'>apagar</a>" + "			<a href='javascript:void(0);' rel='" + REL + "' class='send-dm' ref='answer'>responder</a>" + "		</div>" + "		<div class='clearfix'></div>" + "</li>";
						$("#mensagens > ul").append(HTML);
					});
					$("#notificacoes > ul").append('<div class="clearfix"></div>');
					if (messages > 0)
						$("#nav .avisos .mensagens").addClass('ativo').find("span").addClass('ativo').html(messages);
				}

				if (qtde_notificacoes > 0)
					$("title").html("Skynerd (" + qtde_notificacoes.toString() + ")");
			}
		});
	}


	$(".cut-my-avatar").live('click', function() {
		var image_width = $("#avatar-crop").width();
		var image_height = $("#avatar-crop").height();

		$("#avatar-crop").Jcrop({
			setSelect : [image_width / 2 - 139, image_height / 2 - 233, image_width / 2 + 139, image_height / 2 + 233]
		});

		$('.crop-it').trigger('click');
	});

	$("#post-form button").click(function(e) {
		e.preventDefault();
		e.stopPropagation();

		if ($.trim($("#tituloPost").val()) == 'Título do post')
			$("#tituloPost").addClass("has_error").val('Você precisa definir um título para seu post, nerd!');
		else if ($.trim($("#tituloPost").val()) == 'Você precisa definir um título para seu post, nerd!')
			$("#tituloPost").addClass("has_error").val('Já disse pra por um título no post.');
		else if ($.trim($("#tituloPost").val()) == 'Já disse pra por um título no post.')
			$("#tituloPost").addClass("has_error").val('Posso ficar aqui o dia todo, mas você ainda vai ter que colocar um título no seu post.');
		else if ($.trim($("#tituloPost").val()) == 'Posso ficar aqui o dia todo, mas você ainda vai ter que colocar um título no seu post.')
			$("#tituloPost").addClass("has_error");
		else
			$("#tituloPost").removeClass("has_error");

		if ($.trim($("input[name=categories]").val()) == "")
			$("#post-form h4.category").addClass("has_error").html('Você deve inserir ao menos uma categoria no seu post');
		else
			$("#post-form h4.category").removeClass("has_error").html('CATEGORIZE SEU POST, NERD!');

		if ($.trim($("#conteudo .box-compartilhar form textarea").val()) == '') {
			$("#conteudo .box-compartilhar form textarea").addClass("has_error");
		} else {
			$("#conteudo .box-compartilhar form textarea").removeClass("has_error");
		}

		var tagged_users =  $("#conteudo .box-compartilhar form textarea").val().match(/(^|\W)@\w+/g);
		if(tagged_users instanceof Array){
			for(var i=0; i<tagged_users.length; i++){
				tagged_users[i] = tagged_users[i].replace(/^[^@]/, "");
			}
		}
		
		if (tagged_users && tagged_users.length > 10) {
			$("#post-form h4.error_report").html("IH RAPAZ, seu post passa do limite de até 10 pessoas que você pode marcar. Corrige isso aí!").addClass("has_error");
		} else {
			$("#post-form h4.error_report").removeClass("has_error").empty();
		}

		if ($(".has_error").length > 0) {
			return;
		}

		$.ajax({
			beforeSend : function() {
				$("#post-form button").attr("disabled", true);
				var e = $.Event("keydown");
				e.which = 13;
				e.keyCode = 13;
				$("#post-form .redactor_editor").trigger(e)
			},
			type : $("#post-form").attr("method"),
			url : $("#post-form").attr("action"),
			data : $("#post-form").serialize(),
			success : function(post_data) {

				$("#post-form button").removeAttr("disabled");

				$("#posts-container").prepend(post_data);

				$("#post-form input[name=title]").val('Título do post').removeClass('has_error');
				$("#post-form textarea").val('').removeClass('has_error');
				$("#post-form .redactor_editor").empty();
				$("#post_content h4").removeClass("has_error").html('CATEGORIZE SEU POST, NERD!')
				$(".container-categorias input:checkbox").removeAttr("checked");

				$("ul.tagit li.tagit-choice").each(function(){
					$(this).find("a").trigger("click");
				});

			}
		})
	});

	$("#new_avatar_fallback").show();
	$("#avatar, #avatar-loader").dragndropuploader({
		upload_url : HOST + "perfil/configuracoes/trocar-avatar",
		upload_request_filename : 'new_avatar',
		allowed_extensions : ['jpg', 'jpeg', 'png'],
		proccess_server_response_handler : function(response) {
			var object = eval(response);
			if (!object || object.status == false)
				return false;

			mostrarOverlayParaCortarAvatar(object);
			$("#avatar-loader").find("img").hide();
			$("#avatar-loader").find("span").show();
		},
		invalid_extension_error_handler : function(filename) {
			alert("Formato de arquivo inválido!");
		},
		before_upload_handler : function() {
			$("#avatar-loader").show().find("img").show();
			$("#avatar-loader").find("span").hide();
		},
		upload_finished_handler : function() {
			$("#avatar-loader").fadeOut("moderate");
		},
		fallback : function() {
			$("#avatar-loader > span").html("Trocar avatar")
		}
	});

	$('#avatar').mouseenter(function() {
		$("#avatar-loader").show().mouseleave(function() {
			if ($("#avatar-loader").find("img").is(":hidden"))
				$("#avatar-loader").hide();
		});
	}).mouseleave(function() {

	});

	$('#avatar-loader .take-picture').click(function() {
		var htmlStr = "" + "<div id='camera-container'><div style='background-color:#f6f6f6;box-shadow:0px 0px 3px #444 inset;height:505px;width:665px' id='camera'></div><span class='ticker'></span></div>" + "<div class='clearfix'></div>" + "<div style='text-align:right;min-width:505px;padding-top:10px;' >" + "		<button class='cancel'>Vou me arrumar e já volto</button>" + "		<button class='take-picture-instantly'>Manda ver!</button>" + "		<button class='take-picture-delayed'>Quer fazer pose, tira a foto já já!</button>" + "</div>";

		$("#overlay .content").html(htmlStr).css({
			"margin-left" : "-332px"
		})
		$("#overlay").fadeIn();

		setTimeout(function() {
			$("#camera").webcam({
				width : 665,
				height : 505,
				mode : "save",
				swffile : HOST + "/templates/default/swf/jscam.swf",
				onTick : function(remain) {
					if (remain == "0") {
						$("#camera-container .ticker").html("LAMBDA!");
					} else {
						$("#camera-container .ticker").html(remain);
					}

				},
				onSave : function(data) {
					confirm.log(data)
				},
				onCapture : function() {
					webcam.save(HOST + "perfil/configuracoes/trocar-avatar/tirar-foto");

					$("#webcam-flash").css("display", "block");
					$("#webcam-flash").fadeOut("fast", function() {
						$("#webcam-flash").css("opacity", 1);
					});

					$("#camera-container .ticker").empty();
				},
				debug : function() {

				},
				onLoad : function() {
					$("#camera-container .ticker").empty();
				}
			});
		}, 400);
	});

	$(".take-picture-instantly").live('click', function() {
		webcam.capture(0);
	});

	$(".take-picture-delayed").live('click', function() {
		webcam.capture(3);
	});

	$("#areaPostagem").redactor({
		plugins : ['youtube_video', 'convert_video_urls', 'spoiler'],
		buttons : ['bold', 'italic', 'deleted', 'underline', '|', 'unorderedlist', 'orderedlist', 'outdent', 'indent', '|', 'image', 'link', '|', 'fontcolor', 'alignment', '|', 'horizontalrule', '|'],
		lang : 'pt-br',
		imageUpload : HOST + "perfil/compartilhar/anexar-arquivos",
		imageUploadErrorCallback : function(obj, json) {
			alert("Ocorreu um erro ao adicionar este arquivo:\n" + json.message)
		}
	});

	$('.crop-it').live('click', function() {
		$.ajax({
			type : "POST",
			url : HOST + 'perfil/configuracoes/concluir-troca-de-avatar',
			data : {
				x : $('input[name=x]').val(),
				y : $('input[name=y]').val(),
				x2 : $('input[name=x2]').val(),
				y2 : $('input[name=y2]').val(),
				w : $('input[name=w]').val(),
				h : $('input[name=h]').val()
			},
			success : function(response) {
				$("#avatar").attr('src', AVATAR_DIR + "small/" + response)
				$("#overlay .content").empty().parent().fadeOut();
			}
		})
	});

	$('.cancel').live('click', function() {
		$("#overlay").fadeOut();
		setTimeout(function() {
			$("#overlay .content").empty();
		}, 700)
	});

	list_name_generated_by_system = true;
	$("#overlay input[name^=new_list_social_networks], #overlay input[name^=new_list_categories]").live("click", function() {
		if (list_name_generated_by_system == true || $.trim($('#overlay input[name=new_list_name]').val()) == '') {
			var suggestion = new Array();
			$("#overlay input:checkbox:checked").each(function() {
				suggestion.push($(this).attr("ref"));
			});
			$("#overlay input[name=new_list_name]").val(suggestion.join(", "))
			list_name_generated_by_system = true;
		}
	});

	$("#overlay input[name=new_list_name]").live('keyup keypress keydown', function() {
		if ($(this).val() != '')
			list_name_generated_by_system = false;
		else
			list_name_generated_by_system = true;
	})

	$(".create-list").live("click", function() {
		var has_errors = false;
		var list_name = $.trim($("input[name=new_list_name]").val());

		var list_socialnetworks = new Array();
		$("input:checkbox[name^=new_list_social_networks]:checked").each(function() {
			list_socialnetworks.push($(this).val());
		});

		var list_categories = new Array();
		$("input:checkbox[name^=new_list_categories]:checked").each(function() {
			list_categories.push($(this).val());
		});

		if (list_name == '') {
			has_errors = true;
			$("input[name=new_list_name]").addClass("has_error").val("É importante que sua lista tenha um título");
		}

		if (list_categories.length == 0 && list_socialnetworks.length == 0) {
			$(".new_list_error_alert").html("Sua lista deve seguir pelo menos<br/>uma categoria ou rede social");
			has_errors = true;
		}

		if (has_errors == true)
			return;

		$(".new_list_error_alert").empty();
		$("input[name=new_list_name]").removeClass("has_error");

		$.ajax({
			type : "POST",
			url : HOST + "perfil/configuracoes/listas/salvar",
			data : {
				"method" : "add_list",
				"list_title" : list_name,
				"new_list_social_networks" : list_socialnetworks,
				"new_list_categories" : list_categories
			},
			success : function(response) {
				var JSON = $.parseJSON(response);
				var $element = $("<li><a href='javascript:void(0);' rel='" + JSON.id + "' ref='" + response + "'>" + list_name + "</a></li>");
				$element.insertBefore("#create-list");
				$element.find("a").trigger("click");
				$("#overlay").fadeOut('slow');
			}
		});

	});

	

	$(".obter-comentarios").live('click', function() {
		var $el = $(this);
		$el.hide().next().fadeIn();

		var post_id = $(this).attr("ref");
		$.ajax({
			type : "POST",
			url : HOST + "comentarios-do-post",
			data : {
				post_id : post_id
			},
			success : function(server_response) {
				$el.next().fadeOut();

				var i = 0, idx = 0;

				var comments = $.parseJSON(server_response);

				var commentHTML = '';
				for (i in comments) {
					var c = comments[i];
					var classname = (idx % 2 == 0) ? 'cinza' : '';
					commentHTML += '' + '<div class="comentario comentario_' + c.id + ' ' + classname + '">' + '		<span class="numeroComentario">' + (++idx).toString() + '</span>' + '		<span class="dados">' + '			<span><a href="' + HOST + 'perfil/' + c.user.login + '">' + c.user.login + '</a> diz:</span>' + '		</span>' + '		<div class="data">' + c.date + '</div>' + '		<div class="textoUsuario"><p>' + c.comment + '</p></div>' + '		<ul class="dados" rel="' + c.id + '">' + '			<li title="Megaboga!" class="tooltip north bt-positivar ' + ((c.my_rating == '1') ? 'meu-voto' : '').toString() + '"></li>' + '			<li class="res-positivar">' + c.rating.megaboga + '</li>' + '			<li title="Whatever..." class="tooltip north bt-negativar ' + ((c.my_rating == '-1') ? 'meu-voto' : '').toString() + '"></li>' + '			<li class="res-negativar">' + c.rating.whatever + '</li>' + '			<li class="reply"><a href="javascript:void(0);" rel="' + c.id + '">responder</a></li>' + '		</ul>' + '		<div class="infoComentarista">' + '			<a href="' + HOST + 'perfil/' + c.user.login + '">' + '				<img src="' + AVATAR_DIR + 'square/' + c.user.avatar + '" alt="" />' + '			</a>' + '			<ul>' + '				<li class="nivel">Nv ' + c.user.experience.current_level + '</li>' + '				<li class="exp">Exp ' + c.user.experience.exp + '/' + c.user.experience.exp_to_next_level + '</li>' + '				<li class="hp">HP ' + c.user.experience.hp + '</li>';
					if (c.user.badges.length > 0) {
						commentHTML += '<li class="badges">';
						for ( x = 0; x < c.user.badges.length; x++) {
							commentHTML += '<img src="' + MEDIA_DIR + 'images/badges/' + c.user.badges[x].icon_url + '" />';
						}
						commentHTML += '</li>';
					}
					commentHTML += '</ul>'
					+ '</div>'
					+ '<div class="reply-area-container">'
					+ '		<textarea name="reply" data-post-id="'+post_id+'"></textarea>'
					+ '	</div>'
					+ '</div>';

					if (c.replies.length > 0) {
						var x = 0;
						for (x in c.replies) {
							var r = c.replies[x];

							commentHTML += '' + '<div class="comentario resposta replies_' + c.id + ' ' + classname + '">' + '		<div class="margem-resposta"></div>' + '		<span class="dados">' + '			<span><a href="' + HOST + 'perfil/' + r.user.login + '">' + r.user.login + '</a> responde:</span>' + '		</span>' + '		<div class="data">' + r.date + '</div>' + '		<div class="textoUsuario"><p>' + r.comment + '</p></div>' + '		<div class="infoComentarista">' + '			<a href="' + HOST + 'perfil/' + r.user.login + '">' + '				<img src="' + AVATAR_DIR + 'square/' + r.user.avatar + '" />' + '			</a>' + '			<ul>' + '				<li class="nivel">Nv ' + r.user.experience.current_level + '</li>' + '				<li class="exp">Exp ' + r.user.experience.exp + '/' + r.user.experience.exp_to_next_level + '</li>' + '				<li class="hp">HP ' + r.user.experience.hp + '</li>';
							if (r.user.badges.length > 0) {
								commentHTML += '<li class="badges">';
								for ( x = 0; x < r.user.badges.length; x++) {
									commentHTML += '<img src="' + MEDIA_DIR + 'images/badges/' + r.user.badges[x].icon_url + '" />';
								}
								commentHTML += '</li>';
							}
							commentHTML += '</ul>' + '		</div>' + '</div>';
						}
					}
					commentHTML += '<div class="margem-comentario"></div>';
				}

				$(".skynerd_post_" + post_id + "_comments").html(commentHTML);
				$(".skynerd_post_" + post_id + "_comments").find(".margem-comentario:last").remove();
				$(".skynerd_post_" + post_id + "_comments").find(".comentario:last").addClass("ultimo");
			}
		});
	});

	var maior = 0;
	$(".lista-de-badges li").each(function() {
		maior = Math.max(maior, $(this).height())
	}).height(maior);

	$(".send-dm").live('click', function() {
		var data = eval($(this).attr("rel"));
		var title = ($(this).attr("ref") == 'answer') ? 'Responder a mensagem de ' : 'Enviar nova mensagem para ';

		var HTML = '' + "<div style='background-color:#fff;box-shadow:0px 0px 3px #444 inset;padding:15px;width:500px;'>" + "		<h1>" + title + " " + data.username + "</h1>" + "		<img src='" + AVATAR_DIR + "small/" + data.avatar + "' style='margin-top:10px;margin-right:10px;border:3px solid #333;float:left;' />" + "		<form style='float:left;' id='frm-send-message'>" + "			<input type='hidden' name='send_message_to_uid' value='" + data.uid + "' />" + "			<div style='margin:10px 0px 10px 0px;'>" + "				<label for='message_title' style='font-family:SanskritHelvetica;text-transform:none;font-size:13px;'>Título</label>" + "				<input type='text' id='message_title' name='message_title' style='width:325px' />" + "			</div>" + "			<div>" + "				<label for='message_content' style='font-family:SanskritHelvetica;text-transform:none;font-size:13px;'>Mensagem</label>" + "			</div>" + "			<div>" + "				<textarea id='message_content' name='message_content' style='resize:none;width:365px;height:135px;border:1px solid #454545'></textarea>" + "			</div>" + "		</form>" + "		<div class='clearfix'></div>" + "</div>" + "<div class='clearfix'></div>" + "<div style='text-align:right;min-width:505px;padding-top:10px;' id='msg_server_response'>" + "		<button class='cancel'>Cancela isso vai</button>" + "		<button class='send-message'>Manda a mensagem, mas que fique em segredo hein!</button>" + "</div>";

		$("#overlay .content").html(HTML).css({
			"margin-left" : -250
		});
		$("#overlay").fadeIn();
	});

	$(".send-message").live('click', function() {
		$.ajax({
			type : "POST",
			url : HOST + 'enviar-mensagem',
			data : $('#frm-send-message').serialize(),
			success : function(server_response) {
				server_response = eval(server_response);
				if (server_response.status == 'SUCCESS') {
					$('#msg_server_response').html('<p>Mensagem enviada com sucesso!</p>')
				} else {
					$('#msg_server_response').html('<p>Ops, algo deu errado ao enviar essa mensagem...</p>')
				}
				setTimeout(function() {
					$("#overlay").fadeOut();
				}, 2000)
			}
		})
	});

	$("#overlay .background").click(function() {
		$("#overlay").fadeOut();
	});

	RedefinirUltimoComentario();

	var total_times_auto_loaded = 0;
	if ($(".box-compartilhar").get(0)) {
		$(window).scroll(function() {

			TravarBarraInteracao();

			if (total_times_auto_loaded == 5)
				return;

			var amount_scrolled = $(window).scrollTop() / $("#posts-container").height();
			if (amount_scrolled > 0.85) {
				total_times_auto_loaded += 1;
				obterMaisPosts($(".mais-posts"));
			}
		});
	}

	if ($(".profile-tl").get(0)) {
		$(window).scroll(function() {

			TravarBarraInteracao();

			if($("#posts-container").get(0)){
				if (total_times_auto_loaded == 5)
					return;
				
				var amount_scrolled = $(window).scrollTop() / $("#posts-container").height();
				if (amount_scrolled > 0.85) {
					total_times_auto_loaded += 1;
					$(".mais-posts-profile").click();
				}
			}
			
			if($(".lista-de-aliados").get(0)){
				var amount_scrolled = $(window).scrollTop() / $(".lista-de-aliados").height();
				if (amount_scrolled > 0.75) {
					total_times_auto_loaded += 1;
					$(".load-more-allies").click();
				}
			}
			
		});
	}

	if (window.location.href.indexOf("post") > 1) {
		$(window).scroll(function() {
			TravarBarraInteracao();
		});
	}

	obterNotificacoes();
	setInterval(function() {
		obterNotificacoes();
	}, 180000);

});

function TravarBarraInteracao() {
	if (! $("#nav").hasClass("fixed") && $(window).scrollTop() > 175) {
		$("#nav").addClass("fixed");
	} else if ($("#nav").hasClass("fixed") && $(window).scrollTop() < 175) {
		$("#nav").removeClass("fixed");
	}
}

function RedefinirUltimoComentario() {
	$("#conteudo div.comentarios").each(function() {
		var $comentarios = $(this);
		$comentarios.each(function() {
			var $prev = $(this).find("div.comentario:first").prev();
			if ($prev.hasClass("margem-comentario"))
				$prev.remove();

			$(this).find("div.comentario.ultimo").removeClass('ultimo');
			var $next = $(this).find("div.comentario:last").addClass("ultimo").next();
			if ($next.hasClass("margem-comentario"))
				$next.remove();
				
			var current_comment=0;
			$(this).find("div.comentario:not(.resposta)").each(function(){
				$(this).find("span.numeroComentario").html(++current_comment);
			});
		});
	});
}

function mostrarOverlayParaCortarAvatar(object) {
	var htmlStr = $("#overlayAvatarCrop").tmpl(object);

	$("#overlay .content").html(htmlStr).css({
		"margin-left" : (((object.width < 505) ? 505 : object.width) - 20) / -2
	}).find("div:last-child");
	
	$("#overlay").fadeIn();
	setTimeout(function() {
		$("#avatar-crop").Jcrop({
			setSelect : [0, 0, 278, 466],
			onSelect : function(c) {
				$('input[name=x]').val(c.x);
				$('input[name=y]').val(c.y);
				$('input[name=x2]').val(c.x2);
				$('input[name=y2]').val(c.y2);
				$('input[name=w]').val(c.w);
				$('input[name=h]').val(c.h);
			},
			onChange : function(c) {
				$('input[name=x]').val(c.x);
				$('input[name=y]').val(c.y);
				$('input[name=x2]').val(c.x2);
				$('input[name=y2]').val(c.y2);
				$('input[name=w]').val(c.w);
				$('input[name=h]').val(c.h);
			},
			aspectRatio : 0.59
		});
	}, 700);
}

// parseUri 1.2.2
// (c) Steven Levithan <stevenlevithan.com>
// MIT License

function parseUri(str) {
	var o = parseUri.options, m = o.parser[o.strictMode ? "strict" : "loose"].exec(str), uri = {}, i = 14;

	while (i--)
	uri[o.key[i]] = m[i] || "";

	uri[o.q.name] = {};
	uri[o.key[12]].replace(o.q.parser, function($0, $1, $2) {
		if ($1)
			uri[o.q.name][$1] = $2;
	});

	return uri;
};

parseUri.options = {
	strictMode : false,
	key : ["source", "protocol", "authority", "userInfo", "user", "password", "host", "port", "relative", "path", "directory", "file", "query", "anchor"],
	q : {
		name : "queryKey",
		parser : /(?:^|&)([^&=]*)=?([^&]*)/g
	},
	parser : {
		strict : /^(?:([^:\/?#]+):)?(?:\/\/((?:(([^:@]*)(?::([^:@]*))?)?@)?([^:\/?#]*)(?::(\d*))?))?((((?:[^?#\/]*\/)*)([^?#]*))(?:\?([^#]*))?(?:#(.*))?)/,
		loose : /^(?:(?![^:@]+:[^:@\/]*@)([^:\/?#.]+):)?(?:\/\/)?((?:(([^:@]*)(?::([^:@]*))?)?@)?([^:\/?#]*)(?::(\d*))?)(((\/(?:[^?#](?![^?#\/]*\.[^?#\/.]+(?:[?#]|$)))*\/?)?([^?#\/]*))(?:\?([^#]*))?(?:#(.*))?)/
	}
}; 