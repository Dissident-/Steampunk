
get '/auth/login' do
	haml :index, :locals => @viewdata.merge!({:content => :'auth/login'})
end


post '/auth/login' do
	user = Dimension::Account.find(params[:Username])
	if user then
		if user.check_password(params[:Password]) then
			session[:user] = user
			redirect to('/')
		else
			haml :index, :locals => @viewdata.merge!({:content => :'auth/login', :data => { :errors => [ "Incorrect password" ] }})
		end
	else
		haml :index, :locals => @viewdata.merge!({:content => :'auth/login', :data => { :errors => [ "User not found" ] }})
	end
end


get '/auth/register' do
	haml :index, :locals => @viewdata.merge!({:content => :'auth/register'})
end

post '/auth/register' do
	errors = []
	errors << "Username required" unless params[:Username].length > 0
	errors << "Username already taken" unless Dimension::Account.find(params[:Username]) == nil
	errors << "Password required" unless params[:Password].length > 0
	errors << "Email address required" unless params[:Password].length > 0
	
	if errors.count > 0 then
		haml :index, :locals => @viewdata.merge!({:content => :'auth/register', :data => { :errors => errors }})
	else
		account = Dimension::Account.new params[:Username]
		account.set_password params[:Password]
		account.email = params[:Email]
		session[:user] = account
		redirect to('/character/list')
	end
end