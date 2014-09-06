module Dimension

	class SocketHandler
	
		@@handlers = ThreadSafe::Cache.new
		
		@@sockets = ThreadSafe::Array.new
		
		@@socket_sessions = ThreadSafe::Cache.new
		
		def self.set_session(socket, session)
			@@socket_sessions[socket][:character].detach_socket(socket) unless @@socket_sessions[socket] === nil or @@socket_sessions[socket][:character] === nil or @@socket_sessions[socket][:character] == session[:character]
			session[:character].attach_socket(socket) unless session[:character] === nil or (@@socket_sessions[socket] != nil and @@socket_sessions[socket][:character] == session[:character])
			@@socket_sessions[socket] = session
		end
		
		def self.sockets()
			return @@sockets
		end
		
		def self.add_socket(ws)
			@@sockets << ws
		end
		
		def self.remove_socket(ws)
			@@sockets.delete ws
			@@socket_sessions.delete ws
		end
	
		def self.register(type, proc)
			@@handlers[type] = proc
		end
		
		def self.handle(socket, message)
			begin
			
			
				case message["type"]
				when 'authenticate'
					character = Dimension::Character.find_by_name message['character']
					return if character === nil
					set_session(socket, {:character => character}) if character.owner.auth_token == message['token']
				when 'speech'
					return if @@socket_sessions[socket] === nil or @@socket_sessions[socket][:character] === nil
					character = @@socket_sessions[socket][:character] 
					Dimension::Message.send('<a href="/character/profile/' + character.name + '">' + character.name + '</a> said \'' +  Rack::Utils.escape_html(message['message']) + '\'', character.location.occupants, character)
				else
					socket.send({'type' => 'error', 'message' => 'Unknown message type!'}.to_json)
				end
			
			
				#type = message['type']
				#@@handlers[type].call(socket, message, @@socket_sessions[socket]) unless @@handlers[type] === nil
			rescue => e
				puts "ERROR #{e.inspect}"
				raise e
			end
		end
	end
end