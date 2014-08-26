require 'jdbc/mysql' unless ENV['OS'] == 'Windows_NT'
require 'sequel'
gem('mysql') if ENV['OS'] == 'Windows_NT'
print 'Connecting to database...'
DB = Sequel.connect("jdbc:mysql://127.0.0.1/dungeon?user=esk") unless ENV['OS'] == 'Windows_NT'
DB = Sequel.connect("mysql://127.0.0.1/dungeon?user=root") if ENV['OS'] == 'Windows_NT'
puts "100%"