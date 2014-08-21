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
	redirect '/game'
end

get '/game' do
	@character = session[:character]
	if @character.hp > 0 then
		haml :index, :locals => @viewdata.merge!({:content => :'game/index', :data => { :character => @character }})
	else
		haml :index, :locals => @viewdata.merge!({:content => :'game/dead', :data => { :character => @character }})
	end
end