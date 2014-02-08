<?php

    Phalanx::urlPatterns(
        array(
            'adm'												=>	'admin.MainController.Home',
            
            'adm/posts-em-destaque'								=>	'admin.PostsController.EmDestaque',
            'adm/posts-em-destaque/promover/(?P<pid>[0-9]+?)'	=>	'admin.PostsController.Promover',
            
			'adm/usuarios'										=> 	'admin.UsersController.form',
			'adm/usuarios/ban'									=>	'admin.UsersController.ban',
			'adm/usuarios/unban'								=>	'admin.UsersController.unban',
			
			'adm/moderar-comentarios'							=>	'admin.CommentsController.form',
			'adm/moderar-comentarios/apagar-comentario'			=>	'admin.CommentsController.deleteComment',
			'adm/moderar-comentarios/apagar-resposta'			=>	'admin.CommentsController.deleteReply'
        )
    );
	
	
	
	
	Phalanx::urlPatterns(
		array(
		
			'memcache_test'		=>	'public.ExternalContentController.Memcache',
		
			''						=>	'public.MainController.Index',
			
			'exportar-meu-conteudo'	=>	'public.ExportDataController.Export',
			
			'login'					=>	'public.LoginController.Login',
			'login/proccess'		=>	'public.LoginController.Login',
			'logout'				=>	'public.LoginController.Logout',
			
			'cadastre-se'			=>	'public.SignupController.SignUp',
			'cadastre-se/processar'	=>	'public.SignupController.SignUpProccess',
			'cadastre-se/sucesso'	=>	'public.SignupController.SignUpSuccess',
			'cadastre-se/falha'		=>	'public.SignupController.SignUpError',
			
			'custom_timeline'		=> 	'public.MainController.TimeLineBuilder',
			'ajuda'					=> 	'public.MainController.DisplayHelpPage',
			
			'session-id'					=>	'public.MainController.SessionID',
			'carregar-conteudo-externo'		=>	'public.ExternalContentController.OpenFrame',
			
			'alterar-usuario'				=>	'public.UsersController.CheckAvailability',
			'alterar-usuario/finalizar'		=>	'public.UsersController.ChangeUsername'
		)
	);
	
	
	Phalanx::urlPatterns(
		array(
			'esqueci-minha-senha'												=> 'public.PasswordController.forgot_my_password',
			'esqueci-minha-senha/concluir'										=> 'public.PasswordController.confirm_reset_password',
			'esqueci-minha-senha/(?P<uid>[0-9]+?)/(?P<token>[a-zA-Z0-9]+?)/'	=> 'public.PasswordController.reset_password'
		)
	);
	
	
	Phalanx::urlPatterns(
		array(
			'site/post/(?P<post_id>\d+?)\-(?P<slug>.*)'		=>	'public.ExternalContentController.DisplayWordpressPost',
			
			'avaliar-postagem'								=>	'public.ExternalContentController.SkynerdRatingFrame',
			'dados-do-post'									=>	'public.ExternalContentController.GetPostData',
			
			'mini-ficha'							=>	'public.UsersController.UserCard',
			'mini-ficha/login'						=>	'public.UsersController.Login',
			
			'enviar-mensagem'						=>	'public.MessagesController.SendMessage',
			'apagar-mensagem/(?P<msgid>[0-9]+?)'	=>	'public.MessagesController.DeleteMessage',
			
			'mensagens/marcar-como-lida'			=>	'public.MessagesController.MarkAsReaded',
		)
	);
	
	Phalanx::urlPatterns(
		array(
			'perfil/timeline/posts-antigos'	=>	'public.TimelineController.GetOlderPosts',
			'perfil/timeline'				=>	'public.TimelineController.BuildFromList',
			
			'perfil/notificacoes'			=>	'public.NotificationsController.get_json',
			'perfil/notificacoes/ler'		=>	'public.NotificationsController.mark_as_readed',
			
			'perfil/procurar-aliados'		=>	'public.ProfileController.SearchPeople'
		)
	);
	
	Phalanx::urlPatterns(
		array(
			'comentar-post'									=>	'public.PostsController.Comment',
			'comentarios-do-post'							=>	'public.PostsController.GetComments',
			
			'perfil/avaliar'								=>	'public.RatingController.Rate',
			'perfil/avaliacoes'								=>	'public.RatingController.GetInfo',
			'perfil/favoritar'								=>	'public.FavoritesController.proccess',
			'perfil/reblogar'								=>	'public.ReblogController.Reblog',
			'perfil/desblogar'								=>	'public.ReblogController.Unblog',
			
			'perfil/posts-antigos'							=>	'public.ProfileController.DisplayOldPosts',
						
			'perfil/compartilhar'							=>	'public.PostsController.Post',
			'posts-em-destaque'								=>	'public.PostsController.PromotedPosts',
			
			
			'perfil/compartilhar/anexar-arquivos'			=>	'public.PostsAttachmentsController.AttachFiles',
			'perfil/compartilhar/anexar-arquivos/fallback'	=>	'public.PostsController.AttachFilesFrame',
			'perfil/compartilhar/analisar-url'				=>	'public.PostsController.ParseURL',
						
			'perfil/apagar-post'							=>	'public.PostsController.DeletePost',
			'perfil/apagar-comentario'						=>	'public.PostsController.DeleteComment',
			
			'perfil/adicionar-aliado'						=>	'public.ProfileController.RequestFriendship',
			'perfil/remover-aliado'							=>	'public.ProfileController.RemoveFriend',
			'perfil/aprovar-pedido-de-amizade'				=>	'public.ProfileController.AllowFriendshipRequest',
			'perfil/reprovar-pedido-de-amizade'				=>	'public.ProfileController.DenyFriendshipRequest',
			'perfil/bloquear-pedido-de-amizade'				=>	'public.ProfileController.BlockFriendshipRequest',
			
			
			'perfil/configuracoes'							=>	'public.ProfileSettingsController.form',
			'perfil/configuracoes/salvar'					=>	'public.ProfileSettingsController.save_profile_info',
			'perfil/configuracoes/acesso/salvar'			=>	'public.ProfileSettingsController.save_profile_access_data',
			'perfil/configuracoes/privacidade/salvar'		=>	'public.ProfileSettingsController.save_profile_privacy_data',
			'perfil/configuracoes/listas/salvar'			=>	'public.ProfileSettingsController.save_profile_list_data',
			'perfil/configuracoes/opcoes/salvar'			=>	'public.ProfileSettingsController.SaveProfileOptions',
			
			
			'perfil/configuracoes/trocar-avatar'			=>	'public.ProfileAvatarController.change_avatar',
			'perfil/configuracoes/trocar-avatar/fallback'	=>	'public.ProfileAvatarController.avatar_upload_frame',
			'perfil/configuracoes/concluir-troca-de-avatar'	=>	'public.ProfileAvatarController.confirm_avatar_change',
			
			'perfil/configuracoes/gamer-tags/salvar'		=>	'public.ProfileGamerTagsController.save',
			'perfil/configuracoes/notificacoes/salvar'		=>	'public.ProfileSettingsController.save_profile_notifications_settings',
			
			
			'perfil/configuracoes/cancelar-conta'					=>	'public.ProfileAccessController.CancelAccount',
			'perfil/configuracoes/cancelar-conta/confirmar'			=>	'public.ProfileAccessController.CancelAccountConfirm',
			'perfil/configuracoes/cancelar-conta/segundo-passo'		=>	'public.ProfileAccessController.SendCancelAccountMail',
			'perfil/configuracoes/reativar-conta'					=>	'public.ProfileAccessController.ReactivateAccountRequest',
			'perfil/configuracoes/reativar-conta/confirmar'			=>	'public.ProfileAccessController.ReactivateAccount',
			'perfil/configuracaoes/tempo-limite-excedido'			=>	'public.ProfileAccessController.TimeLimitExceeded',

			
			'perfil/(?P<username>([.A-Za-z0-9_-]+))'				=>	'public.ProfileController.DisplayProfile',
			'perfil/(?P<username>([.A-Za-z0-9_-]+))/badges'			=>	'public.ProfileController.DisplayUserBadges',
			'perfil/(?P<username>([.A-Za-z0-9_-]+))/aliados'		=>	'public.ProfileController.DisplayUserFriends',
			'perfil/(?P<username>([.A-Za-z0-9_-]+))/aliados/pagina'	=>	'public.ProfileController.GetFriendsPage',
			
			'perfil/(?P<username>([.A-Za-z0-9_-]+))/post/(?P<post_id>\d+?)(\-.*)?' =>	'public.PostsController.DisplayPost'
		)
	);
	
	Phalanx::urlPatterns(
		array(
			'meu-perfil/redes-sociais/salvar-configuracoes'					=>	'public.SocialNetworksController.saveSharingOptions',
		
			'meu-perfil/redes-sociais/instagram/login'						=>	'public.InstagramController.login',
			'meu-perfil/redes-sociais/instagram/logout'						=>	'public.InstagramController.logout',
			'meu-perfil/redes-sociais/instagram/callback/(?P<variables>.*)'	=>	'public.InstagramController.callback',
			
			'meu-perfil/redes-sociais/twitter/login' 						=> 	'public.TwitterController.login',
			'meu-perfil/redes-sociais/twitter/logout' 						=> 	'public.TwitterController.logout',
			'meu-perfil/redes-sociais/twitter/callback'						=>	'public.TwitterController.callback',
			'meu-perfil/redes-sociais/twitter/compartilhar-post'			=>	'public.TwitterController.sharePost',
			
			'meu-perfil/redes-sociais/youtube/login'						=>	'public.YouTubeController.login',
			'meu-perfil/redes-sociais/youtube/logout'						=>	'public.YouTubeController.logout',
			'meu-perfil/redes-sociais/youtube/callback/(?P<variables>.*)'	=>	'public.YouTubeController.callback',
			
			'meu-perfil/redes-sociais/nerdstore/login'						=>	'public.NerdStoreController.login',
			'meu-perfil/redes-sociais/nerdstore/logout'						=>	'public.NerdStoreController.logout',
			'meu-perfil/redes-sociais/nerdstore/callback'					=>	'public.NerdStoreController.callback',
			
			'meu-perfil/redes-sociais/nerdtrack/login'						=>	'public.NerdTrackController.login',
			'meu-perfil/redes-sociais/nerdtrack/logout'						=>	'public.NerdTrackController.logout',
			'meu-perfil/redes-sociais/nerdtrack/callback'					=>	'public.NerdTrackController.callback',
			
			'meu-perfil/redes-sociais/jovemnerd/login'						=>	'public.JovemNerdBlogController.login',
			'meu-perfil/redes-sociais/jovemnerd/logout'						=>	'public.JovemNerdBlogController.logout',
			'meu-perfil/redes-sociais/jovemnerd/callback'					=>	'public.JovemNerdBlogController.callback',
			
			'meu-perfil/redes-sociais/facebook/login'						=>	'public.FacebookController.login',
			'meu-perfil/redes-sociais/facebook/callback'					=>	'public.FacebookController.callback',
			'meu-perfil/redes-sociais/facebook/logout'						=>	'public.FacebookController.logout',
			'meu-perfil/redes-sociais/facebook/compartilhar-post'			=>	'public.FacebookController.sharePost',
			
			'meu-perfil'													=>	'public.ProfileController.DisplayProfile',
			'meu-perfil/aliados'											=>	'public.ProfileController.DisplayUserFriends',
			'meu-perfil/badges'												=>	'public.ProfileController.DisplayUserBadges'
		)
	);

	
	Phalanx::urlPatterns(
		array(
			'nerdtrack/get'		=>	'public.NerdTrackController.get',
			'nerdtrack/post'	=>	'public.NerdTrackController.post',
			'nerdtrack/rate'	=>	'public.NerdTrackController.rate',
		)
	);


	Phalanx::urlPatterns(
		array(
			'(?P<username>([.A-Za-z0-9_-]+))'	=>	'public.ProfileController.DisplayProfileFromShortURL',
		)
	);
	 