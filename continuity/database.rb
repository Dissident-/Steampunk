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
	DB = Sequel.connect("jdbc:mysql://127.0.0.1/brawl?user=brawl&password=FDybdt8uddy")

	}
puts "100%"	
print 'Loading accounts...'

show_wait_spinner{
	DB.fetch("SELECT * FROM `account`") do |row|
		account = Dimension::Account.load row
	end
}
puts "100%"	
print 'Loading characters...'
show_wait_spinner{
	DB.fetch("SELECT * FROM `character`") do |row|
		character = Dimension::Character.load row
	end
}
puts "100%"	