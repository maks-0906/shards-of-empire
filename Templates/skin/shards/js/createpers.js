/**
 *
 * @constructor CreatePersonage
 */
function CreatePersonage()
{
	if(typeof sync != 'function') throw  new Error('SYNC function not defined');
}

/**
 *
 */
CreatePersonage.prototype.initAndShowPage = function()
{
	// Заполнение начальными даннми
	var request = {url: Config.BASE_URL + "/personage/attributes.json"};
	$(request).bind('complete', function(data){delete(request)});

	sync('load', request, {
		error: function(response){console.log(response)},
		success: function(response)
		{
			console.log('response -> ', response);
			if(typeof response.fractions == 'undefined'
				|| typeof response.types == 'undefined' || typeof response.religions == 'undefined')
				throw new Error('One of the server parameter is not defined');

			if(response.fractions.length === 0 || response.types.length === 0 || response.religions.length === 0)
				throw new Error("One of the server parameter must be array with count great 0`");

			//CreatePersonage.setTemplate(function(){
				Lang.bindTranslation(
					['create_personage', 'religion', 'fractions', 'types'],
					CreatePersonage.bindHandlerAndShowPage, {'response': response}
				);
			//});
		}
	});
};

/**
 * Присваивание обработчиков событий при инициализации окна создания персонажа.
 * @param data
 * @param lang
 */
CreatePersonage.bindHandlerAndShowPage = function(data, lang)
{
	var response = data.response;
	for(var i = 0; i < response.fractions.length; i++)
	{
		if(!response.fractions[i].hasOwnProperty('name')) throw new Error("Fraction not defined name");
		var translateFraction = lang[response.fractions[i].name];
		response.fractions[i].name_image = response.fractions[i].name;
		response.fractions[i].name = translateFraction.name;
		response.fractions[i].description = translateFraction.description;
	}

	for(var i = 0; i < response.religions.length; i++)
	{
		if(!response.religions[i].hasOwnProperty('name')) throw new Error("Religion not defined name");

		var translateReligion = lang[response.religions[i].name];
		response.religions[i].name = translateReligion.name;
		response.religions[i].description = translateReligion.description;
	}

	/**
	 * Рендеринг части фракции образов.
	 */
	response.renderTypes = function()
	{
		return function(data) {
			var image = {
					fraction_id: null
				},
				idFraction = null,
				imageTemplate = "",
				numberAvatar;
			/* @var response.types {Array} */
			for(var i = 0; i < response.types.length; i++)
			{
				var currentType = response.types[i];
				// Новые параметры для формирования нового блока фракции
				if(image.fraction_id  === null) {
					image.fraction_id = currentType.fraction_id;
					numberAvatar = 0;
				}

				// Если закончились образы одной фракции или итерации всего списка окончены скидываем шаблонизировнный html код
				if(currentType.fraction_id > image.fraction_id || i == (response.types.length - 1))
				{
					// Если цикл окончен сохраняем последний образ
					if(i == (response.types.length - 1))image['id_' + numberAvatar.toString()] = currentType.id;

					imageTemplate += Mustache.to_html(data, image);
					// Задаём новый блок
					image = { fraction_id: currentType.fraction_id };
					numberAvatar = 0;
				}

				image['id_' + numberAvatar.toString()] = currentType.id;
				numberAvatar += 1;
			}

			return imageTemplate;
		}
	};

	response.image = Config.IMAGE_URL;
	console.log("END -> ", response);
	var fullData = $.extend($.extend(response, window.main), lang);
	$('.start-data').each(function()
	{
		var el=$(this);
		$(this).html(Mustache.render(el.html(), fullData));
		console.log("i");
	});

	CreatePersonage.initEventsForUseCases();

	showPopup('start-data');
	drawGradientText($('.start-data'));
	// Одинаковая высота для колонок фракций и религий
	setEqualHeight($('.fraction, .religion').find('.desc'));
	function setEqualHeight(columns){
		var tallestcolumn = 0;
		columns.each(function(){
			currentHeight = $(this).height();
			if(currentHeight > tallestcolumn){
				tallestcolumn = currentHeight;
			}
		});
		columns.height(tallestcolumn);
	}
};

/**
 * Инициализация шаблона окна создания персонажа.
 * @param success
 * @param data
 */
CreatePersonage.setTemplate = function(success, data)
{
	if($('.start-data').length === 0)
	{
		$.get(Config.TEMPLATES_URL + "/create_personage.html", function(data) {
			$('#c-popup').prepend(data);
			if(typeof success == 'function') success({data: data});
		});
	}
	else
	{
		if(typeof success == 'function') success({data: data});
	}
};

