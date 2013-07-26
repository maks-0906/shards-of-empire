/**
 * Файл сордержит классы ядра приложения и общего назначения.
 *
 * @author Greg
 * @package core
 */

/**
 * Конфигурация всего приложения.
 * @type {Object}
 */
window.Config = {
	BASE_URL:  window.location.protocol + "//" + window.location.host,
	NAME_SKIN: "shards",
	CURRENT_LANG: 'ru',
	INTERVAL_DETECT_LOAD_TRANSLATION: 10,
	STATIC_URL: "http://shards.kiberland.com/map/",
	onChat: true,
	pPort: 8856
};
Config.SKIN_URL = Config.BASE_URL + "/Templates/skin/" + Config.NAME_SKIN;
Config.TEMPLATES_URL = Config.SKIN_URL + "/templates";
Config.IMAGE_URL = Config.SKIN_URL + "/img";
Config.LANG_SCRIPT_URL = Config.SKIN_URL + "/lang/" + Config.CURRENT_LANG;
// TODO: После тестирования убрать
Config.STATIC_URL = '46.249.52.227';
Config.pURL = Config.STATIC_URL + ":" + Config.pPort;

function FactoryLang()
{
	this.LANG_SCRIPT_URL = Config.LANG_SCRIPT_URL;

	this.lang = {};

	// Загрузка библиотеки общих переводов
	if(!this.lang['main'])
	{
		$.getScript(
			this.LANG_SCRIPT_URL + "/main.js",
			$.proxy(function(){
				this.lang['main'] = window['main'];
			}, this)
		);
	}
}

/**
 * Сборка первеводов и передача их в success (функция)
 * @param item
 * @param success
 * @param data
 */
FactoryLang.prototype.bindTranslation = function(item, success, data)
{
	/*$.ajaxSetup({
		async: true
	});*/

	if(typeof item == 'string')
	{
		if(!this.lang[item])
		{
			$.getScript(
				this.LANG_SCRIPT_URL + "/" + item + ".js",
				$.proxy(function(){
					this.lang[item] = $.extend(window[item], window['main']);
					success(data, this.lang[item]);
				}, this)
			);
		}
		else
			success(data, this.lang[item]);
	}
	else if(isArray(item))
	{
		var assemblerTranslation = {};

		var counter = {
			countTranslation:  item.length,
			countLoadFromCache: 0,
			countLoadFromServer: 0,
			isFinishLoad: function()
			{
				return ((this.countLoadFromCache + this.countLoadFromServer) == this.countTranslation);
			},
			incLoadFromCache: function(){this.countLoadFromCache += 1;},
			incLoadFromServer: function(){this.countLoadFromServer += 1;}
		};

		for(var i = 0; i < counter.countTranslation; i++)
		{
			if(this.lang[item[i]])
			{
				if(i === 0)
					assemblerTranslation = $.extend(this.lang[item[i]], window['main']);
				else
					assemblerTranslation = $.extend(assemblerTranslation,this.lang[item[i]]);

				counter.incLoadFromCache();
			}
			else
				FactoryLang.setLang(item[i], assemblerTranslation, this, counter);
		}

		var self = this;
		// Ожидаем загрузки всех переводов
		var interval = setInterval(function(e){
			// TODO: Добавить счётчик таймаут ожидания, если таймаут прошёл выводить окно уведомления о том что бы попробовать попозже и останавливать процесс.
			if(counter.isFinishLoad())
			{
				delete(counter);
				clearInterval(interval);
				success(data, assemblerTranslation);
				//console.log("Кэш переводов: ", self.lang);
			}
		}, Config.INTERVAL_DETECT_LOAD_TRANSLATION);
	}
	else
		throw new Error("Parameter `item` for bindLang not defined");
};

/**
 * Простая загрузка файла перевода.
 *
 * @param name {string}
 * @param assemblerTranslation {Object}
 * @param self {FactoryLang}
 * @param counter {Object}
 */
FactoryLang.setLang = function(name, assemblerTranslation, self, counter)
{
	$.getScript(
		Config.LANG_SCRIPT_URL + "/" + name + ".js",
		$.proxy(function()
		{
			assemblerTranslation = $.extend(assemblerTranslation, window[name]);
			counter.incLoadFromServer();
			// Кэширование перевода.
			self.lang[name] = window[name];
		}, this)
	);
};

/**
 * Метод для получения перевода из кэша.
 * @param name
 * @return {*}
 */
FactoryLang.prototype.getLangFromCache = function(name)
{
	if(this.lang[name] != "undefined")
		return this.lang[name];
	else
		return {};
};

try
{
	window.Lang = new FactoryLang();
}
catch(err){
	console.log(err);
}