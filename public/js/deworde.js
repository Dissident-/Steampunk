var deWorde = new function(){

	this.socket = null;
	this.running = false;
	this.authjson = null;
	this.charname = null;
	this.sendChatMessage = function(themessage)
	{
		packet = {type: 'speech', message: themessage}
		deWorde.socket.send(JSON.stringify(packet));
	};
	
	
	this.connect = function()
	{			
		Socket = window.MozWebSocket || window.WebSocket;
		socket = new Socket('ws://' + location.hostname + ':4020/ws', "dungeon"),
			
		socket.addEventListener('open', function() {

			deWorde.running = true;
			if(deWorde.authjson != null) deWorde.socket.send(deWorde.authjson);
			
		});

		socket.onerror = function(event) {
			this.close();
			deWorde.running = false;
			deWorde.socket = null;
			deWorde.connect();
		};
		socket.onmessage = function(event) {
			
			event = JSON.parse(event.data);

			if(event.type == 'multiple')
			{
				event.list.forEach(function(subevent){
					deWorde.parse(subevent);
				});
			}
			else
			{
				deWorde.parse(event);
			}

		};
		socket.onclose = function(event) {
			deWorde.running = false;
			deWorde.socket = null;
		};
		deWorde.socket = socket;
		
	};
	
	this.parse = function(event)
	{
		switch (event.type) {
			case 'log':
				$('#activity_log ul').prepend('<li>' + event.message + '</li>');
				break;
			case 'vitals':				
				['hp', 'ap', 'xp', 'level', 'cp'].forEach(function(stat){
					elem = $('#hud_player_vitals .' + stat);
					if(elem.attr('data-value') != event[stat])
					{
						elem.html(elem.attr('data-prefix') + event[stat] + elem.attr('data-suffix')).addClass('anim-unfade');
					}
				});
				setTimeout(function(){$('#hud_player_vitals .anim-unfade').removeClass('anim-unfade');},400);
				break;
			case 'occupants':
				if(event.list.length == 2) occmsg = 'You can see 1 other person at this location:'; else occmsg = 'You can see ' + (event.list.length - 1) + ' other people at this location:';
				$('.occupants-message').html(occmsg);
				$('.occupants-list li').remove();
				event.list.forEach(function(person)
				{
					if(person.name != deWorde.charname) $('.occupants-list').append('<li><a href="/character/profile/' + person.name + '" data-ajax="#page_content">' + person.name + '</a></li>');
				});
				target = $('.maptile[data-mapid=' + event.mapid + '] .other-occupants');
				if(event.list.length < 2) target.fadeTo("fast", 0); else target.fadeTo("fast", 100);
				break;
			case 'remoteoccupance':
			{
				target = $('.maptile[data-mapid=' + event.mapid + '] .other-occupants');
				if(event.occupied) target.fadeTo("fast", 100); else target.fadeTo("fast", 0);
			}
		} 
	}
	
	this.auth = function(authpacket){
		deWorde.charname = authpacket.character;
		authpacket = JSON.stringify(authpacket)	
		if(deWorde.socket != null && deWorde.running == true && authpacket != deWorde.authjson)
		{
			deWorde.socket.send(authpacket);
		}
		deWorde.authjson = authpacket;
	}
	
}

deWorde.connect();