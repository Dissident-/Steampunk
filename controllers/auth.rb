
get '/auth/login' do
	haml :index, :locals => {:data => {:user => Dimension::Account.find(session[:username])}, :content => :'auth/login', :ajax => AJAX::Templating.new(env, params)}
end


post '/auth/login' do
	user = Dimension::Account.find(params[:Username])
	if user then
		session[:username] = user.username
		redirect to('/')
	else
		redirect back
	end
end


get '/auth/register' do
	haml :index, :locals => {:data => {:user => Dimension::Account.find(session[:username])}, :content => :'auth/register', :ajax => AJAX::Templating.new(env, params)}
end

post '/auth/register' do
	Dimension::Account.new params[:Username]
	redirect to('/auth/login')
end