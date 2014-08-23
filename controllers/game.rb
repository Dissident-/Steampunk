helpers do
	def render_game()
		@character = session[:character]
		if @character.hp > 0 then
			haml :index, :locals => @viewdata.merge!({:content => :'game/index', :data => { :character => @character }})
		else
			haml :index, :locals => @viewdata.merge!({:content => :'game/dead', :data => { :character => @character }})
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
		haml :index, :locals => @viewdata.merge!({:content => :'game/index', :data => { :character => @character, :minor_warnings => "You can't talk while dead!" }})
	else
		Dimension::Message.send('<a href="/character/profile/' + session[:character].name + '">' + session[:character].name + '</a> said \'' +  Rack::Utils.escape_html(params[:speech]) + '\'', session[:character].location.occupants.values, session[:character])
		render_game
	end
end