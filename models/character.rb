module Dimension
	class Character
	
		@@list = ThreadSafe::Cache.new
	
		attr_reader :owner
		
		attr_accessor :name
		attr_accessor :hp
		attr_accessor :ap
		attr_accessor :xp
		attr_accessor :level
		attr_accessor :cp
		
		attr_accessor :location
		
		def initialize(owner, charname)
			@name = charname
			@owner = owner
			@@list[@name] = self
		end
		
		def self.find(name)
			return @@list[name]
		end
	end
end