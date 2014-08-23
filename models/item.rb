module Dimension
	class Item
	
		@@list = ThreadSafe::Array.new
		@@list_by_id = ThreadSafe::Cache.new
		@@unsaved = ThreadSafe::Array.new
		
		attr_reader :id
		
		attr_reader :type
		
		attr_reader :owner
		
		attr_accessor :quality
		
		attr_accessor :effects
		
		def self.list()
			@@list
		end
		
		def id=(id)
			@id = id
			@@list_by_id[@id] = self unless id === nil
		end
		
		def initialize(owner, type, id = nil)
			@@list << self
			@owner = owner
			@type = type
			@id = id
			@@list_by_id[@id] = self unless id === nil
			owner.obtain self
		end
		
		def self.find_by_id(id)
			return @@list_by_id[id]
		end
		
		def self.load(values)
			new = Item.new(Character.find_by_id(values[:CharacterID]), ItemType.find_by_id(values[:ItemTypeID]), values[:ItemInstanceID])
			new.quality = values[:Quality]
			return new
		end
		
		def save()
			return {:ItemInstanceID => @id, :ItemTypeID => @type.id, :CharacterID => @owner.id, :Quality => @quality}
		end
	end
end