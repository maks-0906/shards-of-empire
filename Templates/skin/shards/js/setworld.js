$(document).ready(function () {
	gameExpWorlds.loadWorlds($);
	gameUnExpWorlds.loadWorlds($);
	//Получение cписка миров

	/*$('.news.span100').find('a').on('click', function(e){
	 e.preventDefault();
	 var world_id = $('.news.span100').find('a').index(this);


	 localStorage.setItem('world_id', world_id);
	 window.location='main.html';

	 });*/
});

/************************************************************************/
var gameExpWorlds = {
	getWorlds:Config.BASE_URL + "/personage/worlds_explored.json",
	name_world:0,
	congestion:0,
	id_personage:0,
	language:0,
	status:0,
	time_online:0,
	total_level:0,

	loadWorlds:function ($) {
		showOverlay();
		$.jsonp({
			url:gameExpWorlds.getWorlds,
			callback:'_jqjsp',
			callbackParameter:'callback',
			timeout:5000,
			data:{

			},

			success:function (json, textStatus, xOptions) {
				hideOverlay();
				console.log(json);
				$('#worlds-explored-info').children().remove();
				for (var id in json.worlds) {

					name_world = json.worlds[id].name_world,
					world_id = json.worlds[id].map_id,
						congestion = json.worlds[id].congestion,
						id_personage = json.worlds[id].id_personage,
						language = json.worlds[id].lang,
						status = json.worlds[id].status,
						time_online = json.worlds[id].time_online,
						total_level = json.worlds[id].total_level
                        name_dignity =  myModels.render.local(json.worlds[id].name_dignity, myModels.lang(), 'name')();
					thisTime = Math.round(new Date().getTime() / 1000.0)

					date = time_online,
						date1 = thisTime - date


					// Set the unit values in milliseconds.
					var msecPerMinute = 60;
					var msecPerHour = msecPerMinute * 60;
					var msecPerDay = msecPerHour * 24;
					var msecPerMons = msecPerDay * 30;

					// Set a date and get the milliseconds
					 var date = new Date('6/15/1990');
					 dateMsec = date.getTime();

					 // Set the date to January 1, at midnight, of the specified year.
					 date.setMonth(0);
					 date.setDate(1);
					 date.setHours(0, 0, 0, 0);
					 // Get the difference in milliseconds.
					 var interval = dateMsec - date.getTime();


					// Calculate how many days the interval contains. Subtract that
					// many days from the interval to determine the remainder.
					// var mons = Math.floor(date1 / msecPerMons );
					// date1 = date1 - (mons * msecPerMons );

					var days = Math.floor(date1 / msecPerDay);
					date1 = date1 - (days * msecPerDay );

					// Calculate the hours, minutes, and seconds.
					var hours = Math.floor(date1 / msecPerHour);
					date1 = date1 - (hours * msecPerHour );

					var minutes = Math.floor(date1 / msecPerMinute);
					date1 = date1 - (minutes * msecPerMinute );

					var seconds = Math.floor(date1 / 1000);

					// Display the result.
					console.log(/*mons+ " mons " +*/days + " days, " + hours + " hours, " + minutes + " minutes, " + seconds + " seconds.");

					//Output: 164 days, 23 hours, 0 minutes, 0 seconds.
					var dayss1 = days + 0;
					if(dayss1 == 0) {
						dayss1 = ' '
					};

					function num_ending(number) {
						var endings = ['дней назад', 'день назад', 'дня назад', 'вчера', 'сегодня'];
						var num100 = number % 100;
						var num10 = number % 10;
						if(number <= 0 && number < 1) {
							return endings[4];
						} else if(number > 0 && number < 2) {
							return endings[3];
						} else if(num100 >= 5 && num100 <= 20) {
							return endings[0];
						} else if(num10 == 0) {
							return endings[0];
						} else if(num10 == 1) {
							return endings[1];
						} else if(num10 >= 2 && num10 <= 4) {
							return endings[2];
						} else if(num10 >= 5 && num10 <= 9) {
							return endings[0];
						} else {
							return endings[2];
						}
					}

					//console.log('name_world:' + name_world, ' congestion:' + congestion, ' id_personage:' + id_personage, ' language:' + language, ' status:' + status, 'time_online:' + time_online, ' total_level:' + total_level);
                    name_dignity
                    $('#worlds-explored-info').append(
						'<li class="span100 world-item">' +
							'<a href="" id="' + world_id + '">' +
							'<span class="span40 location-name">' + name_world + '</span>' +
							'<span class="span40 location-date">{{LANG_LOCATIONS_LAST_VISIT}} ' + dayss1 +
							' ' + num_ending(days) + ' </span>' +
							'<span class="span20 location-other">{{LANG_LOCATIONS_LEVEL}}: ' + name_dignity + '</span>' +
							'</a>' +
							'</li>'
					);
				}

				//Меняем на нормальный текст
				$('.locale').each(function () {
					var el = $(this);
					el.html(Mustache.render(el.html(), lang));
				});

				//Обрабатываем клик
				$('#worlds-explored-info').on("click", "a", function(e) {
					e.preventDefault();
					var world_id = $(this).attr('id');
					console.log("ID: " + world_id);

					if(typeof world_id == "undefined")
						throw new Error("World ID not defined for selected world");

					sync('load', {url: Config.BASE_URL + "/personage/init.json", attributes: {world_id: world_id}},{
						error: function(r){ throw new Error('Bad request for `personage/init.json`');},
						success: function(r)
						{
							try
							{
								console.log('response personage' , r);
                                r.personage.cities = r.cities;
								myModels.user.init(r.personage);
								window.location='main.html';
							}
							catch(err)
							{
								console.log(err);
							}
						}
					});
				});
			},
			error:function (xOptions, textStatus) {
				console.log('gameExpWorlds:' + textStatus);
				showMessagePopup(13);
			}

		});
	}
};

