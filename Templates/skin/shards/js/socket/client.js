strings = {
	'connected': '[sys][time]%time%[/time]: Вы успешно соединились к сервером как [user]%name%[/user].[/sys]',
	'userJoined': '[sys][time]%time%[/time]: Пользователь [user]%name%[/user] присоединился к чату.[/sys]',
	'messageSent': '[out][time]%time%[/time]: [user]%name%[/user]: %text%[/out]',
	'messageReceived': '[in][time]%time%[/time]: [user]%name%[/user]: %text%[/in]',
	'userSplit': '[sys][time]%time%[/time]: Пользователь [user]%name%[/user] покинул чат.[/sys]'
};
window.onload = function() {
	/*var Chat = function(){
  		this.observer = new Observer({ url: 'http://46.249.52.227:8856/', socket:{ name:'message' } });
	};
    var draw = function(r){
		console.log("chat: ", r);
        if(r.type == 'message'&& r.data)	{

            /*document.querySelector('#log').innerHTML +=
				strings[r.data.event].replace(/\[([a-z]+)\]/g, '<span class="$1">').replace(/\[\/[a-z]+\]/g, '</span>')
					.replace(/\%time\%/, r.data.time).replace(/\%name\%/, r.data.name)
					.replace(/\%text\%/, unescape(r.data.text).replace('<', '&lt;').replace('>', '&gt;')) + '<br>';

            document.querySelector('#log').scrollTop = document.querySelector('#log').scrollHeight;
        }
    };

	var a = new Chat();
	draw.subscribe(a.observer);*/

	/*document.querySelector('#send').onclick = function() {
		a.observer.deliver(escape(document.querySelector('#input').value));
		document.querySelector('#input').value = '';
	};*/

//	var ResourcesPanel = function(){
//		this.observer = new Observer({ url: Config.pURL, socket:{ name:'updateResources' } });
//	};
//	var drawResources = function(r)
//	{
//		console.log("draw resources: ", r);
//		if(r.type == 'updateResources'&& r.data)
//		{
//			console.log(r.data);
//		}
//		else
//			console.log('response update resources bad');
//	};
//
//	var resourcePanel = new ResourcesPanel();
//	drawResources.subscribe(resourcePanel.observer);
};