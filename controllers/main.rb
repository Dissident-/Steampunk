get '/' do
	haml :index, :locals => @viewdata
end