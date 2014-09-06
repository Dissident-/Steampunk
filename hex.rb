require 'require_all'
require 'sinatra/base'
require 'thread_safe'
require 'sequel'
require 'pp'
require 'rufus-scheduler'
require 'json'
require 'securerandom'
require 'eventmachine'
require 'faye/websocket'
require 'haml'

Faye::WebSocket.load_adapter('puma')

class Dungeon < Sinatra::Application
	use Rack::Session::Pool


	if ENV['OS'] == 'Windows_NT' then
		configure { set :views, settings.root + '/views'
		set :threaded, true }
	else
		configure { set :server, :puma
		set :views, settings.root + '/views'
		set :threaded, true		}
	end

	helpers do
	  def esc(text)
		Rack::Utils.escape_html(text)
	  end
	end

	before do
		@viewdata = {:user => session[:user], :ajax => AJAX::Templating.new(env, params)}
	end

	require_rel 'controllers'
	require_rel 'models'
	require_rel 'helpers'
	#require_rel 'sockets'
	require_rel 'continuity'

	Dimension::Wizzardry.incantation

	scheduler = Rufus::Scheduler.new

	scheduler.every '15m', :blocking => true do
		Dimension::Sourcery.persistence
	end

	scheduler.cron '*/15 * * * *', :blocking => true do
		Dimension::Sourcery.revitalise
	end
end
	


   server  = 'puma'
   host    = '0.0.0.0'
   port    = '4567'
   web_app = Dungeon.new
   
 	
	 ws_app = lambda do |env|
		#puts env.inspect
		#if Faye::WebSocket.websocket?(env)
		ws = Faye::WebSocket.new(env, ["dungeon"], {:ping => 30})
		Dimension::SocketHandler.add_socket ws
		
		ws.on :message do |event|
			begin
			
			msg = JSON(event.data)
			
			puts event.data
			
			Dimension::SocketHandler.handle(ws, msg)
				
			rescue JSON::ParserError => ex
				puts "D: You call that JSON? #{ex}"
				data = { 'type' => 'error', 'message' => "Please use valid JSON. #{ex}" }.to_json
				ws.send(data)
				puts ">> #{data}"
			end
		end
		
		ws.on :error do |event|
			puts event.inspect
		end

		ws.on :close do |event|
			p [:close, event.code, event.reason]
			puts "closed for #{ event.reason} "
			ws.character = nil
			Dimension::SocketHandler.remove_socket(ws)
			ws = nil
		end

		#Return async Rack response
		ws.rack_response

		#else
		# Normal HTTP request
		#[200, {'Content-Type' => 'text/plain'}, ['This is the WebSocket endpoint']]
		#end
	end
	 
   
   
   
   
	dispatch = Rack::Builder.app do
		map '/' do
			run web_app
		end
		map '/ws' do
			run ws_app
		end
	end
	


	
		Rack::Server.start({
		  app:    dispatch,
		  server: server,
		  Host:   host,
		  Port:   port
		})
	
