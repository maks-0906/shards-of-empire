/**
 * Файл содержит базовый класс общих функциональностей и свойств книги.
 *
 * @author Greg
 * @package book
 *
 * Исследования +
 Ресурсы +
 Здания основные +
 Здания ресурсные +
 Персонаж -
 Квесты +
 Союзы +
 Справочник +
 Почта +
 Модераторская +

 артефакты, янтарь, болезни, отправка юнитов, меню - это все всплывающие по типу создания персонажа
 центральная часть - города, карта, бои персонажа
 */

/**
 * Базовый класс общих функциональностей и свойств книги.
 * @constructor {Book}
 */
$.Class("Book",
{
	/**
	 * Флаг инициализации первоначального рендеринга окна книги.
	 */
	initRender: false,
	/**
	 * Флаг, указывающий о включении метода подгрузки шаблона с сервера.
	 * Методы рендеринга могут и не использовать этот функционал. При этом потребуется реализовывать его вручную
	 * в наследуемых классах.
	 */
	loadTemplate: false,
	/**
	 * Селектор, который является инициатором события открытия окна.
	 */
	callingWindow: '',
	/**
	 * Базовый URL
	 */
	url: Config.BASE_URL,
	/**
	 * Контейнер, в который загружается шаблон, если включена подгрузка и реализован механизм её в наследуемых классах.
	 */
	containerTemplate: '#message-box',
	/**
	 * Селектор, содержащий полностью весь html код шаблона.
	 */
	template: '',
	/**
	 * Имя шаблона, которое используется для открытия книги. В основном совпадает с this.template
	 */
	nameBook: '',
	/**
	 * URL к каталогу с шаблонами.
	 */
	urlTemplate: Config.TEMPLATES_URL,
	/**
	 * Имя, подгружаемого шаблона.
	 */
	nameTemplate: '',
	/**
	 * Основные файлы перевода, которые требуются для всех книг.
	 */
	basicTranslations: ['main'],
	/**
	 * Фабрика переводов.
	 */
	translator: Lang,
	/**
	 * Контейнер аттрибутов (свойств) модели.
	 * Это свойства, которые присоединяются к запросу на сервер.
	 */
	attributes: {},
	/**
	 * Название селектора контейнера правой части книги.
	 */
	rightPart: '',
	/**
	 * Название селектора контейнера левой части книги.
	 */
	leftPart: '',
	/**
	 * Кэш шаблона
	 */
	cacheTemplate: {
		left: "",
		right: ""
	},
	/**
	 * Объект анимации
	 */
	animate: $({}),
	/**
	 * Конструктор модуля.
	 * @param name
	 */
	init: function(name) {
		this.cacheTemplate.right = $(this.rightPart).html();
		this.cacheTemplate.left = $(this.leftPart).html();
	},
	/**
	 * Метод подгрузки и установки шаблона.
	 * @param success {Function}
	 * @param data {Object}
	 */
	setTemplate: function(success, data)
	{
		if($(this.template).length === 0 && this.loadTemplate === true)
		{
			$.get(this.urlTemplate + "/" + this.nameTemplate, function(data) {
				$(this.containerTemplate).prepend(data);
				redrawInterface();
				if(typeof success == 'function') success({data: data});
			});
		}
		else
			if(typeof success == 'function') success({data: data});
	},
	/**
	 * Открытие книги.
	 * @param data {Object}
	 */
	openBook: function(data)
	{
		$('.book-column').hide();
		showPopup('book');
		console.log("pagename ->", this.nameBook);
		var page = (typeof this.nameBook == 'string') ? $('.' + this.nameBook) : this.nameBook;
		page.each(function()
		{
			var el = $(this);
			el.html(Mustache.render(el.html(), data));
		});

		page.show().find('.scroll-bar-wrap').show();
		scrollInit(page.find('.scroll-bar-wrap'));
	},
	/**
	 * Рендеринг правой части книги.
	 * @param data
	 * @param garbage {Function} вызов уборщика при повторном рендеринге (удаляются обработчики, не нужные ссылки на DOM)
	 */
	rightPartRender: function(data, garbage)
	{
		console.log("render right part");
		if(garbage instanceof  Function) garbage();
		var part = $(this.rightPart);
		//console.log("HTML шаблон правой части:   ", this.cacheTemplate.right);
		part.html(Mustache.render(this.cacheTemplate.right, data));
		scrollInit(part.find('.scroll-bar-wrap'));
	},
	/**
	 * Рендеринг левой части книги.
	 * @param data
	 * @param garbage {Function} вызов уборщика при повторном рендеринге (удаляются обработчики, не нужные ссылки на DOM)
	 */
	leftPartRender: function(data, garbage)
	{
		console.log("render left part");
		if(garbage instanceof  Function) garbage();
		var part = $(this.leftPart);
		part.html(Mustache.render(this.cacheTemplate.left, data));
		scrollInit(part.find('.scroll-bar-wrap'));
	},
	/**
	 * Связывание обработчиков и инициализация книги.
	 * @param data
	 * @param lang
	 */
	bindHandlerAndInitBook: function(data, lang){}
});