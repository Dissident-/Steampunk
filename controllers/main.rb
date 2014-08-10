
get '/' do
	haml :index, :locals => {:data => {:user => Dimension::Account.find(session[:username])}, :ajax => AJAX::Templating.new(env, params)}
end

get '/tt' do
	haml :index
end

get '/test' do
	"return"
end


get '/test2' do
	return "return"
end