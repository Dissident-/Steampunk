module Dimension
	class Character
	
		@@list = ThreadSafe::Cache.new
		
		
	
		attr_accessor :log
		
		attr_reader :owner
		
		attr_accessor :name
		attr_accessor :hp
		attr_accessor :ap
		attr_accessor :xp
		attr_accessor :level
		attr_accessor :cp
		
		attr_accessor :location
		
		def initialize(owner, charname)
			@log = ThreadSafe::Array.new
			@name = charname
			@owner = owner
			@@list[@name] = self
		end
		
		def self.find(name)
			return @@list[name]
		end
		
		def self.load(values)
			new = Account.find_by_id(values[:AccountID]).add_character values[:CharName]
			new.hp = values[:HitPoints]
			new.ap = values[:ActionPoints]
			new.xp = values[:Experience]
			new.level = values[:Level]
			new.cp = values[:SkillPoints]
			new.location = Location.find_by_id values[:LocationID]
			new.location.arrive new unless new.location == nil
			return new
		end
		
		def add_message(message)
			@log << message
		end
		
		def respawn()
			@location = Location.find_by_id 1
			@location.arrive self
			@hp = 50
		end
	end
end