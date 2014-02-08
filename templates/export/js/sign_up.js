$(document).ready(function(){
	$("#frm-cadastro").submit(function(e){
		e.preventDefault();
		e.stopPropagation();
		
		var errors = new Array();
		
		var chosen_username = $.trim($("#login").val());
		if(chosen_username == '' || (! chosen_username.match(/^[.A-Za-z0-9_-]+$/i))){
			$("#login").addClass('error');
			errors.push("Verifique o nome de usuário escolhido")
		}
			
		var email = $.trim($("#email").val())
		if(email == '' || (! email.match(/^[A-Z0-9._%+-]+@[A-Z0-9.-]+\.[A-Z]{2,4}$/i))){
			$("#email").addClass("error");
			errors.push("Endereço de email inválido");
		}
		
		var password = $("#password").val();
		var password_confirmation = $("#password_confirmation").val();
		
		if($.trim(password) == '' || (password != password_confirmation)){
			$("#password, #password_confirmation").addClass("error");
			errors.push("Os campos de senha devem coincidir");
		}
		
		var real_name = $.trim($("#real_name").val());
		if(real_name == ''){
			$("#real_name").addClass("error");
			errors.push("Eu preciso saber qual é o seu nome");
		}

		if(errors.length){
			var htmlStr = ""
			+ "<div style='background-color:#fff;box-shadow:0px 0px 3px #444 inset;padding:15px;width:500px;'>"
			+ "		<div style='float:left;'><h1>Ooops!</h1></div>"
			+ "		<div class='clearfix'></div>"
			+ "		<div style='padding-left:30px;font-size:13px;color:#454545;font-family:SanskritHelvetica;text-transform: none;margin-top:15px;'>"
			+ "			Nerd, existem alguns problemas na sua ficha que me impedem de fechar o cadastro. Vamos rever esses pontos: </br>";
			for(i = 0; i < errors.length; i++)
				htmlStr += "&nbsp;&nbsp;- " + errors[i] + ";<br />";
			
			htmlStr += "<br />"
			+ "		</div>"
			+ "</div>"
			+ "<div class='clearfix'></div>"
			+ "<div style='text-align:right;width:530px;margin-top:10px;'>"
			+ "		<button class='cancel'>Beleza, vou arrumar isso.</button>"
			+ "</div>";
			
			$("#overlay .content").html(htmlStr).css({"margin-left": -265});
			$("#overlay").fadeIn();
			return false;
		}	
			
		var $form = $(this);
		$.ajax({
			async: false,
			type: $form.attr("method"),
			url:  $form.attr("action"),
			data: $form.serialize(),
			beforeSend: function(){
				$form.find("button").attr("disabled", true);
				$(".ajax-loader").fadeIn();
			},
			success: function(server_response){
				$form.find("input:submit").removeAttr("disabled");
				$(".ajax-loader").fadeOut();
				
				
				var htmlStr = "";
				var o = eval('('+ server_response + ');');
				if(o.status == true){
						htmlStr = ""
						+ "<div style='background-color:#fff;box-shadow:0px 0px 3px #444 inset;padding:15px;width:500px;'>"
						+ "		<div style='float:left;'><h1>Obrigado!</h1></div>"
						+ "		<div class='clearfix'></div>"
						+ "		<div style='padding-left:30px;font-size:13px;color:#454545;font-family:SanskritHelvetica;text-transform: none;margin-top:15px;'>"
						+ "			Seu cadastro na Skynerd já está feito! Agora você já pode fazer login e divertir-se no universo nerd."
						+ "		</div>"
						+ "</div>"
						+ "<div class='clearfix'></div>"
						+ "<div style='text-align:right;width:530px;margin-top:10px;'>"
						+ "		<button class='go-to-login-page'>OBRIGADO SLAVE ROBOTO</button>"
						+ "</div>";
				} else {
					htmlStr = ""
					+ "<div style='background-color:#fff;box-shadow:0px 0px 3px #444 inset;padding:15px;width:500px;'>"
					+ "		<div style='float:left;'><h1>Ooops!</h1></div>"
					+ "		<div class='clearfix'></div>"
					+ "		<div style='padding-left:30px;font-size:13px;color:#454545;font-family:SanskritHelvetica;text-transform: none;margin-top:15px;'>"
					+ "			Caro nerd, tem alguma coisa errada por aqui. <br/>";
					if(o.username == false) htmlStr += "&nbsp;&nbsp;-O login que você escolheu já está em uso. <br/>"
					if(o.email == false) htmlStr += "&nbsp;&nbsp;-Já existe um cadastro com este endereço de email. <br/>"
					htmlStr += ""
					+ "		</div>"
					+ "</div>"
					+ "<div class='clearfix'></div>"
					+ "<div style='text-align:right;width:530px;margin-top:10px;'>"
					+ "		<button class='cancel'>Deixa que eu arrumo!</button>"
					+ "</div>";
				}
				
				
				$("#overlay .content").html(htmlStr).css({"margin-left": -265});
				$("#overlay").fadeIn();
			}
		})
	});
	
	$(".go-to-login-page").live('click', function(){
		window.location.href = HOST + 'login';
	});
	
	$('.cancel').live('click', function(){
		$("#overlay").fadeOut();
		setTimeout(function(){
			$("#overlay .content").empty();
		}, 700)
	});
	
});