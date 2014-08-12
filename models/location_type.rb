module Dimension
	class LocationType
	
		@@list_by_id = ThreadSafe::Cache.new
		
		@@list = ThreadSafe::Cache.new
	
		attr_reader :id
		
		attr_accessor :name
		attr_accessor :colour, :description, :apcost
		
		def initialize(name, id = nil)
			@name = name
			@@list[name] = self
			@@list_by_id[id] = self unless id == nil
			@id = id
		end
		
		def self.find(name)
			return @@list[name]
		end
		
		def self.find_by_id(id)
			return @@list_by_id[id]
		end
		
		def self.load(values)
			new = LocationType.new(values[:TypeName], values[:TileTypeID])
			new.colour = values[:Colour]
			new.description = values[:DefaultDescription]
			new.apcost = values[:APCost]
			return new
		end
	end
end