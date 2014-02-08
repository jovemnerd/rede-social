(function($){
	$(document).ready(function(){
		
		if(SETTINGS_PAGE_STATUS_MESSAGE != ''){
			eval(SETTINGS_PAGE_STATUS_MESSAGE + "();");
		}
		
		$("form").submit(function(e){
			e.preventDefault();
		});
		
		$("textarea, input[type=text]").each(function(){
			$(this).data("original-content", $(this).val())
		});
		
		$("#save_profile_info_access").click(function(){
			var has_changed = false;
			$("#profile_data_form input[type=text], #profile_data_form textarea").each(function(){
				if($(this).val() != $(this).data("original-content"))
					has_changed = true;
			});
			
			if(has_changed){
				$.ajax({
					url: HOST+"perfil/configuracoes/salvar",
					type: "POST",
					data: $("#profile_data_form").serialize(),
					success: function(response){
						var o = $.parseJSON(response);
						if(o.status == true) GenericSuccessOverlay();
						else GenericErrorOverlay();
						
						has_changed = false;
					}
				});	
			}
			
			if($.trim($("#current_password").val()) != "" && $.trim($("#new_password").val()) != "" &&  ($.trim($("#new_password").val()) == $.trim($("#new_password_confirm").val()))){
				$.ajax({
					url: HOST+"perfil/configuracoes/acesso/salvar",
					type: "POST",
					data: $("#profile_acess_data_form").serialize(),
					success: function(response){
						var o = $.parseJSON(response);
						if(o.status == true) PasswordChanged();
						else PasswordNotChanged();
					}
				})
			}
			
		});
		
		$("#list_title").focus(function(){
			if($.trim($(this).val()) == "Título")
				$(this).val("");
		}).blur(function(){
			if($.trim($(this).val()) == "")
				$(this).val("Título");
		});
		
		$("#create_new_list").click(function(){
			var errors = new Array();
			var list_name = $.trim($("#list_title").val());
			$(".list_error").empty();
			
			if(list_name == '' || list_name == 'Título'){
				$("#list_title").addClass("error");
				errors.push("Sua lista deve conter um título");
			} else {
				$("#list_title").removeClass("error");
			}
			
			var categories = new Array();
			$(".new_list_categories:checked").each(function(){
				categories.push($(this).val());
			});
			
			var social_networks = new Array();
			$(".new_list_social_networks:checked").each(function(){
				social_networks.push($(this).val());
			});
			
			if(categories.length==0 && social_networks.length==0)
				errors.push("A lista deve seguir ao menos uma categoria ou rede social");
			
			if(errors.length > 0){
				for(i in errors)
					$(".list_error").append("-" + errors[i] + "<br />");
					
				return;
			}
			
			$.ajax({
				url: HOST+"perfil/configuracoes/listas/salvar",
				type: "POST",
				data: {
					new_list_categories: categories,
					new_list_social_networks: social_networks,
					list_title: list_name,
					method: 'add_list'
				},
				success: function(response){
					var o = $.parseJSON(response);
					if(o.status == true) ListSuccessfullyCreated();
					else GenericErrorOverlay();
					
					$("#list_title").val('Título');
					$(".new_list_categories").removeAttr("checked");
					$(".new_list_social_networks").removeAttr("checked");
				}
			});
		});
		
		$("#save_privacy").click(function(){
			$.ajax({
				url: HOST+"perfil/configuracoes/privacidade/salvar",
				type: "POST",
				data: $("#frm_privacy").serialize(),
				success: function(response){
					var o = $.parseJSON(response);
					if(o.status == true) GenericSuccessOverlay();
					else GenericErrorOverlay();
					
					has_changed = false;
				}
			});	
		});
		
		$("#save_gamertags").click(function(){
			var has_changed = false;
			$("#frm_gamertags input[type=text], #frm_gamertags textarea").each(function(){
				if($(this).val() != $(this).data("original-content"))
					has_changed = true;
			});
			
			if(has_changed){
				$.ajax({
					url: HOST+"perfil/configuracoes/gamer-tags/salvar",
					type: "POST",
					data: $("#frm_gamertags").serialize(),
					success: function(response){
						var o = $.parseJSON(response);
						if(o.status == true) GenericSuccessOverlay();
						else GenericErrorOverlay();
						
						has_changed = false;
					}
				});	
			}
		});
		
		$("#save_notification_settings").click(function(){
			$.ajax({
				url: HOST+"perfil/configuracoes/notificacoes/salvar",
				type: "POST",
				data: $("#frm_notification").serialize(),
				success: function(response){
					var o = $.parseJSON(response);
					if(o.status == true) GenericSuccessOverlay();
					else GenericErrorOverlay();
				}
			});	
		});
		
		$("#save_account_options").click(function(){
			$.ajax({
				type: "POST",
				url: HOST + "perfil/configuracoes/opcoes/salvar",
				data: {
					show_nsfw: ($("#show_nsfw").attr("checked") == "checked") ? 1 : 0
				},
				success: function(response){
					if(response.status == 1){
						GenericSuccessOverlay();
					} else {
						GenericErrorOverlay();
					}
				}
			})
		})
		
		$(".toggle-content").click(function(){
			$("#" + $(this).data("target")).slideDown("fast", function(){
				$(this).find("ul").jScrollPane({
					verticalDragMaxHeight: 40,
					verticalDragMinHeight: 40
				});
				$(this).siblings(".user-lists-container").hide();
			}).siblings(".hidden").slideUp("fast", function(){
				var jScrollPane = $(this).find("ul").data('jsp');
				if(jScrollPane)
					jScrollPane.destroy();
			});
		});
		
		$("div.column .hidden button").click(function(){
			$(this).parent().animate({
				width: 'hide'
			}, 200, function(){
				$(".user-lists-container").show();
			});
		});
		
		$("div.pagina-configuracoes div.column div.box.lists > div div.column a.list-name").click(function(){
			$(this).addClass("open").next().next().slideToggle("fast");
		})
		
		$("div.pagina-configuracoes div.column div.box.lists > div div.column ul.user-lists li a").click(function(){
			$(this).parent().parent().slideUp("fast");
			
			var list_data = $(this).data("content");
			var scrollpane_api = $(".list-content").data('jsp');
			if(scrollpane_api)
				scrollpane_api.destroy();
			
			
			$(".list-name").html($(this).html());
			
			$(".list-content").empty();
			
			if(list_data.categories)
				for(i in list_data.categories)
					$(".list-content").append("<li>" + list_data.categories[i].name + "</li>");
			
			if(list_data.social_networks)
				for(i in list_data.social_networks)
					$(".list-content").append("<li>" + list_data.social_networks[i].name + "</li>");
			
			$("#delete-list").data("list-id", list_data.id);
			$(".list-content").jScrollPane()
		});
		$("div.pagina-configuracoes div.column div.box.lists > div div.column ul.user-lists li:first-child a").trigger("click");
		
		FB.init({
			appId: FB_APP_ID,
			cookie: true, 
			status: true
		});
	 
		 $(".link-nerdstore-account").live('click', function(){
		 	var email_address = $("#overlay input[name=nerdstore-email-account]").val();
		 	$.ajax({
		 		type: "POST",
		 		url: HOST + "meu-perfil/redes-sociais/nerdstore/login",
		 		data: {
		 			"email_address": email_address
		 		},
		 		beforeSend: function(){
		 			$(".nerdstore-msg-wrapper button").hide()
		 		},
		 		success: function(response){
		 			if(response == 'SUCCESS')
		 				msg = 'Email enviado com sucesso. Verifique sua caixa de entrada.';
		 			else
		 				msg = 'Ops.. Alguma coisa deu errado. Tente novamente mais tarde!';
		 				
		 			$(".nerdstore-msg-wrapper").html(msg);
		 			setTimeout(function(){
		 				$("#overlay").fadeOut("fast");
		 			}, 5000);
		 		}
		 	})
		 });
		 
		  $(".link-nerdtrack-account").live('click', function(){
		 	var email_address = $("#overlay input[name=nerdtrack-email-account]").val();
		 	$.ajax({
		 		type: "POST",
		 		url: HOST + "meu-perfil/redes-sociais/nerdtrack/login",
		 		data: {
		 			"email_address": email_address
		 		},
		 		beforeSend: function(){
		 			$(".nerdtrack-msg-wrapper button").hide()
		 		},
		 		success: function(response){
		 			if(response == 'SUCCESS')
		 				msg = 'Email enviado com sucesso. Verifique sua caixa de entrada.';
		 			else
		 				msg = 'Ops.. Alguma coisa deu errado. Tente novamente mais tarde!';
		 				
		 			$(".nerdtrack-msg-wrapper").html(msg);
		 			setTimeout(function(){
		 				$("#overlay").fadeOut("fast");
		 			}, 5000);
		 		}
		 	})
		 });
		 
		 $(".link-blog-account").live('click', function(){
		 	var email_address = $("#overlay input[name=blog-email-account]").val();
		 	$.ajax({
		 		type: "POST",
		 		url: HOST + "meu-perfil/redes-sociais/jovemnerd/login",
		 		data: {
		 			"email_address": email_address
		 		},
		 		beforeSend: function(){
		 			$(".jovemnerd-blog-msg-wrapper").hide()
		 		},
		 		success: function(response){
		 			if(response == 'SUCCESS')
		 				msg = 'Email enviado com sucesso. Verifique sua caixa de entrada.';
		 			else
		 				msg = 'Ops.. Alguma coisa deu errado. Tente novamente mais tarde!';
		 				
		 			$(".jovemnerd-blog-msg-wrapper").html(msg);
		 			setTimeout(function(){
		 				$("#overlay").fadeOut("fast");
		 			}, 5000);
		 		}
		 	});
		 });
		 
		 /*
		 try{
			 $("#facebook").click(function(e){
			 	e.preventDefault();
			 	e.stopPropagation();
				FB.login(function(response) {
					$.ajax({
						url: HOST+"meu-perfil/redes-sociais/facebook/callback/",
						type: "POST",
						data: response.authResponse,
						success: function(data){
							window.location.reload();
						}
					});
				}, {scope:'publish_stream, publish_actions, email, user_about_me, user_likes, share_item, user_status'});
			});	
		} catch(err){}
		*/
		
		$("#delete-list").click(function(){
			var list_id = $(this).data("list-id");
			$.ajax({
				url: HOST+"perfil/configuracoes/listas/salvar",
				type: "POST",
				data: {
					list_id: list_id,
					method: 'remove_list'
				},
				success: function(response){
					var o = $.parseJSON(response);
					if(o.status == true)
						window.location.reload();
				}
			});
		});
		
		$("#save_social_network_options").click(function(){
			$.ajax({
				type: "POST",
				url: HOST+"meu-perfil/redes-sociais/salvar-configuracoes",
				data: $("#frm_social_networks_options").serialize(),
				success: function(response){
					GenericSuccessOverlay();
				}
			})
		});
		
		
		$("a[href^=#]").click(function(){
			if($(this).attr("id") == "facebook") return; 
			
			var $el = $($(this).attr("href") + "-overlay");
			var html_content = $el.html();
			$("#overlay .content").html(html_content).css({"margin-left": -265});
			$("#overlay").fadeIn();
		});
	});
}(jQuery));


