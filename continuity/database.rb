require 'jdbc/mysql'
require 'sequel'

def show_wait_spinner(fps=10)
  chars = %w[| / - \\]
  delay = 1.0/fps
  iter = 0
  spinner = Thread.new do
    while iter do  # Keep spinning until told otherwise
      print chars[(iter+=1) % chars.length]
      sleep delay
      print "\b"
    end
  end
  yield.tap{       # After yielding to the block, save the return value
    iter = false   # Tell the thread to exit, cleaning up after itself…
    spinner.join   # …and wait for it to do so.
  }                # Use the block's return value as the method's
end
 
print 'Connecting to database...'

show_wait_spinner{
	DB = Sequel.connect("jdbc:mysql://127.0.0.1/brawl?user=esk")

	}
puts "100%"		
print 'Loading planes...'
show_wait_spinner{
	DB.fetch("SELECT * FROM `plane`") do |row|
		Dimension::Plane.load row
	end
}
puts "100%"		
print 'Loading location types...'
show_wait_spinner{
	DB.fetch("SELECT * FROM `tile_type`") do |row|
		Dimension::LocationType.load row
	end
}
puts "100%"	
print 'Loading locations...'
show_wait_spinner{
	DB.fetch("SELECT * FROM `location`") do |row|
		Dimension::Location.load row
	end
}
puts "100%"		
print 'Loading accounts...'

show_wait_spinner{
	DB.fetch("SELECT * FROM `account`") do |row|
		Dimension::Account.load row
	end
}
puts "100%"	
print 'Loading characters...'
show_wait_spinner{
	DB.fetch("SELECT * FROM `character`") do |row|
		Dimension::Character.load row
	end
}
puts "100%"	