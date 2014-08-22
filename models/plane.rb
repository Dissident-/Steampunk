module Dimension
	class Plane
	
		@@list_by_id = ThreadSafe::Cache.new
		
		@@list = ThreadSafe::Cache.new
		
		@@locations_x_y_z = ThreadSafe::Cache.new{|hash, key| hash[key] = ThreadSafe::Cache.new{|hash, key| hash[key] = ThreadSafe::Cache.new}}
	
		attr_reader :id
		
		def self.list()
			@@list
		end
		
		def id=(id)
			@id = id
			@@list_by_id[@id] = self
		end
		
		attr_accessor :name
		
		def find_location(x, y, z)
			return nil unless @@locations_x_y_z.key? x
			loc = @@locations_x_y_z[x]
			return nil unless loc.key? y
			loc = loc[y]
			return nil unless loc.key? z
			return loc[z]
		end
		
		def add_location(location)
			@@locations_x_y_z[location.x][location.y][location.z] = location
		end
		
		def initialize(name, id = nil)
			@name = name
			@@list[name] = self
			@id = id
			@@list_by_id[id] = self unless id == nil
		end
		
		def self.find(name)
			return @@list[name]
		end
		
		def self.find_by_id(id)
			return @@list_by_id[id]
		end
		
		def self.load(values)
			new = Plane.new(values[:PlaneName], values[:PlaneID])
			return new
		end
		
		def save()
			return {:PlaneID => @id, :PlaneName => @name }
		end
	end
end