function ListRemoved(){
	var htmlStr = ""
		+ "<div style='background-color:#fff;box-shadow:0px 0px 3px #444 inset;padding:15px;width:500px;'>"
		+ "		<div style='float:left;'><h1>Lista excluída com sucesso</h1></div>"
		+ "		<div class='clearfix'></div>"
		+ "		<div style='padding-left:30px;font-size:13px;color:#454545;font-family:Helvetica,Arial;text-transform: none;margin-top:15px;'>"
		+ "			Beleza."
		+ "		</div>"
		+ "</div>"
		+ "<div class='clearfix'></div>"
		+ "<div style='text-align:right;width:530px;margin-top:10px;'>"
		+ "		<button class='cancel'>Ok, valeu</button>"
		+ "</div>";
		
		$("#overlay .content").html(htmlStr).css({"margin-left": -265});
		$("#overlay").fadeIn();
}

function GenericSuccessOverlay(){
	var htmlStr = ""
		+ "<div style='background-color:#fff;box-shadow:0px 0px 3px #444 inset;padding:15px;width:500px;'>"
		+ "		<div style='float:left;'><h1>Alterações realizadas com sucesso!</h1></div>"
		+ "		<div class='clearfix'></div>"
		+ "		<div style='padding-left:30px;font-size:13px;color:#454545;font-family:Helvetica,Arial;text-transform: none;margin-top:15px;'>"
		+ "			Beleza."
		+ "		</div>"
		+ "</div>"
		+ "<div class='clearfix'></div>"
		+ "<div style='text-align:right;width:530px;margin-top:10px;'>"
		+ "		<button class='cancel'>Ok, valeu</button>"
		+ "</div>";
		
		$("#overlay .content").html(htmlStr).css({"margin-left": -265});
		$("#overlay").fadeIn();
}

