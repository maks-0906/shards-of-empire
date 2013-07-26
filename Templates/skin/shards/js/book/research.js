/**
 * Файл содержит класс модуль управлением и отображением исследований.
 *
 * @author Greg
 * @package book
 */

/**
 * Класс модуль управлением и отображением исследований персонажа.
 * @constructor {Research}
 */
Book("Research", {
	template:'.research-page',
	nameBook:'research-page',
	nameTemplate:'research.html',
	callingWindow:'.btn-research',
	translations:['research', 'building_upgrade', 'building', 'resources', 'message'],
	rightPart:'.research-page.column-right',
	leftPart:'.research-page.column-left',
	cacheTemplate:{
		left:"",
		right:""
	},
	currentIdResearch:null,
	updateInterval: null,
	langTranslate: null,
	init:function (callingWindow, loadTemplate) {
		// Инициализация своей анимации.
		this.animate.bind('start.animate.request', function () {
			console.log("Start my animate")
		});
		this.animate.bind('end.animate.request', function () {
			console.log("End my animate")
		});

		this.translations = $.merge(this.translations, this.basicTranslations);

		if (typeof loadTemplate != 'undefined') this.loadTemplate = loadTemplate;

		if (typeof callingWindow != 'undefined') this.callingWindow = callingWindow;

		this.updateInterval = new UpdateInterval(this.template);

		this._super();
	},
	/**
	 * Инициализация окна - книги исследований.
	 */
	initialize:function () {
		/**
		 * Обработчик события клика по полосе исследований для вызова окна исследования.
		 */
	},
	/**
	 * Показ отрендеренной книги-окна исследований.
	 */
	showRendering:function () {
		// Заполнение начальными данными
		var request = {url:Config.BASE_URL + "/research/index.json"};
		$(request).bind('complete', function (data) {
			delete(request)
		});
		var self = this;
		try {
			sync('load', request, {
				error:function (r) {
					console.log(r)
				},
				/**
				 * @param r {response}
				 */
				success:function (r) {
					if (typeof r.list_research == 'undefined') throw new Error(r.error_text);
					if (r.list_research.length === 0)
						throw new Error("Parameter from server `list_research must be array with count great 0`");

					self.translator.bindTranslation(
						self.translations,
						$.proxy(self.bindHandlerAndInitBook, self), {'response':r}
					);
                    redrawInterface();
				}
			});
		}
		catch (err) {
			console.log(err.message);
		}
	},
	/**
	 * Обработчик события клика кнопки "Изучить" или "Изучить быстро"
	 */
	bindHandlerLearn: function (lang)
	{
		// TODO: Конструкция $(this.template + " .description-buttons").on('click', "#learn_research", {module: this}, function (e) не работает!
		$("#learn_research").on('click', {module: this}, function (e)
		{
			e.preventDefault();
			e.data.module.handlersForTimerIntervalLearn(this);
			e.data.module.learn(false, this);
		});

		$("#learn_now_research").on('click', {module: this}, function (e)
		{
			e.preventDefault();
			e.data.module.learn(true, this);
		});
	},
	/**
	 * Обработчики перед предварительным выводом таймера и после завершения времени изучения по таймеру.
	 *
	 * @param item jQuery|string selector|Object
	 */
	handlersForTimerIntervalLearn: function(item)
	{
		$(item).on("prepare.interval", {module: this},function(e)
		{
			$(this).addClass('active');
			$(this).addClass('hidden');

			var btnCancelLearn = $(this).parent().find("#stop_learn_research")
			btnCancelLearn.removeClass("hidden");
			btnCancelLearn.on('click', {module:e.data.module}, function(e){
				console.log('cancel research');
				var requestCancel = {
					url:Config.BASE_URL + "/research/cancel_learn.json",
					attributes:{research_id:e.data.module.currentIdResearch}
				};
				$(requestCancel).bind('complete', function () {
					delete(requestCancel)
				});

				var optionsCancel = {
					error:function (response) {
						console.log(response);
					},
					success:function (response)
					{
						if (response.status == 1) {
							e.data.module.fillingRightSideResearch(null, e.data.module, e.data.module.langTranslate, e);
							e.data.module.updateInterval.stop();
						}
					},
					animate:false
				};

				sync('load', requestCancel, optionsCancel);
			});
		});

		$(item).on("finish.interval", {module:this}, function(e)
		{
			var requestLearn = {
				url:Config.BASE_URL + "/research/finish_learn.json",
				attributes:{research_id:e.data.module.currentIdResearch}
			};
			$(requestLearn).bind('complete', function () {
				delete(requestLearn)
			});

			var optionsLearn = {
				error:function (response) {
					console.log(response);
				},
				success:function (response)
				{
					if (response.status == 1) {
						e.data.module.fillingRightSideResearch(null, e.data.module, e.data.module.langTranslate, e);
					}
				},
				animate:false
			};

			sync('load', requestLearn, optionsLearn);
		});
	},
	learn: function(isNowLearn, activeItem)
	{
		var researchId = this.currentIdResearch;
		this.learnRequest(researchId, isNowLearn, lang, activeItem)
	},
	bindHandlerAndInitBook:function (data, lang)
	{
		var researchPage = $('.column-right.research-page');
		researchPage.find('.empty-book-text').removeClass('hidden').addClass('active');
		researchPage.find('.description-container').removeClass('active').addClass('hidden');

		/**
		 * Вызываем функцию для заполнения данными модального окна при открытии
		 */
		var initData = formedData.generateLeftDataForStandardization(data, lang);
		this.openBook(initData);

		/**
		 * Обработчик событий клика из списка слева для просмотра исследования отдельно в окне справа.
		 */
		$('#list_research').on('click', 'li', {module:this}, function (e) {

			researchPage.find('.empty-book-text').removeClass('active').addClass('hidden');
			researchPage.find('.description-container').removeClass('hidden').addClass('active');

			e.preventDefault();
			e.data.module.updateInterval.stop();
			e.data.module.langTranslate = lang;
			e.data.module.fillingRightSideResearch(this, e.data.module, lang, e);
		});

		this.bindHandlerLearn(lang);
	},
	/**
	 * Заполняем данными конкретного исследования правую сторону модального окна.
	 */
	fillingRightSideResearch:function (selector, module, lang, e)
	{
		//var researchId = $(selector).attr('data-research-id');
		var researchId = $(selector).attr('data-research-id') || module.currentIdResearch;
		var request = {url:Config.BASE_URL + "/research/properties.json", attributes:{research_id:researchId}};

		$(request).bind('complete', function () {
			delete(request)
		});

		var options = {
			error:function (response) {
				console.log(response)
			},
			success:function (response)
			{
				module.currentIdResearch = $(selector).data('research-id') || module.currentIdResearch;

                if (typeof response.properties.research_examined != 'undefined') {

                    $(module.template).find('.empty-book-text').removeClass('hidden').addClass('active').html(lang[response.properties.research_examined].message);
                    $(module.template).find('.description-container').removeClass('active').addClass('hidden');

                }else{

				var translatesResearch = e.data.module.translator.getLangFromCache('research');
				var fullRightData = formedData.generateRightDataForStandardization(
					response, $.extend(lang, translatesResearch)
				);

				e.data.module.rightPartRender(fullRightData);

				// Инициализация отсчёта времени, если изучение ещё в процессе
				if(fullRightData.research_status == 'research')
				{
					// При не законченном изучении кнопки изучить (#learn_research) нет в шаблоне
					var learn = $("#stop_learn_research");
					module.handlersForTimerIntervalLearn(learn);
					module.updateInterval.active(learn);
				}

				//Отображение блок кнопок изучить в зависимости от статуса исследования
				if (fullRightData.research_status == 'research') {
					$(module.template).find('.research-countdown').removeClass('hidden').addClass('active');
					//$(module.template + ' .description-buttons').addClass('hidden');
					$(module.template).find('.research-time').removeClass('active').addClass('hidden');
					//e.data.module.startResearchTimer(fullRightData.finish_time, 1);
				} else {
					$(module.template + '.research-countdown').removeClass('active').addClass('hidden');
					//$(module.template + '.description-buttons .learn-cost .research-time').addClass('active');
					//$(module.template).removeClass('active').addClass('hidden');
				}


				delete(response);
				delete(fullRightData);
				delete(lang);

				e.data.module.bindHandlerLearn(lang);
			}},
			animate:false

		};

		sync('load', request, options);

		delete(options);
		delete(researchId);

	},
	/**
	 * Заполняем данными левую сторону модального окна.
	 */
	fillingLeftSideResearch:function (lang)
	{
		var self = this;
		var request = {url:Config.BASE_URL + "/research/index.json"};
		$(request).bind('complete', function () {
			delete(request)
		});

		var options =
		{
			error:function (response) {console.log(response)},
			success:function (response)
			{
				var translatesResearch = self.translator.getLangFromCache('research');
				var fullLeftData = formedData.generateLeftDataForStandardization(
					{response: response}, $.extend(lang, translatesResearch)
				);

				self.leftPartRender(fullLeftData);
				self.showRendering();

				delete(response);
				delete(fullLeftData);
				delete(lang);
			},
			animate:false
		};

		sync('load', request, options);

		delete(options);
	},
	/**
	 * Обработка событий изучить и  изучить мгновенно.
	 * @param id
	 * @param isEventNowLearn
	 * @param lang
	 * @param activeItem
	 */
	learnRequest: function (id, isEventNowLearn, lang, activeItem)
	{
		var self = this;
		if(isEventNowLearn)
		{
			//Нажата кнопка изучить мгновенно
			var requestNowLearn = {url:Config.BASE_URL + "/research/immediately.json", attributes:{research_id:id}};
			$(requestNowLearn).bind('complete', function () {
				delete(requestNowLearn)
			});

			var optionsNowLearn = {
				error:function (response) {
					console.log(response);
				},
				success:function (response)
				{
					if (response.status == 1) self.fillingLeftSideResearch(lang);
				},
				animate:false
			};

			sync('load', requestNowLearn, optionsNowLearn);
		}
		else
		{
			//Нажата кнопка изучить
			var requestLearn = {url:Config.BASE_URL + "/research/slow.json", attributes:{research_id:id}};
			$(requestLearn).bind('complete', function () {
				delete(requestLearn)
			});

			var optionsLearn = {
				error:function (response) {
					console.log(response);
				},
				success:function (response)
				{
					if (response.status == 1) {
						self.updateInterval.active($(activeItem));
					}
				},
				animate:false
			};

			sync('load', requestLearn, optionsLearn);
		}
	}
});

