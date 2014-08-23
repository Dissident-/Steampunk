module Dimension
	class ItemCategory
	
		@@list_by_id = ThreadSafe::Cache.new
		
		@@list = ThreadSafe::Cache.new
	
		def self.list()
			@@list
		end
		
		def id=(id)
			@id = id
			@@list_by_id[@id] = self unless id === nil
		end
	
		attr_reader :id
		
		attr_accessor :name
		attr_accessor :icon
		
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
			new = ItemCategory.new(values[:CategoryName], values[:ItemCategoryID])
			new.icon = values[:Icon]
			return new
		end
		
		def save()
			return {:ItemCategoryID => @id, :CategoryName => @name, :Icon => @icon }
		end
	end
end