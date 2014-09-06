socket_handler_message = lambda {|ws, message, session|
	return if session[:character] === nil
	
	Dimension::Message.send('<a href="/character/profile/' + session[:character].name + '">' + session[:character].name + '</a> said \'' +  Rack::Utils.escape_html(message['speech']) + '\'', session[:character].location.occupants, session[:character])
	puts 'message'
}
Dimension::SocketHandler.register('message', socket_handler_message) 