CreatePersonage.openStartDataPage = function(num){
	var currStep = $('.steps').find('a.active').data('target');
	// for (var i=0; i<=currStep; i++){
	// 	$('.step'+i).addClass('passed');
	// }

	$('.start-data-page').hide();
	$('.steps').find('a').addClass('disabled').removeClass('active');
	for (var i=0; i<=num; i++){
		$('.step'+i).removeClass('disabled');
	}
	$('.start-data-page.page'+num).show();
	$('.step'+num).removeClass('disabled').addClass('active');

	if($('.step'+num).hasClass('passed'))
		$('.next-step').removeClass('disabled');
	else
		$('.next-step').addClass('disabled');
}

/**
 * что бы в массиве 0 всегда были ячейки которые в center
 в массиве 1 например всегда были ячейки что в top
 */
CreatePersonage.initEventsForUseCases = function()
{
	// Переход по шагам начальных данных
	$('.start-data').find('.steps').find('a').on('click', function(e)
	{
		e.preventDefault();
		if(!$(this).hasClass('disabled')){
			var page=$(this).data('target');
			CreatePersonage.openStartDataPage(page);
		}
	});

	// Далее
	$('.next-step').on('click', function(e){
		e.preventDefault();
		if(!$(this).hasClass('disabled'))
		{
			var rootPage = $('.start-data');
			var page = rootPage.find('.steps').find('a.active').data('target')+1;
			rootPage.find('.steps').find('a.active').addClass('passed')
			CreatePersonage.openStartDataPage(page);
			// $(this).addClass('disabled');
		}
	});
	// Назад
	$('.prev-step').on('click', function(e)
	{
		e.preventDefault();
		if(!$(this).hasClass('disabled')){
			var page=$('.start-data').find('.steps').find('a.active').data('target')-1;
			CreatePersonage.openStartDataPage(page);
			// $('.next-step').removeClass('disabled');
		}
	});

	/**
	 *
	 * @type {Object}
	 */
	var startData =
	{
		world_id: parseInt(localStorage.getItem('world_id'), 10),
		fraction_id:'',
		type_personage_id:'',
		nick:'',
		religion_id:'',
		city:''
	},

	inputTimer,
	inputTimeout = 100;

	// выбор фракций
	$('.fraction').on('click',function(e)
	{
		e.preventDefault();
		$('.fraction.checked').removeClass('checked');
		$(this).addClass('checked');
		$('.next-step').removeClass('disabled');
		startData.fraction_id = $(this).data('fraction');
		console.log(startData);
		//Show avatars for selected fraction
		$('.avatar-block').addClass('hidden').filter('[data-fraction="'+startData.fraction_id +'"]').removeClass('hidden')
	});

	// выбор аватара
	$('.start-data-avatar').on('click', function(e)
	{
		e.preventDefault();
		$('.start-data-avatar').removeClass('checked');
		$(this).addClass('checked');
		startData.type_personage_id=$(this).data('avatar');
		if(checkAvaAndNick()){
			$('.next-step').removeClass('disabled');
		}
		console.log(startData);
	});

	$('#nickname').on('keydown', function()
	{
		clearTimeout(inputTimeout);
		inputTimer = setTimeout(function(){
			if(checkAvaAndNick()){
				$('.next-step').removeClass('disabled');
				startData.nick=$('#nickname').val();
			}
			 else{
			 	startData.nick='';
				$('.next-step').addClass('disabled');
			 }
		}, inputTimeout);

		console.log(startData);
	});

	function checkAvaAndNick()
	{
		if($('.start-data-avatar.checked')[0] && $('#nickname').val())
			return true;
		else
			return false;
	}

	// выбор религии
	$('.religion').on('click',function(e)
	{
		e.preventDefault();
		$('.religion.checked').removeClass('checked');
		$(this).addClass('checked');
		startData.religion_id=$(this).data('religion');
		$('.next-step').removeClass('disabled');
		console.log(startData);
	});

	// подтверждение города
	$('#city').on('keydown', function()
	{
		clearTimeout(inputTimeout);
		inputTimer=setTimeout(function(){
			if($('#city').val()){
				$('.play').removeClass('disabled');
				startData.city=$('#city').val();
			}
			 else{
				startData.city='';
				$('.play').addClass('disabled');
			 }
		}, inputTimeout);
		console.log(startData);
	});

	// играть
	$('.play').on('click',function(e)
	{
		e.preventDefault();
		if(!$(this).hasClass('disabled'))
		{
			console.log(startData);

			try
			{
				var request = {url: Config.BASE_URL + "/personage/create.json", attributes: startData};
				$(request).bind('complete', function(data){delete(request)});

				sync('save', request, {
					error: function(response){
						console.log(response);
						showMessagePopup(13);
						hidePopup('start-data');
					},
					success: function(response)
					{
						console.log(response);
						window.location='locations.html';
					}
				});
			}
			catch(err)
			{
				// TODO: Если персонаж уже существует выводим сообщение, в tools.js SYNC функции сменить сообщение исключения
				console.log(err.message);
				showMessagePopup(0, err.message);
				hidePopup('start-data');
			}
		}
	});
};

function cp()
{
	var createPersonage = new CreatePersonage;
	createPersonage.initAndShowPage();
}