/**
 * Файл содержит класс модуль управлением и отображением ресурсами.
 *
 * @author Greg
 * @package book
 */

/**
 * Класс модуль управлением и отображением ресурсами.
 * @constructor {Resource}
 */
Book("Resource", {
	template:'.resource-page',
	nameBook:'resource-page',
	nameTemplate:'resource.html',
	callingWindow:'.btn-resource',
	translations:['resources'],
	rightPart:'.resource-page.column-right',
	leftPart:'.resource-page.column-left',
	cacheTemplate:{
		left:"",
		right:""
	},
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

		this._super();
	},
	initialize:function () {
		/**
		 * Обработчик события клика по полосе ресурсов для вызова окна ресурсов.
		 */
	},
	setIdCity:function (idCity) {
		this.attributes['idCity'] = idCity;
	},
	showRendering:function () {
		// Заполнение начальными данными
		var request = {url:Config.BASE_URL + "/resource/info.json", attributes:this.attributes};
		$(request).bind('complete', function (data) {
			delete(request)
		});


		var self = this;

		try {
			sync('load', request, {
				error:function (r) {
					throw new Error(r);
				},
				/**
				 * @param r {response}
				 */
				success:function (r) {
					self.translator.bindTranslation(
						self.translations,
						$.proxy(self.bindHandlerAndInitBook, self), {'response':r}
					);
				}
			});
		}
		catch (err) {
			console.log(err.message);
		}
	},
	bindHandlerAndInitBook:function (data, lang) {
		var fullData = $.extend(data.response, lang);

		var response = data.response;
		var resources_personage = [];
		var resources_basic = [];
		var resources_special = [];

		console.log("данные от сервера", response);

		for (var i = 0; i < response.info.length; i++) {

			var resource = response.info[i];

			if (resource.type == 'personage') {
				resource.name = lang[resource.name_resource].name;
				resource.id = resource.id_resource;
				resources_personage.push(resource);
			}
			else if (resource.type == 'basic') {
				resource.name = lang[resource.name_resource].name;
				resource.id = resource.id_resource;
				resources_basic.push(resource);
			}
			else if (resource.type == 'special') {
				resource.name = lang[resource.name_resource].name;
				resource.id = resource.id_resource;
				resources_special.push(resource);
			}
			else
				throw new Error('Есть не перехваченные типы ресурсов!');
		}

		closers.hideClassEmptyBookText();
		closers.hideRightSideModalWindow();

		var res = {
			city_name:response.info[0].city_name,
			population:response.info[0].population,
			population_influx:response.info[0].population_influx,
			population_outflow:response.info[0].population_outflow,
			free_people:response.info[0].free_people,
			growth:response.info[0].growth,
			happiness:response.info[0].happiness,
			tax:response.info[0].tax,
			faith:response.info[0].faith,
			crime:response.info[0].crime,
			number_epidemic:response.info[0].number_epidemic,
			list_resource_personage:resources_personage,
			list_resource_basic:resources_basic,
			list_resource_special:resources_special
		};

		var fullData = $.extend(res, lang);
		console.log("full data -> ", fullData);
		this.openBook(fullData);

		delete(res);
		delete(fullData);
		delete(data);

		/**
		 * Обработчик событий клика из списка слева для просмотра ресурсов отдельно в окне справа.
		 */
		$(".resource-table").on("click", "td", {module:this}, function (e) {
			e.preventDefault();
			e.data.module.fillingRightSideResources(this, lang, e);
		});

	},
	/**
	 * Заполняем данными конкретного ресурса правую сторону модального окна.
	 */
	fillingRightSideResources:function (selector, lang, e) {

		var idResource = $(selector).attr('data-resource-id');
		var request = {url:Config.BASE_URL + "/resource/properties.json", attributes:{id_resource:idResource}};

		$(request).bind('complete', function () {
			delete(request)
		});

		var options = {
			error:function (response) {
				console.log(response)
			},
			success:function (response) {
				var resource = response;
				console.log('concrete_resource', resource);



				var total_number_resources_city = resource.properties[0].total_number_resource_city;
				var name_resource = resource.properties[0].name_resource;
				var closersCell = false;

				//Данные для правой стороны модального окна ресурсов
				var resultRight = {
					resource_name:lang[resource.properties[0].name_resource].name,
					resource_description:lang[resource.properties[0].name_resource].description,
					name_resource:resource.properties[0].name_resource,
					total_number_resources:resource.properties[0].total_number_resources,
					income:response.properties[0].income,
					resource_consumption:response.properties[0].resource_consumption,
					in_total_resources:response.properties[0].income - response.properties[0].resource_consumption
				};

				if (typeof total_number_resources_city != 'undefined') {
					resultRight.total_number_resources_city = total_number_resources_city;
				} else {
					closersCell = true;
				}

				var fullRightData = $.extend(resultRight, lang);
				e.data.module.rightPartRender(fullRightData, function () {
				});

				if (closersCell === true) {
					closers.hideStringResourceInCity();
					closers.hideClassResourceInRome();
				} else {
					closers.openStringResourceInCity();
				}

				if (name_resource == 'amber') {
					closers.hideBlockOfInformationAboutResource();
				}

				if (name_resource == 'blessing') {
					closers.hideBlockOfInformationAboutResource();
				}

				delete(resource);
				delete(resultRight);
				delete(fullRightData);
				delete(closersCell);
			    delete(name_resource);
				delete(lang);
			},
			// Подключение свое анимации
			// animate: e.data.module.animate
			// Отключение анимации полностью
			animate:false
		};

		sync('load', request, options);

		delete(options);
		delete(idResource);
	}
});

var closers = {};
closers.hideBlockOfInformationAboutResource = function () {
	$('.resource-information').addClass('hidden');
}

closers.hideStringResourceInCity = function () {
	$('.resource-in-city').addClass('hidden');
}

closers.openStringResourceInCity = function () {
	$('.resource-in-city.hidden').removeClass('hidden');
}

closers.hideClassEmptyBookText = function () {
	$('.empty-book-text.hidden').removeClass('hidden');
}

closers.hideRightSideModalWindow = function () {
	$('.description-container.scroll-content.active').removeClass('active').addClass('hidden');
}

closers.hideClassResourceInRome = function () {
	$('.resource-in-rome').addClass('hidden');
}