function GenericErrorOverlay(){
	var htmlStr = ""
		+ "<div style='background-color:#fff;box-shadow:0px 0px 3px #444 inset;padding:15px;width:500px;'>"
		+ "		<div style='float:left;'><h1>Ooooops! Ocorreu um erro ao processar seu pedido.</h1></div>"
		+ "		<div class='clearfix'></div>"
		+ "		<div style='padding-left:30px;font-size:13px;color:#454545;font-family:SanskritHelvetica;text-transform: none;margin-top:15px;'>"
		+ "			Este não era o comportamento esperado.<br/>"
		+ " 		Tente novamente mais tarde."
		+ "		</div>"
		+ "</div>"
		+ "<div class='clearfix'></div>"
		+ "<div style='text-align:right;width:530px;margin-top:10px;'>"
		+ "		<button class='cancel'>Ok, tentarei novamente :)</button>"
		+ "</div>";
		
		$("#overlay .content").html(htmlStr).css({"margin-left": -265});
		$("#overlay").fadeIn();
}	

function PasswordChanged(){
	var htmlStr = ""
		+ "<div style='background-color:#fff;box-shadow:0px 0px 3px #444 inset;padding:15px;width:500px;'>"
		+ "		<div style='float:left;'><h1>Senha alterada com sucesso</h1></div>"
		+ "		<div class='clearfix'></div>"
		+ "		<div style='padding-left:30px;font-size:13px;color:#454545;font-family:Helvetica,Arial;text-transform: none;margin-top:15px;'>"
		+ "			Beleza."
		+ "		</div>"
		+ "</div>"
		+ "<div class='clearfix'></div>"
		+ "<div style='text-align:right;width:530px;margin-top:10px;'>"
		+ "		<button class='cancel'>Ok, valeu</button>"
		+ "</div>";
		
		$("#overlay .content").html(htmlStr).css({"margin-left": -265});
		$("#overlay").fadeIn();
		
		$("#profile_acess_data_form input").val("");
}	