var gameUnExpWorlds = {
	getWorlds:Config.BASE_URL + "/personage/worlds_unexplored.json",
	name_world:0,
	congestion:0,
	id_personage:0,
	language:0,
	status:0,
	time_online:0,
	total_level:0,

	loadWorlds:function ($) {
		showOverlay();
		$.jsonp({
			url:gameUnExpWorlds.getWorlds,
			callback:'_jqjsp',
			callbackParameter:'callback',
			timeout:5000,
			data:{

			},
			success:function (json, textStatus, xOptions) {
				hideOverlay();
				console.log(json);
				$('#worlds-unexplored-info').children().remove();
				for (var id in json.worlds)
				{
					world_id = json.worlds[id].map_id,
					id_personage = json.worlds[id].id_personage,
					name_world = json.worlds[id].name_world,
					language = json.worlds[id].lang,
					current_count_users = json.worlds[id].current_count_users,
					max_users = json.worlds[id].max_users,
					//time_online = json.worlds[1].time_online,
					congestion = Math.round((100 / max_users) * current_count_users),

					//console.log('name_world:'+name_world,' congestion:'+congestion, ' id_personage:'+id_personage, ' language:'+language, ' status:'+status, 'time_online:'+time_online, ' total_level:'+total_level);
					$('#worlds-unexplored-info').append(
						$('<li class="span100 world-item">').append(
							$('<a style="cursor: pointer" id="' + world_id + '">' +
								'<span class="span40 location-name">' + name_world + '</span>' +
								'<span class="span40 location-date">{{LANG_LOCATIONS_CONGESTION}} '
								+ congestion + '%</span><span class="span20 location-other">{{LANG_LOCATIONS_LANG}}: '
								+ language + '</span>' +
								'</a>'
							)
						)
					)
				}

				//Меняем на нормальный текст
				$('#worlds-unexplored-info').each(function () {
					var el = $(this);
					el.html(Mustache.render(el.html(), lang));
				});

				//Обрабатываем клик
				$('#worlds-unexplored-info').on("click", "a", function(e) {
					e.preventDefault();
					var world_id = $(this).attr('id');
					console.log("ID: " + world_id);

					localStorage.setItem('world_id', world_id);
					var createPersonage = new CreatePersonage;
					createPersonage.initAndShowPage();
					//window.location='locations.html';
				});
			},
			error:function (xOptions, textStatus) {
				console.log('gameUnExpWorlds:' + textStatus);
				showMessagePopup(13);
			}

		});
	}
}
/************************************************************************/   
