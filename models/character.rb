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
		
		def self.load(values)
			new = Account.find_by_id(values[:AccountID]).add_character values[:CharName]
			new.hp = values[:HitPoints]
			new.ap = values[:ActionPoints]
			new.xp = values[:Experience]
			new.level = values[:Level]
			new.cp = values[:SkillPoints]
			new.location = Location.find_by_id values[:LocationID]
			return new
		end
	end
end