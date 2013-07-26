/**
 * Файл содержит класс модуль управлением и отображением окна персонажа.
 *
 * @author Greg
 * @package book
 */

/**
 * Класс модуль управлением и отображением окна персонажа.
 * @constructor {Personage}
 */
Book("Personage", {
	template: '',
	nameBook: '',
	callingWindow: '',
	translations: [],
	cacheTemplate: {
		left: "",
		right: ""
	},
    init : function(callingWindow, loadTemplate)
	{
		this.translations = $.merge(this.translations, this.basicTranslations);

		if(typeof loadTemplate != 'undefined') this.loadTemplate = loadTemplate;

		if(typeof callingWindow != 'undefined') this.callingWindow = callingWindow;
		this._super();
    },
	/**
	 * Инициализация модуля.
	 * @param data
	 */
	initialize: function(data){
		/**
		 * Обработчик события клика по кнопке вызова окна персонажа.
		 */
		$(this.callingWindow).on('click', $.proxy(this.showRendering, this));
	},
	showRendering: function()
	{
		// Заполнение начальными данными
		var request = {url: Config.BASE_URL + "/personage/info.json", attributes: this.attributes};
		$(request).bind('complete', function(data){delete(request)});

		var self = this;

		try
		{
			sync('load', request, {
				error: function(r){ throw new Error(r); },
				/**
				 * @param r {response}
				 */
				success: function(r)
				{
					//console.log(r);
					/*if(typeof r.list_research == 'undefined') throw new Error(r.error_text);
					if(r.list_research.length === 0)
						throw new Error("Parameter from server `list_research must be array with count great 0`");*/

					//self.setTemplate(function(data){
						self.translator.bindTranslation(
							self.translations,
							$.proxy(self.bindHandlerAndInitBook, self), {'response': r}
						);
					//});
				}
			});
		}
		catch(err)
		{
			console.log(err.message);
		}
	},
	bindHandlerAndInitBook: function(data, lang)
	{
		// TEST DATA
		data.response = {};
		var fullData  = $.extend(data.response, lang);
		var test = "scacsad";

		fullData.personageResource = function(data){console.log('inner ---> ', test); return "  ssadvd";};
		fullData.basicResource = function(html){return html;};

		console.log("full data -> ", $.extend(data.response, lang));
		this.openBook(lang);
	}
});