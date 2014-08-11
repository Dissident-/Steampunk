
get '/character/list' do
	haml :index, :locals => @viewdata.merge!({:content => :'character/list'})
end

get '/character/create' do
	haml :index, :locals => @viewdata.merge!({:content => :'character/create'})
end

post '/character/create' do

	user = Dimension::Account.find session[:username]
	
	user.add_character params[:CharName]
	
	redirect to('/character/list')
end