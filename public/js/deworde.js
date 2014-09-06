var deWorde = new function(){

	this.socket = null;
	this.sendChatMessage = function(themessage)
	{
		packet = {type: 'speech', message: themessage}
		deWorde.socket.send(JSON.stringify(packet));
	};
	
	
	this.connect = function()
	{	
		if(deWorde.socket != null ) return;
		Socket = window.MozWebSocket || window.WebSocket;
		socket = new Socket('ws://' + location.hostname + ':4020/ws', "dungeon"),
		
				
		socket.addEventListener('open', function() {

			packet = {type:'authenticate', token: $('#ws_authentication_token').attr('value'), character: $('#ws_character').attr('value')}
			this.send(JSON.stringify(packet));
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
			
		};
		deWorde.socket = socket;
		
	};
	

	
	
}