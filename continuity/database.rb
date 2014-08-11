require 'jdbc/mysql'
require 'sequel'

puts 'Connecting to database...'

DB = Sequel.connect("jdbc:mysql://127.0.0.1/brawl?user=brawl&password=FDybdt8uddy")

puts 'Loading accounts...'

DB.fetch("SELECT * FROM `account`") do |row|
	account = Dimension::Account.load row
end

puts 'Loading characters...'

DB.fetch("SELECT * FROM `character`") do |row|
	character = Dimension::Character.load row
end