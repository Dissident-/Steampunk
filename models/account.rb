module Dimension
	class Account
	
		@@list = ThreadSafe::Cache.new
	
		attr_reader :username
		attr_accessor :password
		attr_accessor :characters
		
		def initialize(newname)
			@username = newname
			@@list[@username] = self
			@characters = {}
		end
		
		def add_character(name)
			return @characters[name] = Dimension::Character.new(self, name)
		end
		
		def self.find(name)
			return @@list[name]
		end
	end
end