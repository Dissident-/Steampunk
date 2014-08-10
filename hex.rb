require 'require_all'
require 'sinatra'
require 'thread_safe'
require 'sequel'

#require 'sinatra/session'
use Rack::Session::Pool
require 'haml'

if ENV['OS'] == 'Windows_NT' then
	configure { set :views, settings.root + '/views' }
else
	configure { set :server, :puma
	set :views, settings.root + '/views' }
end

helpers do
  def esc(text)
    Rack::Utils.escape_html(text)
  end
end

#Thread.new do
#    while true do
#        puts "Hi there"
#        sleep(2)
#    end
#end

require_rel 'controllers'
require_rel 'models'
require_rel 'helpers'