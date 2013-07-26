$(document).ready(function() {
   // put all your jQuery goodness in here.


	function showMessagePopupVal(){
		$('.overlay-wrapper, .message-box').removeClass('hidden');
		$('.message-box').find('h3').children('.corners-content').text('Сообщение авторизации!');
	}


	var authorize 		= Config.BASE_URL + "/user/login.json",
		registerButton 	= Config.BASE_URL + "/user/register.json",
		restorePass 	= Config.BASE_URL + "/user/recovery.json",

		captchaAu 		= '<div class="captcha"><img style="-webkit-user-select: none" src="' + Config.BASE_URL + '/user/captcha.auth" /></div><br/><input id="captcha" value="" name="captcha"/>',
		captchaReg 		= '<div class="captcha"><img style="-webkit-user-select: none" src="' + Config.BASE_URL + '/user/captcha.register" /></div><br/><input id="captcha" value="" name="captcha"/>',
		captchaRes 		= '<div class="captcha"><img style="-webkit-user-select: none" src="' + Config.BASE_URL + '/user/captcha" /></div><br/><input id="captcha" value="" name="captcha"/>',

		confirmation 	= "Config.BASE_URL/user/confirmation.json",
		MS_TIMEOUT 		= 6000;

	// $('#register-button').on('click', function(){

	// });

	// $('#restore-pass').on('click', function(){

	// });

	// $('#already-registered').on('click', function(){

	// });

	// $('#authorize').on('click', function(){

	// });
	//<img style="-webkit-user-select: none" src="Config.BASE_URL/user/captcha.auth" />
	// <br/>
	// <input id="captcha" value="" name="captcha"/>


	//sendCapcha
	function withCapcha(pass, captchaUrl, mess){
		showMessagePopup(0,   mess!=undefined?mess+captchaUrl:captchaUrl);
		$('.take').on('click',function(e){
			e.preventDefault()
			$.jsonp({
				url: pass,
				callback: '_jqjsp',
				callbackParameter: 'callback',
				timeout: MS_TIMEOUT,
				data: {
					login: $('#login').val(),
					password: md5( $('#password').val()),
					captcha: $('#captcha').val()
				},
				success: function(json, textStatus, xOptions){
					console.log(json);
					if(json.status==6||json.status==5){
						json.status==6?mess='Капча не совпадает<br/>':mess='Нужно заполнить капчу<br/>';
						withCapcha(pass, captchaUrl, mess);
					}else{
                          showMessagePopup(json.status);
                           if(!$("#form-login").valid()){
                            $("#form-login").trigger('submit');
                            showMessagePopupVal();
                        }else{
                            $.jsonp({
                                url: authorize,
                                callback: '_jqjsp',
                                callbackParameter: 'callback',
                                timeout: MS_TIMEOUT,
                                data: {
                                    login: $('#login').val(),
                                    password: md5( $('#password').val())
                                    // on_captcha: 1
                                }
                                     })
                                      }
                           window.location='locations.html';
					     }
				},
				error: function(xOptions, textStatus) {
					console.log('login request:'+textStatus);
					showMessagePopup(13);
				}
			});
			$(this).unbind();
		});
	}


	//authoriz
	$(function(){
		$('#btn-enter').on('click',function(e){
			e.preventDefault();
			showOverlay();
			// test login and password
			if(!$("#form-login").valid()){
				$("#form-login").trigger('submit');
				showMessagePopupVal();
			}else{
				$.jsonp({
					url: authorize,
					callback: '_jqjsp',
					callbackParameter: 'callback',
					timeout: MS_TIMEOUT,
					data: {
						login: $('#login').val(),
						password: md5( $('#password').val())
						// on_captcha: 1
					},
					success: function(json, textStatus, xOptions){
						hideOverlay();
						console.log(json);
						if(json.status==6||json.status==5){
							withCapcha(authorize, captchaAu);
						}else{
							if(json.status == 1){
								window.location='locations.html';
							} else{
								showMessagePopup(json.status);
							}
						}								
					},
					error: function(xOptions, textStatus) {
						console.log('login request:'+textStatus);
						showMessagePopup(13);
					}
				});
			}
		});
		//authoriz

		//regist
		$('#btn-done').on('click',function(e){
			e.preventDefault();
			showOverlay();
			// test login and password
			if(!$("#form-login").valid()){
				$("#form-login").trigger('submit');
				showMessagePopupVal();
			}else{
				$.jsonp({
					url: registerButton,
					callback: '_jqjsp',
					callbackParameter: 'callback',
					timeout: MS_TIMEOUT,
					data: {
						login: $('#login').val(),
						password: md5( $('#password').val())
					},
					success: function(json, textStatus, xOptions){
						hideOverlay();
						console.log(json);	
						if(json.status==6||json.status==5){
							withCapcha(registerButton, captchaReg);
						}else{
							if(json.status == 1){
								window.location=Config.SKIN_URL + "/locations.html";
							} else{
								showMessagePopup(json.status);
							}
						}
					},
					error: function(xOptions, textStatus) {
						console.log('login request:'+textStatus);
						showMessagePopup(13);
					}
				});
			}
		});
		//regist

		// restore the password
		$('#btn-restore').on('click',function(e){
			e.preventDefault();		
			showOverlay();
			// test login and password
			if(!$("#form-login").valid()){
				$("#form-login").trigger('submit');
				showMessagePopupVal();
			}else{
				function sendCaptchaRes(mess){
					showMessagePopup(0, mess!=undefined?mess+captchaRes:captchaRes);
					$('.take').on('click',function(e){
						$.jsonp({
							url: restorePass,
							callback: '_jqjsp',
							callbackParameter: 'callback',
							timeout: MS_TIMEOUT,
							data: {
								login: $('#login').val(),
								captcha: $('#captcha').val()
							},
							success: function(json, textStatus, xOptions){
								hideOverlay();
								console.log(json);
								if(json.status==6||json.status==5){
									json.status==6?mess='Капча не совпадает<br/>':mess='Нужно заполнить капчу<br/>';
									sendCaptchaRes(mess);
								}else{
									showMessagePopup(json.status);
								}
							},
							error: function(xOptions, textStatus) {
								console.log('login request:'+textStatus);
								showMessagePopup(13);
							}
						});
						$('.take').unbind('click');
					});
				}
				sendCaptchaRes();
			}
		});
		// restore the password
		// unite auth system
			$('#btn-ed').on('click', function(e){
				e.preventDefault();
				showOverlay();
				showMessagePopup(0, 'Единая система авторизации', 'Помощь')
			});
		// unite auth system
	});

});