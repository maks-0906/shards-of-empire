/**
 * Description content file
 *
 * @author Greg
 * @package
 */
function assert(value, msg) {
	if(!value) throw (msg || (value + " не равно true"));
}

function assertEqual(val1, val2, msg) {
	if(val1 !== val2) throw (msg || (val1 + " не равно " + val2));
}

function isArray(x){ return typeof(x)=='object' && typeof(x.length)=='number' }

//Удаление куки по имени
function DelCookie (name)
{
  	document.cookie = name + "=" + ";path=/; expires=Mon, 02-Jan-2005 00:00:00 GMT";
}

//Ещё одна ф-ция необходимая для работы
//Получение позиции символа
function strpos( haystack, needle, offset)
{
    var i = haystack.indexOf( needle, offset );
    return i >= 0 ? i : false;
}

/**
 *
 * @param mixed_value
 * @returns {*}
 */
function serialize (mixed_value) {
  // http://kevin.vanzonneveld.net
  // +   original by: Arpad Ray (mailto:arpad@php.net)
  // +   improved by: Dino
  // +   bugfixed by: Andrej Pavlovic
  // +   bugfixed by: Garagoth
  // +      input by: DtTvB (http://dt.in.th/2008-09-16.string-length-in-bytes.html)
  // +   bugfixed by: Russell Walker (http://www.nbill.co.uk/)
  // +   bugfixed by: Jamie Beck (http://www.terabit.ca/)
  // +      input by: Martin (http://www.erlenwiese.de/)
  // +   bugfixed by: Kevin van Zonneveld (http://kevin.vanzonneveld.net/)
  // +   improved by: Le Torbi (http://www.letorbi.de/)
  // +   improved by: Kevin van Zonneveld (http://kevin.vanzonneveld.net/)
  // +   bugfixed by: Ben (http://benblume.co.uk/)
  // %          note: We feel the main purpose of this function should be to ease the transport of data between php & js
  // %          note: Aiming for PHP-compatibility, we have to translate objects to arrays
  // *     example 1: serialize(['Kevin', 'van', 'Zonneveld']);
  // *     returns 1: 'a:3:{i:0;s:5:"Kevin";i:1;s:3:"van";i:2;s:9:"Zonneveld";}'
  // *     example 2: serialize({firstName: 'Kevin', midName: 'van', surName: 'Zonneveld'});
  // *     returns 2: 'a:3:{s:9:"firstName";s:5:"Kevin";s:7:"midName";s:3:"van";s:7:"surName";s:9:"Zonneveld";}'
  var val, key, okey,
    ktype = '', vals = '', count = 0,
    _utf8Size = function (str) {
      var size = 0,
        i = 0,
        l = str.length,
        code = '';
      for (i = 0; i < l; i++) {
        code = str.charCodeAt(i);
        if (code < 0x0080) {
          size += 1;
        }
        else if (code < 0x0800) {
          size += 2;
        }
        else {
          size += 3;
        }
      }
      return size;
    },
    _getType = function (inp) {
      var match, key, cons, types, type = typeof inp;

      if (type === 'object' && !inp) {
        return 'null';
      }
      if (type === 'object') {
        if (!inp.constructor) {
          return 'object';
        }
        cons = inp.constructor.toString();
        match = cons.match(/(\w+)\(/);
        if (match) {
          cons = match[1].toLowerCase();
        }
        types = ['boolean', 'number', 'string', 'array'];
        for (key in types) {
          if (cons == types[key]) {
            type = types[key];
            break;
          }
        }
      }
      return type;
    },
    type = _getType(mixed_value)
  ;

  switch (type) {
    case 'function':
      val = '';
      break;
    case 'boolean':
      val = 'b:' + (mixed_value ? '1' : '0');
      break;
    case 'number':
      val = (Math.round(mixed_value) == mixed_value ? 'i' : 'd') + ':' + mixed_value;
      break;
    case 'string':
      val = 's:' + _utf8Size(mixed_value) + ':"' + mixed_value + '"';
      break;
    case 'array': case 'object':
      val = 'a';
  /*
        if (type == 'object') {
          var objname = mixed_value.constructor.toString().match(/(\w+)\(\)/);
          if (objname == undefined) {
            return;
          }
          objname[1] = this.serialize(objname[1]);
          val = 'O' + objname[1].substring(1, objname[1].length - 1);
        }
        */

      for (key in mixed_value) {
        if (mixed_value.hasOwnProperty(key)) {
          ktype = _getType(mixed_value[key]);
          if (ktype === 'function') {
            continue;
          }

          okey = (key.match(/^[0-9]+$/) ? parseInt(key, 10) : key);
          vals += this.serialize(okey) + this.serialize(mixed_value[key]);
          count++;
        }
      }
      val += ':' + count + ':{' + vals + '}';
      break;
    case 'undefined':
      // Fall-through
    default:
      // if the JS object has a property which contains a null value, the string cannot be unserialized by PHP
      val = 'N';
      break;
  }
  if (type !== 'object' && type !== 'array') {
    val += ';';
  }
  return val;
}

function unserialize (data) {
  // http://kevin.vanzonneveld.net
  // +     original by: Arpad Ray (mailto:arpad@php.net)
  // +     improved by: Pedro Tainha (http://www.pedrotainha.com)
  // +     bugfixed by: dptr1988
  // +      revised by: d3x
  // +     improved by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
  // +        input by: Brett Zamir (http://brett-zamir.me)
  // +     improved by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
  // +     improved by: Chris
  // +     improved by: James
  // +        input by: Martin (http://www.erlenwiese.de/)
  // +     bugfixed by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
  // +     improved by: Le Torbi
  // +     input by: kilops
  // +     bugfixed by: Brett Zamir (http://brett-zamir.me)
  // +      input by: Jaroslaw Czarniak
  // %            note: We feel the main purpose of this function should be to ease the transport of data between php & js
  // %            note: Aiming for PHP-compatibility, we have to translate objects to arrays
  // *       example 1: unserialize('a:3:{i:0;s:5:"Kevin";i:1;s:3:"van";i:2;s:9:"Zonneveld";}');
  // *       returns 1: ['Kevin', 'van', 'Zonneveld']
  // *       example 2: unserialize('a:3:{s:9:"firstName";s:5:"Kevin";s:7:"midName";s:3:"van";s:7:"surName";s:9:"Zonneveld";}');
  // *       returns 2: {firstName: 'Kevin', midName: 'van', surName: 'Zonneveld'}
  var that = this,
    utf8Overhead = function (chr) {
      // http://phpjs.org/functions/unserialize:571#comment_95906
      var code = chr.charCodeAt(0);
      if (code < 0x0080) {
        return 0;
      }
      if (code < 0x0800) {
        return 1;
      }
      return 2;
    },
    error = function (type, msg, filename, line) {
      throw new that.window[type](msg, filename, line);
    },
    read_until = function (data, offset, stopchr) {
      var i = 2, buf = [], chr = data.slice(offset, offset + 1);

      while (chr != stopchr) {
        if ((i + offset) > data.length) {
          error('Error', 'Invalid');
            showMessagePopup('Error', 'Invalid');
        }
        buf.push(chr);
        chr = data.slice(offset + (i - 1), offset + i);
        i += 1;
      }
      return [buf.length, buf.join('')];
    },
    read_chrs = function (data, offset, length) {
      var i, chr, buf;

      buf = [];
      for (i = 0; i < length; i++) {
        chr = data.slice(offset + (i - 1), offset + i);
        buf.push(chr);
        length -= utf8Overhead(chr);
      }
      return [buf.length, buf.join('')];
    },
    _unserialize = function (data, offset) {
      var dtype, dataoffset, keyandchrs, keys,
        readdata, readData, ccount, stringlength,
        i, key, kprops, kchrs, vprops, vchrs, value,
        chrs = 0,
        typeconvert = function (x) {
          return x;
        };

      if (!offset) {
        offset = 0;
      }
      dtype = (data.slice(offset, offset + 1)).toLowerCase();

      dataoffset = offset + 2;

      switch (dtype) {
        case 'i':
          typeconvert = function (x) {
            return parseInt(x, 10);
          };
          readData = read_until(data, dataoffset, ';');
          chrs = readData[0];
          readdata = readData[1];
          dataoffset += chrs + 1;
          break;
        case 'b':
          typeconvert = function (x) {
            return parseInt(x, 10) !== 0;
          };
          readData = read_until(data, dataoffset, ';');
          chrs = readData[0];
          readdata = readData[1];
          dataoffset += chrs + 1;
          break;
        case 'd':
          typeconvert = function (x) {
            return parseFloat(x);
          };
          readData = read_until(data, dataoffset, ';');
          chrs = readData[0];
          readdata = readData[1];
          dataoffset += chrs + 1;
          break;
        case 'n':
          readdata = null;
          break;
        case 's':
          ccount = read_until(data, dataoffset, ':');
          chrs = ccount[0];
          stringlength = ccount[1];
          dataoffset += chrs + 2;

          readData = read_chrs(data, dataoffset + 1, parseInt(stringlength, 10));
          chrs = readData[0];
          readdata = readData[1];
          dataoffset += chrs + 2;
          if (chrs != parseInt(stringlength, 10) && chrs != readdata.length) {
            error('SyntaxError', 'String length mismatch');
              showMessagePopup('SyntaxError', 'String length mismatch');
          }
          break;
        case 'a':
          readdata = {};

          keyandchrs = read_until(data, dataoffset, ':');
          chrs = keyandchrs[0];
          keys = keyandchrs[1];
          dataoffset += chrs + 2;

          for (i = 0; i < parseInt(keys, 10); i++) {
            kprops = _unserialize(data, dataoffset);
            kchrs = kprops[1];
            key = kprops[2];
            dataoffset += kchrs;

            vprops = _unserialize(data, dataoffset);
            vchrs = vprops[1];
            value = vprops[2];
            dataoffset += vchrs;

            readdata[key] = value;
          }

          dataoffset += 1;
          break;
        default:
          error('SyntaxError', 'Unknown / Unhandled data type(s): ' + dtype);
            showMessagePopup('SyntaxError', 'Unknown / Unhandled data type(s): ' + dtype);
          break;
      }
      return [dtype, dataoffset - offset, typeconvert(readdata)];
    }
  ;

  return _unserialize((data + ''), 0)[2];
}



/**
 *
 * @param sessionDelete
 */
function fullClearCookie(sessionDelete)
{
    allcoockies = document.cookie.substring(0, document.cookie.length)+';';
    while (allcoockies)
    {
        var spos = strpos(allcoockies, ';',0);
        var val = allcoockies.substr(0, spos);
        allcoockies = allcoockies.substr(spos+2, allcoockies.length);
        coockie_param = val.substr(0, strpos(val, '=',0));

		if(sessionDelete)
			DelCookie (coockie_param);
		else
			if (coockie_param.toUpperCase() != 'PHPSESSID')
			{
				DelCookie (coockie_param);
			}
    }
}

function isPropertyExists(property) {
	return ((typeof 'undefined' != property) && (null != property))
}

/**
 * Показ анимации при отправке запроса на сервер
 * @param options
 */
function showAnimateRequest(options)
{
	// Включение анимации по умолчанию, если не была определена опция.
	if(typeof options != "undefined" && typeof options.animate == "undefined") options.animate = true;

	if(typeof options.animate != "undefined" && options.animate instanceof jQuery)
		options.animate.trigger('start.animate.request');
	else if(typeof options == "undefined" || options.animate === true)
		showOverlay();
}

/**
 * Скрытие анимации запроса.
 */
function hideAnimateRequest(options)
{
	if(typeof options.animate != "undefined" && options.animate instanceof jQuery)
		options.animate.trigger('end.animate.request');
	//if(typeof options == "undefined" || options.animate === true)
		//hideOverlay();
}

/**
 * Переопределение метода запроса (синхронизации) с сервером.
 *
 * @param method {string}
 * @param model {Object}
 * @param options {Object}
 */
window.sync = function (method, model, options)
{
	var data = prepareRequestData(method, model, options);
	showAnimateRequest(options);
	var xhr =  $.jsonp(
		{
			url: (options.url) ? options.url : model.url,
			callback: '_jqjsp',
			callbackParameter: 'callback',
			timeout: 15000,
			data: data,
	        success: function(json, textStatus, xOptions)
			{
				hideAnimateRequest(options);
				processingErrorStatus(json);

				if(typeof json.error === 'string')
				{
					console.log("Была получена ошибка от сервера 'Error'");

					if(json.code && json.code == '403')
					{
						fullClearCookie(true);
						window.location.href = '/';
					}

					$(model).trigger('error', json.error);
					if(typeof options.error == 'function') options.error(json);
				}
				else if(json.notice)
				{
					// Если есть вывод уведомлений от сервера возможен перехват
					if(typeof options.notice == 'function') options.notice(json.error);
				}
				else
				{
					// Ответ - записи, либо данные
					console.log("Response object `data`");
					if(typeof options.success == 'function') options.success(json);

					$(model).trigger((method == 'save') ? 'save' : 'load', json);
					$(model).trigger('read');
					$(model).trigger('complete');
					// TODO: ??? Нужно ли событие может только потребуется complete и всё
					$(model).trigger('success');
				}
	        },
	        error: function(xOptions, textStatus)
			{
				hideOverlay();
				if(typeof options.error == 'function') options.error(textStatus);
				$(model).trigger('error', textStatus);
	          	console.log(method + ':'+textStatus);
                showMessagePopup('error',textStatus);
	        }
	  });

	// Сохраняем ссылку на запрос в модель
	model.loading = {
		method: method,
		xhr: xhr
	};

	/**
	 * Подготовка данных для отправки на сервер.
	 *
	 * @param method {string}
	 * @param model {Object}
	 * @param options {Object}
	 * @return {Object}
	 */
	function prepareRequestData(method, model, options) {
		var data = {};
		if(model.attributes) $.extend(data, model.attributes);
		if(!options.data) options.data = {};
		$.extend(data, options.data);

		var cleanData = {};
		$.each(data, function (i, value) {
			if(typeof data[i] != 'function') cleanData[i] = value;
		});

		return cleanData;
	}

	/**
	 * Обработка статусов ошибок от сервера.
	 * @param r
	 */
	function processingErrorStatus(r)
	{
		if(r.status == '0') throw new Error(r.error_text);

		// Персонаж отсутствует
		if(r.status == '16')
			window.location.href = Config.SKIN_URL + "/locations.html";

		// Персонаж уже существует
		if(r.status == '13') throw new Error(r.error_text);

		// Не хватает ресурсов
		if(r.status == '17')
			showMessagePopup(false, "Не хватает ресурсов!");
	}
};