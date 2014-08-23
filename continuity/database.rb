require 'jdbc/mysql'
require 'sequel'

print 'Connecting to database...'
DB = Sequel.connect("jdbc:mysql://127.0.0.1/dungeon?user=esk")
puts "100%"