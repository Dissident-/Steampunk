require 'bcrypt' unless ENV['OS'] == 'Windows_NT'
require 'digest/md5';

module Dimension
	class Account
	
		@@list = ThreadSafe::Cache.new
		@@list_by_id = ThreadSafe::Cache.new
		
		attr_reader :auth_token
		attr_reader :username
		attr_reader :password
		attr_accessor :email
		attr_reader :characters
		attr_reader :id
		
		def self.list()
			@@list
		end
		
		def id=(id)
			@id = id
			@@list_by_id[@id] = self
		end
		
		def initialize(newname, password = nil)
			@auth_token = SecureRandom.base64
			@username = newname
			@@list[@username] = self
			@characters = {}
			if password != nil then
				begin
					@password = BCrypt::Password.set password
				rescue	
					@password = password.split(":")
				end
			end
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
			new.id = values[:AccountID]
			return new
		end
		
		def save()
			return {:AccountID => @id, :Username => @username, :Password => @password, :EmailAddress => @email}
		end
		
		def persist()
			self.id = DB[:account].insert self.save
		end
	end
end