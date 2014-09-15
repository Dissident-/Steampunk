class Dungeon < Sinatra::Application

	helpers do
		def render_game(stuff = Hash.new)
			@character = session[:character]
			if @character.hp > 0 then
				haml :index, :locals => @viewdata.merge!({:content => :'game/index', :data => { :character => @character, :minor_warnings => stuff[:minor_warnings], :minor_actions => stuff[:minor_actions] }})
			else
				haml :index, :locals => @viewdata.merge!({:content => :'game/dead', :data => { :character => @character, :minor_warnings => stuff[:minor_warnings], :minor_actions => stuff[:minor_actions] }})
			end
		end
	end

	before '/game' do
		unless session[:user]
			redirect '/auth/login'
			return false
		end
		unless session[:character]
			redirect '/character/list'
			return false
		end
	end

	get '/game/respawn' do
		session[:character].respawn
		render_game
	end

	get '/game' do
		render_game
	end

	post '/game/speak' do
		if session[:character].hp <= 0 then
			render_game(minor_warnings: "You can't talk while dead!")
		else
			session[:character].say params[:message]
			render_game
		end
	end

	get '/game/activated/:id' do
		character = session[:character]
		activated = Dimension::Effect.find params[:id].to_i
		if activated === nil then
			render_game(minor_warnings: "Skill not found!")
		else
			if character.compiled_effects[:activated].include? activated then
				result = activated.run character
				if result == :success then
					render_game
				else
					render_game(minor_warnings: result)
				end
			else
				render_game(minor_warnings: "You don't have that skill!")
			end
		end
	end
	
	get '/game/move/:id' do
		character = session[:character]
		dest = Dimension::Location.find params[:id].to_i
		result = character.attempt_move dest
		if result[:success] then
			render_game
		else
			render_game(minor_warnings: result[:message])
		end
		
	end
end