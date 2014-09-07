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

	get '/game/move/:id' do
		@character = session[:character]
		@source = @character.location
		@dest = Dimension::Location.find params[:id].to_i
		if @dest === nil or @source === nil then
			render_game(minor_warnings: "You can't move to or from nowhere!")
		else
			if @character.ap < 1 then
				render_game(minor_warnings: "You are too tired to move!")
			else
				if @source.plane == @dest.plane and (((@source.x - @dest.x).abs < 2 and (@source.y - @dest.y).abs < 2 and @source.z - @dest.z == 0) or (@source.x - @dest.x == 0 and @source.y - @dest.y == 0 and (@source.z - @dest.z).abs == 1)) then
					@character.ap = @character.ap - 1
					@character.move @dest
					render_game
				else
					render_game(minor_warnings: "You can only move to adjacent locations!")
				end
			end
		end
	end
end