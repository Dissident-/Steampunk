require 'bcrypt' unless ENV['OS'] == 'Windows_NT'
require 'digest/md5';

module Dimension
	class Account
	
		@@list = ThreadSafe::Cache.new
		@@list_by_id = ThreadSafe::Cache.new
		
		attr_reader :username
		attr_reader :password
		attr_accessor :email
		attr_reader :characters
		
		def initialize(newname, password)
			@username = newname
			@@list[@username] = self
			@characters = {}
			
			begin
				@password = BCrypt::Password.set password unless ENV['OS'] == 'Windows_NT'
			rescue	
				@password = password.split(":")
			end
			@password = password if ENV['OS'] == 'Windows_NT'
		end
		
		def add_character(name)
			return @characters[name] = Dimension::Character.new(self, name)
		end
		
		def self.find(name)
			return @@list[name]
		end
		
		def self.find_by_id(id)
			return @@list_by_id[id]
		end
		
		def check_password(password)
			if @password.kind_of?(Array) then
				return Digest::MD5.hexdigest(password + @password[1]) == @password[0]
			else
				return @password == password
			end
		end
		
		def set_password(password)
			@password = BCrypt::Password.create password unless ENV['OS'] == 'Windows_NT'
			@password = password if ENV['OS'] == 'Windows_NT'
		end
		
		def self.load(values)
			new = Account.new(values[:Username], values[:Password])
			new.email = values[:EmailAddress]
			@@list_by_id[values[:AccountID]] = new
			return new
		end
	end
end