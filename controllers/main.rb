class Dungeon < Sinatra::Application
	get '/' do
		haml :index, :locals => @viewdata
	end
end