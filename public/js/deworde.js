var deWorde = new function(){

	this.socket = null;
	this.running = false;
	this.authjson = null;
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

		};
		socket.onmessage = function(event) {
			
			event = JSON.parse(event.data);

			switch (event.type) {
				case 'log':
				$('#activity_log ul').prepend('<li>' + event.message + '</li>');
			} 

		};
		socket.onclose = function(event) {
			deWorde.running = false;
			deWorde.socket = null;
		};
		deWorde.socket = socket;
		
	};
	
	this.auth = function(authpacket){
		authpacket = JSON.stringify(authpacket)	
		if(deWorde.socket != null && deWorde.running == true && authpacket != deWorde.authjson)
		{
			deWorde.socket.send(authpacket);
		}
		deWorde.authjson = authpacket;
	}
	
}

deWorde.connect();