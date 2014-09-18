require 'jdbc/mysql' unless ENV['OS'] == 'Windows_NT'
require 'sequel'

module Dimension
	class Sourcery
		def self.persistence()
		
			# Due to an issue with rufus on Windows, it seems to trigger a save tick straightaway during startup
			if ENV['OS'] == 'Windows_NT' then
				unless defined? @@skipped then
					@@skipped = true
					return
				end
			end
			
			#TODO: Handle deletions
		
			puts '>>> >>> >>> OPEN THE DUNGEON DIMENSIONS >>> >>> >>>'
			
			print 'saves on a *PLANE* '
			
			# Planes
			
			Dimension::Plane.list.each_value do |plane|
				if plane.id === nil then
					plane.id = DB[:plane].insert plane.save
				else
					DB[:plane].where(:PlaneID => plane.id).update(plane.save)
				end
			end
			
	
			print 'while vile *TILES* smile '
			
			
			Dimension::LocationType.list.each_value do |type|
				if type.id === nil then
					type.id = DB[:tile_type].insert type.save
				else
					DB[:tile_type].where(:TileTypeID => type.id).update(type.save)
				end
			end			
			
			print '*LOCATION* dislocation '
			
			Dimension::Location.list.each do |location|
				if location.id === nil then
					location.id = DB[:location].insert location.save
				else
					DB[:location].where(:LocationID => location.id).update(location.save)
				end
			end			
			
			#print 'APOCALYPTICAL ACCOUNTANCY '
			
			#Accounts are now saved to the database the moment they are created
			#DB.transaction do
			#	Dimension::Account.list.values.each do |account|
			#		if account.id === nil then
			#			account.id = DB[:account].insert account.save
			#		else
			#			DB[:account].where(:AccountID => account.id).update(account.save)
			#		end
			#	end	
			#end			
			
			print 'such shady *CHARACTERS* '
			DB.transaction do
				Dimension::Character.list.each_value do |alt|
					if alt.id === nil then
						alt.id = DB[:character].insert alt.save
					else
						DB[:character].where(:CharacterID => alt.id).update(alt.save)
					end
				end	
			end
			
			print '*CATEGORICALLY* catastrophic '
			DB.transaction do
				Dimension::ItemCategory.list.each_value do |cat|
					if cat.id === nil then
						cat.id = DB[:item_category].insert cat.save
					else
						DB[:item_category].where(:ItemCategoryID => cat.id).update(cat.save)
					end
				end	
			end
			
			print 'typically *TYPED* '
			DB.transaction do
				Dimension::ItemType.list.each_value do |typ|
					if typ.id === nil then
						typ.id = DB[:item_type].insert typ.save
					else
						DB[:item_type].where(:ItemTypeID => typ.id).update(typ.save)
					end
				end	
			end
			
			print 'ITEMising ITEMisation '
			DB.transaction do
				Dimension::Item.list.each do |item|
					if item.id === nil then
						item.id = DB[:item_instance].insert item.save
					else
						DB[:item_instance].where(:ItemInstanceID => item.id).update(item.save)
					end
				end	
			end
			
			print 'affecting *EFFECTS* '
			DB.transaction do
				Dimension::Effect.list.each_value do |effect|
					if effect.id === nil then
						effect.id = DB[:effect].insert effect.save
					else
						DB[:effect].where(:EffectID => effect.id).update(effect.save)
					end
				end	
			end
			
			print 'learning *SKILLS* '
			DB.transaction do
				Dimension::Skill.list.each do |_,skill|
					if skill.id === nil then
						skill.id = DB[:skill].insert skill.save
					else
						DB[:skill].where(:SkillID => skill.id).update(skill.save)
					end
				end	
			end
			
			print '*SPREADING RUMOURS!* '
			DB.transaction do
				Dimension::Message.unsaved.dup.each do |msg|
					msg.id = DB[:activity_log].insert msg.save
					msg.listeners.each do |rec|
						@ins = {ActivityLogID: msg.id, CharacterID: rec.id}
						DB[:activity_log_reader].insert @ins
					end
				end				
			end
			puts 'DONE!'
			puts '<<< <<< <<< SHUT THE DUNGEON DIMENSIONS <<< <<< <<< OUT OF CHEESE ERROR'
		end
		
		def self.revitalise()
			puts '!!! !!! !!! MUCH ADO BOUT SUCH SOURCERY !!! !!! !!! SOME ACTION OCCURS'
			Dimension::Character.list.values.each do |alt|
				alt.ap = alt.ap + 1 unless alt.ap >= 100
			end	
		end
	end
end