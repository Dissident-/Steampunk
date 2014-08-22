require 'jdbc/mysql'
require 'sequel'

module Dimension
	class Sourcery
		def self.persistence()
		
			puts '*** *** *** OPEN THE DUNGEON DIMENSIONS *** *** *** SAVES ON A PLANE'
			
			
			# Planes
			
			Dimension::Plane.list.values.each do |plane|
				if plane.id === nil then
					plane.id = DB[:plane].insert plane.save
				else
					DB[:plane].where(:PlaneID => plane.id).update(plane.save)
				end
			end
			
	
			puts '+++ +++ +++ OPEN THE DUNGEON DIMENSIONS +++ +++ +++ WHILE VILE TILES SMILE'
			
			
			Dimension::LocationType.list.values.each do |type|
				if type.id === nil then
					type.id = DB[:tile_type].insert type.save
				else
					DB[:tile_type].where(:TileTypeID => type.id).update(type.save)
				end
			end			
			
			puts '<<< <<< <<< OPEN THE DUNGEON DIMENSIONS >>> >>> >>> LOCATION DISLOCATION'
			
			Dimension::Location.list.each do |location|
				if location.id === nil then
					location.id = DB[:location].insert location.save
				else
					DB[:location].where(:LocationID => location.id).update(location.save)
				end
			end			
			
			puts '~~~ ~~~ ~~~ OPEN THE DUNGEON DIMENSIONS ~~~ ~~~ ~~~ APOCALYPTICAL ACCOUNTANCY'
			
			#Dimension::Account.list.values.each do |account|
			#	if account.id === nil then
			#		account.id = DB[:account].insert account.save
			#	else
			#		DB[:account].where(:AccountID => account.id).update(account.save)
			#	end
			#end		
			
			puts '--- --- --- OPEN THE DUNGEON DIMENSIONS --- --- --- SUCH SHADY CHARACTERS'
			
			Dimension::Character.list.values.each do |alt|
				if alt.id === nil then
					alt.id = DB[:character].insert alt.save
				else
					DB[:character].where(:CharacterID => alt.id).update(alt.save)
				end
			end	
			
			puts '### ### ### SHUT THE DUNGEON DIMENSIONS ### ### ### OUT OF CHEESE ERROR'
		end
		
		def self.revitalise()
			puts '!!! !!! !!! SUCH SOUCERY !!! !!! !!! SOME ACTION OCCURS'
			Dimension::Character.list.values.each do |alt|
				alt.ap = alt.ap + 1 unless alt.ap > 100
			end	
		end
	end
end