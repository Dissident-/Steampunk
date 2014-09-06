Dimension::SocketHandler.register('authenticate', lambda {|ws, message, session|
	character = Dimension::Character.find message['character']
	return if character === nil
	Dimension::SocketHandler.set_session(ws, {:character => character}) if character.owner.auth_token == message['token']
})