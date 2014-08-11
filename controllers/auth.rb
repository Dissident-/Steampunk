
get '/auth/login' do
	haml :index, :locals => @viewdata.merge!({:content => :'auth/login'})
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
	haml :index, :locals => @viewdata.merge!({:content => :'auth/register'})
end

post '/auth/register' do
	Dimension::Account.new params[:Username]
	redirect to('/auth/login')
end