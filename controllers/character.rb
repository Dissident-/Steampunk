
get '/character/list' do
	haml :index, :locals => {:data => {:user => Dimension::Account.find(session[:username])}, :content => :'character/list', :ajax => AJAX::Templating.new(env, params)}
end

get '/character/create' do
	haml :index, :locals => {:data => {:user => Dimension::Account.find(session[:username])}, :content => :'character/create', :ajax => AJAX::Templating.new(env, params)}
end

post '/character/create' do

	user = Dimension::Account.find(session[:username])
	
	user.add_character params[:CharName]
	
	redirect to('/character/list')
end