function PasswordNotChanged(){
	var htmlStr = ""
		+ "<div style='background-color:#fff;box-shadow:0px 0px 3px #444 inset;padding:15px;width:500px;'>"
		+ "		<div style='float:left;'><h1>Ooooops! Ocorreu um erro ao alterar sua senha.</h1></div>"
		+ "		<div class='clearfix'></div>"
		+ "		<div style='padding-left:30px;font-size:13px;color:#454545;font-family:Helvetica,Arial;text-transform: none;margin-top:15px;'>"
		+ "			Isso pode ter ocorrido por diversos fatores, nerd. Vamos lá:<br/>"
		+ "			- Certifique-se que a senha atual informada é a correta.<br/>"
		+ "			- Além disso, os dois campos de confirmação de senha devem coincidir.<br/>"
		+ "			- Se essas duas condições foram satisfeitas e ainda assim você está vendo essa mensagem, estamos com alguma indisponibilidade no sistema. Tente novamente mais tarde."
		+ "		</div>"
		+ "</div>"
		+ "<div class='clearfix'></div>"
		+ "<div style='text-align:right;width:530px;margin-top:10px;'>"
		+ "		<button class='cancel'>Ok, valeu</button>"
		+ "</div>";
		
		$("#overlay .content").html(htmlStr).css({"margin-left": -265});
		$("#overlay").fadeIn();
}

function AccountCancelationRequestReceived(){
	var htmlStr = ""
		+ "<div style='background-color:#fff;box-shadow:0px 0px 3px #444 inset;padding:15px;width:500px;'>"
		+ "		<div style='float:left;'><h1>Pedido de cancelamento de conta recebido</h1></div>"
		+ "		<div class='clearfix'></div>"
		+ "		<div style='padding-left:30px;font-size:13px;color:#454545;font-family:Helvetica,Arial;text-transform: none;margin-top:15px;'>"
		+ "			Para prosseguir, siga os passos enviados no seu email."
		+ "		</div>"
		+ "</div>"
		+ "<div class='clearfix'></div>"
		+ "<div style='text-align:right;width:530px;margin-top:10px;'>"
		+ "		<button class='cancel'>Ok, valeu</button>"
		+ "</div>";
		
		$("#overlay .content").html(htmlStr).css({"margin-left": -265});
		$("#overlay").fadeIn();
}

function ListSuccessfullyCreated(){
	var htmlStr = ""
		+ "<div style='background-color:#fff;box-shadow:0px 0px 3px #444 inset;padding:15px;width:500px;'>"
		+ "		<div style='float:left;'><h1>Lista criada com sucesso</h1></div>"
		+ "		<div class='clearfix'></div>"
		+ "		<div style='padding-left:30px;font-size:13px;color:#454545;font-family:Helvetica,Arial;text-transform: none;margin-top:15px;'>"
		+ "			Beleza!"
		+ "		</div>"
		+ "</div>"
		+ "<div class='clearfix'></div>"
		+ "<div style='text-align:right;width:530px;margin-top:10px;'>"
		+ "		<button class='cancel'>Ok, valeu</button>"
		+ "</div>";
		
		$("#overlay .content").html(htmlStr).css({"margin-left": -265});
		$("#overlay").fadeIn();
}
