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

get '/game' do
	haml :index, :locals => @viewdata.merge!({:content => :'game/index', :data => { :character => session[:character] }})
end