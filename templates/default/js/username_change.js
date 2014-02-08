$(document).ready(function(){
	$("#force-username-change").submit(function(e){
		e.preventDefault();
		e.stopPropagation();
		
		var chosen_username = $.trim($("#new_username").val());
		if(chosen_username == '' || (! chosen_username.match(/^[.A-Za-z0-9_-]+$/i))){
			$("#new_username").addClass('error');
			
			var htmlStr = ""
			+ "<div style='background-color:#fff;box-shadow:0px 0px 3px #444 inset;padding:15px;width:500px;'>"
			+ "		<div style='float:left;'><h1>Ooops!</h1></div>"
			+ "		<div class='clearfix'></div>"
			+ "		<div style='padding-left:30px;font-size:13px;color:#454545;font-family:SanskritHelvetica;text-transform: none;margin-top:15px;'>"
			+ "			Nerd, o login escolhido é inválido. <br/>Lembre-se: Não pode conter espaços, acentos e/ou caracteres especiais. <br/> Pontos (.) e underscores (_) são permitidos, ok?"
			+ "		</div>"
			+ "</div>"
			+ "<div class='clearfix'></div>"
			+ "<div style='text-align:right;width:530px;margin-top:10px;'>"
			+ "		<button class='cancel'>Beleza, vou arrumar isso.</button>"
			+ "</div>";
			
			return false;
		}
		
		var $form = $(this);
		$.ajax({
			type: $form.attr("method"),
			url:  $form.attr("action"),
			data: $form.serialize(),
			beforeSend: function(){
				$form.find("input:submit").attr("disabled", true);
				$(".ajax-loader").fadeIn();
			},
			success: function(server_response){
				$form.find("input:submit").removeAttr("disabled");
				$(".ajax-loader").fadeOut();
				
				
				var htmlStr = "";
				var o = eval('('+ server_response + ');');
				if(o.available == true){
						htmlStr = ""
						+ "<div style='background-color:#fff;box-shadow:0px 0px 3px #444 inset;padding:15px;width:500px;'>"
						+ "		<div style='float:left;'><h1>Nome de usuário disponível</h1></div>"
						+ "		<div class='clearfix'></div>"
						+ "		<div style='padding-left:30px;font-size:13px;color:#454545;font-family:SanskritHelvetica;text-transform: none;margin-top:15px;'>"
						+ "		Nerd, o nome de usuário " + o.username + " está disponível para uso. Vamos proceder com a alteração?"
						+ "		</div>"
						+ "</div>"
						+ "<div class='clearfix'></div>"
						+ "<div style='text-align:right;width:530px;margin-top:10px;'>"
						+ "		<button class='change-my-user'>SIM! Mude meu usuário, SLAVE ROBOTO</button>"
						+ "		<button class='cancel'>Não, eu quero escolher outro login</button>"
						+ "</div>";
				} else {
					htmlStr = ""
					+ "<div style='background-color:#fff;box-shadow:0px 0px 3px #444 inset;padding:15px;width:500px;'>"
					+ "		<div style='float:left;'><h1>Ooops!</h1></div>"
					+ "		<div class='clearfix'></div>"
					+ "		<div style='padding-left:30px;font-size:13px;color:#454545;font-family:SanskritHelvetica;text-transform: none;margin-top:15px;'>"
					+ "			Nerd, infelizmente foram mais rápidos que você e o nome de usuário escolhido já está em uso"
					+ "		</div>"
					+ "</div>"
					+ "<div class='clearfix'></div>"
					+ "<div style='text-align:right;width:530px;margin-top:10px;'>"
					+ "		<button class='cancel'>Ahh, vou escolher outro :(</button>"
					+ "</div>";
				}
				
				
				$("#overlay .content").html(htmlStr).css({"margin-left": -265});
				$("#overlay").fadeIn();
			}
		})
	});
	
	$(".change-my-user").live('click', function(){
		$(this).attr('disabled');
		
		$.ajax({
			type: "POST",
			url: HOST + "alterar-usuario/finalizar",
			data: {},
			success: function(server_response){
				var o = eval('('+ server_response + ');');
				
				var htmlStr = '';
				
				if(o.status == true){
					htmlStr = ""
					+ "<div style='background-color:#fff;box-shadow:0px 0px 3px #444 inset;padding:15px;width:500px;'>"
					+ "		<div style='float:left;'><h1>Obrigado!</h1></div>"
					+ "		<div class='clearfix'></div>"
					+ "		<div style='padding-left:30px;font-size:13px;color:#454545;font-family:SanskritHelvetica;text-transform: none;margin-top:15px;'>"
					+ "			Seu nome de usuário foi alterado! <br/> Por questões de segurança, você vai precisar fazer o login novamente, ok?"
					+ "		</div>"
					+ "</div>"
					+ "<div class='clearfix'></div>"
					+ "<div style='text-align:right;width:530px;margin-top:10px;'>"
					+ "		<button class='re-login'>Beleza!</button>"
					+ "</div>";
				} else {
					htmlStr = ""
					+ "<div style='background-color:#fff;box-shadow:0px 0px 3px #444 inset;padding:15px;width:500px;'>"
					+ "		<div style='float:left;'><h1>Ooops!</h1></div>"
					+ "		<div class='clearfix'></div>"
					+ "		<div style='padding-left:30px;font-size:13px;color:#454545;font-family:SanskritHelvetica;text-transform: none;margin-top:15px;'>"
					+ "			Que pena! Ocorreu um erro ao alterar seu nome de usuário. Tente novamente mais tarde."
					+ "		</div>"
					+ "</div>"
					+ "<div class='clearfix'></div>"
					+ "<div style='text-align:right;width:530px;margin-top:10px;'>"
					+ "		<button class='cancel'>Ok :(</button>"
					+ "</div>";
				}
				$("#overlay .content").html(htmlStr);
			}
		})
	})
	
	$('.cancel').live('click', function(){
		$("#overlay").fadeOut();
		setTimeout(function(){
			$("#overlay .content").empty();
		}, 700)
	});
	
	$('.re-login').live('click', function(){
		window.location.href = HOST;
	});
});