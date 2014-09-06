class Dungeon < Sinatra::Application
	before '/character/*' do
		unless session[:user]
			redirect '/auth/login'
			return false
		end
	end


	get '/character/list' do
		haml :index, :locals => @viewdata.merge!({:content => :'character/list'})
	end

	get '/character/create' do
		haml :index, :locals => @viewdata.merge!({:content => :'character/create'})
	end

	get '/character/play/:name' do
		session[:character] = session[:user].characters[params[:name]]
		redirect '/game'
	end

	post '/character/create' do

		user = session[:user]
		
		user.add_character params[:CharName]
		
		user.characters[params[:CharName]].respawn
		
		redirect to('/character/list')
	end
end