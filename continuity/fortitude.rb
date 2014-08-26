require 'jdbc/mysql' unless ENV['OS'] == 'Windows_NT'
require 'sequel'

module Dimension
	class Sourcery
		def self.persistence()
		
			# Due to an issuewith rufus on Windows, it eems to trigger a save tick straightaway during startup
			if ENV['OS'] == 'Windows_NT' then
				unless defined? @@skipped then
					@@skipped = true
					return
				end
			end
		
			puts '*** *** *** SEEK THE DUNGEON DIMENSIONS *** *** *** SAVES ON A PLANE'
			
			
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
			
			#Accounts are now saved to the database the moment they are created
			
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
			
			puts '*** *** *** OPEN THE DUNGEON DIMENSIONS *** *** *** CATEGORICALLY CATASTROPHIC'
			
			Dimension::ItemCategory.list.values.each do |cat|
				if cat.id === nil then
					cat.id = DB[:item_category].insert cat.save
				else
					DB[:item_category].where(:ItemCategoryID => cat.id).update(cat.save)
				end
			end	
			
			puts '/// /// /// OPEN THE DUNGEON DIMENSIONS \\\\\\ \\\\\\ \\\\\\ TYPICALLY TYPED'
			
			Dimension::ItemType.list.values.each do |typ|
				if typ.id === nil then
					typ.id = DB[:item_type].insert typ.save
				else
					DB[:item_type].where(:ItemTypeID => typ.id).update(typ.save)
				end
			end	
			
			puts '^^^ ^^^ ^^^ OPEN THE DUNGEON DIMENSIONS ^^^ ^^^ ^^^ ITEMISING ITEMISATION'
			
			Dimension::Item.list.each do |item|
				if item.id === nil then
					item.id = DB[:item_instance].insert item.save
				else
					DB[:item_instance].where(:ItemInstanceID => item.id).update(item.save)
				end
			end	

			puts 'O_o O_o O_o OPEN THE DUNGEON DIMENSIONS o_O o_O o_O SPREADING RUMOURS'
			
			Dimension::Message.unsaved.dup.each do |msg|
					msg.id = DB[:activity_log].insert msg.save
					msg.listeners.each do |rec|
						@ins = {ActivityLogID: msg.id, CharacterID: rec.id}
						DB[:activity_log_reader].insert@ins
					end
			end				
			
			puts '### ### ### SHUT THE DUNGEON DIMENSIONS ### ### ### OUT OF CHEESE ERROR'
		end
		
		def self.revitalise()
			puts '!!! !!! !!! MUCH ADO BOUT SUCH SOURCERY !!! !!! !!! SOME ACTION OCCURS'
			Dimension::Character.list.values.each do |alt|
				alt.ap = alt.ap + 1 unless alt.ap >= 100
			end	
		end
	end
end