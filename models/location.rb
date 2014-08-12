module Dimension
	class Location
	
		@@list = ThreadSafe::Array.new
		@@list_by_id = ThreadSafe::Cache.new
		
		@@occupants = ThreadSafe::Cache.new
		
		attr_reader :id
		attr_accessor :plane, :x, :y, :z #TODO: Adjust list_by_coordinates if they ever get altered? or disable altering
		attr_accessor :type
		attr_accessor :name, :description
		attr_reader :occupants
		
		def initialize(plane, x, y, z, id = nil)
			@@list << self
			@x = x
			@y = y
			@z = z
			@plane = plane
			plane.add_location self
			@@list_by_id[id] = self unless id == nil
			@id = id
		end
		
		def arrive(char)
			@@occupants[char.name] = char
		end
		
		def depart(char)
			@@occupants.delete! char.name
		end
		
		def self.find_by_id(id)
			return @@list_by_id[id]
		end
		
		def self.find_by_coordinates(p, x, y, z)
			return p.find_location(x, y, z)
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
			new = Location.new(Plane.find_by_id(values[:PlaneID]),values[:CoordinateX],values[:CoordinateY],values[:CoordinateZ], values[:LocationID])
			new.type = LocationType.find_by_id values[:TileTypeID]
			new.name = values[:LocationName]
			new.description = values[:Description]
			return new
		end
	end
end