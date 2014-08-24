module Dimension
	class Location
	
		@@list = ThreadSafe::Array.new
		@@list_by_id = ThreadSafe::Cache.new
		
		attr_reader :id
		attr_accessor :plane, :x, :y, :z #TODO: Adjust list_by_coordinates if they ever get altered? or disable altering
		attr_accessor :type
		attr_accessor :name, :description
		
		
		attr_reader :occupants
		
		def self.list()
			@@list
		end
		
		def id=(id)
			@id = id
			@@list_by_id[@id] = self
		end
		
		def initialize(plane, x, y, z, id = nil)
			@@list << self
			@x = x
			@y = y
			@z = z
			@plane = plane
			plane.add_location self
			@id = id
			@@list_by_id[id] = self unless id === nil
			@occupants = ThreadSafe::Array.new
		end
		
		def surrounds(area = 2)
			sur = ThreadSafe::Array.new
			for yy in (y - area)..(y + area)
				for xx in (x - area)..(x + area)
					sur << @plane.find_location(xx, yy, @z)
				end
			end
			return sur
		end
		
		def arrive(char)
			@occupants << char
			@occupants.sort { |x, y| x.name <=> y.name }
		end
		
		def depart(char)
			@occupants.delete char
		end
		
		def self.find_by_id(id)
			return @@list_by_id[id]
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
		
		def save()
			return {:LocationID => @id, :CoordinateX => @x, :CoordinateY => @y, :CoordinateZ => @z, :PlaneID => @plane.id, :TileTypeID => @type.id, :LocationName => @name, :Description => @description}
		end
	end
end