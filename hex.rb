require 'require_all'
require 'sinatra'
require 'thread_safe'
require 'sequel'
require 'pp'
require 'rufus-scheduler'

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

before do
	@viewdata = {:user => session[:user], :ajax => AJAX::Templating.new(env, params)}
end

require_rel 'controllers'
require_rel 'models'
require_rel 'helpers'

require_rel 'continuity' unless ENV['OS'] == 'Windows_NT'

Dimension::Wizzardry.incantation

scheduler = Rufus::Scheduler.new

scheduler.every '15m', :blocking => true do
	Dimension::Sourcery.persistence
end

scheduler.cron '*/15 * * * *', :blocking => true do
	Dimension::Sourcery.revitalise
end