var formedData = {};

/**
 * Формируем данные для шаблонизации левой части модального окна
 */
formedData.generateLeftDataForStandardization = function (data, lang)
{
	var response = (typeof data.response == "undefined") ? data : data.response;
	for (var i = 0; i < response.list_research.length; i++) {
		response.list_research[i].name_research = lang[response.list_research[i].name_research].name;
	}

	var res = {
		list_research:response.list_research,
		image:Config.IMAGE_URL
	};

	var fullLeftData = $.extend(res, lang);

	delete(lang);
	delete(res);
	delete(response);
	delete(data);

	return fullLeftData;
};

/**
 * Формируем данные для шаблонизации правой части модального окна
 */
formedData.generateRightDataForStandardization = function (data, lang) {
	var response = data;

	var firstRight = {
		name_research:lang[response.properties.name_research].name,
		description:lang[response.properties.name_research].description,
		current_level:parseInt(response.properties.level_upgrade -1)
	};

	var dateObject = new Date(0,0,0,0,0,response.properties.time_research,0);
	var hours = dateObject.getHours();
	var minutes = dateObject.getMinutes();
	var seconds = dateObject.getSeconds();

	var upgrade_list = formedData.getListUpgrade(response.properties.name_upgrade, lang);

	//Данные для правой стороны модального окна исследований
	var resultRight = {
		list_research:response.properties.list_research,
		first_properties:response.properties.first_properties,
		first:firstRight,
		upgrade:upgrade_list,
		next_level:parseInt(response.properties.level_upgrade),
		name_building:lang[response.properties.name_building].name,
		research_status:response.properties.research_status,
		finish_time:response.properties.finish_time,
		level_upgrade: response.properties.level_upgrade,
		current_level: response.properties.current_level,
		research_hours:hours,
		research_minutes:minutes,
		research_seconds:seconds,
		access_learn_full: false,
		access_learn: true,
		access_learn_now: true
	};

	var resource = {
		building_current_level:response.resource[0].building_current_level,
		building_next_level:response.resource[0].building_current_level + 1,
		getPriceAmber: function(){return (response.resource[1]) ? response.resource[1].price : 0;},
		list_research_resource: formedData.getListResource(response.resource, lang)
	};

	var isExistsAmberResource = false;
	// Флаги указания вывода кнопок взависимости от требуемых и доступных ресурсов
	for(res in resource.list_research_resource)
	{
		var r = resource.list_research_resource[res];

		if(parseInt(r.personage_resource_value) < parseInt(r.price))
		{
			if(r.image_name == "silver") resultRight.access_learn = false;
			if(r.image_name == "amber") resultRight.access_learn_now = false;
		}

		if(resultRight.access_learn == false && resultRight.access_learn_now == false)
			resultRight.access_learn_full = true;

		// Для кнопки "Изучить" блокируем вывод если изучение ещё происходит.
		if(r.image_name == "silver" && response.properties.research_status == "research")
			resultRight.access_learn = false;

		if(r.image_name == "amber") isExistsAmberResource = true;
	}

	// Если нет ресурса янтарь не выводим кнопку "Изучить мгновенно"
	if(isExistsAmberResource == false) resultRight.access_learn_now = false;

	// Блокирование кнопок взависимости от уровня библиотеки
	if(parseInt(response.properties.current_level) < parseInt(response.properties.level_upgrade))
	{
		resultRight.access_learn_full = true;
		resultRight.access_learn = false;
		resultRight.access_learn_now = false;
	}

	var fullRightData = $.extend(resultRight, resource, lang);

	delete(response);
	delete(upgrade_list);
	delete(resultRight);

	return fullRightData;

};

/**
 * Получаем список усовершенствований для правой стороны окна
 * @param response
 * @param lang
 */
formedData.getListUpgrade = function (response, lang) {

	var upgrade = [];
	var upgrade_result = response.split(',');

	for (var i = 0; i < upgrade_result.length; i++)
	{
		if(lang[upgrade_result[i]]) upgrade.push(lang[upgrade_result[i]].name);
	}

	return upgrade;
};

/**
 * Получаем список ресурсов и их значений для правой стороны окна
 *
 * @param response
 * @param lang
 */
formedData.getListResource = function (response, lang) {

	var resource_result = [];

	for (var i = 0; i < response.length; i++) {

		var resource = [];
		resource.name_resource = lang[response[i].name_resource].name;
        resource.image_name = response[i].name_resource;
		resource.personage_resource_value = response[i].personage_resource_value;
		resource.price = response[i].price;

		resource_result.push(resource);
	}

	return resource_result;
};