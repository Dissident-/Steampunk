module Dimension
	class Skill
	
		@@list = ThreadSafe::Cache.new # by object id
		@@list_by_name = ThreadSafe::Cache.new # by name
		@@list_by_id = ThreadSafe::Cache.new # by db id
		
		attr_accessor :name
		
		attr_reader :effects
		
		def self.list()
			@@list_by_name
		end
	
		def id=(id)
			@id = id
			@@list_by_id[@id] = self unless id === nil
		end
		
		def initialize(name)
			@name = name
			@@list_by_name[@name] = self
			@@list[self.object_id] = self
			@effects = ThreadSafe::Array.new
		end
		
		def self.find(objid)
			return @@list[objid]
		end
		
		def self.find_by_name(name)
			return @@list_by_name[name]
		end
		
		def self.find_by_id(name)
			return @@list_by_id[name]
		end
		
		def self.load(values)
			new = Dimension::Skill.new values[:SkillName]
			new.id = values[:SkillID]
			return new
		end
		
		def add_effect(effect)
			@effects << effect
		end
	end
end