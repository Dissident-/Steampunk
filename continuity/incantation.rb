module Dimension
	class Wizzardry

		def self.show_wait_spinner(fps=10)
		  chars = %w[| / - \\]
		  delay = 1.0/fps
		  iter = 0
		  spinner = Thread.new do
			while iter do  # Keep spinning until told otherwise
			  print chars[(iter+=1) % chars.length]
			  sleep delay
			  print "\b"
			end
		  end
		  yield.tap{       # After yielding to the block, save the return value
			iter = false   # Tell the thread to exit, cleaning up after itself…
			spinner.join   # …and wait for it to do so.
		  }                # Use the block's return value as the method's
		end
		 
		def self.incantation()
			print 'Loading planes...'
			Wizzardry.show_wait_spinner{
				DB.fetch("SELECT * FROM `plane`") do |row|
					Dimension::Plane.load row
				end
			}
			puts "100%"		
			print 'Loading location types...'
			Wizzardry.show_wait_spinner{
				DB.fetch("SELECT * FROM `tile_type`") do |row|
					Dimension::LocationType.load row
				end
			}
			puts "100%"	
			print 'Loading locations...'
			Wizzardry.show_wait_spinner{
				DB.fetch("SELECT * FROM `location`") do |row|
					Dimension::Location.load row
				end
			}
			puts "100%"		
			print 'Loading accounts...'

			Wizzardry.show_wait_spinner{
				DB.fetch("SELECT * FROM `account`") do |row|
					Dimension::Account.load row
				end
			}
			puts "100%"	
			print 'Loading characters...'
			Wizzardry.show_wait_spinner{
				DB.fetch("SELECT * FROM `character`") do |row|
					Dimension::Character.load row
				end
			}
			puts "100%"	
			print 'Loading messages...'
			Wizzardry.show_wait_spinner{
				DB.fetch("SELECT * FROM activity_log WHERE Timestamp > NOW() - INTERVAL 1 WEEK") do |row|
					Dimension::Message.load row
				end
			}
			puts "100%"	
			print 'Assigning messages...'
			Wizzardry.show_wait_spinner{
				DB.fetch("SELECT * FROM activity_log_reader WHERE ActivityLogID > (SELECT MIN(ActivityLogID) FROM activity_log WHERE Timestamp > NOW() - INTERVAL 1 WEEK)") do |row|
					char = Dimension::Character.find_by_id row[:CharacterID]
					unless char === nil then
						message = Dimension::Message.find_by_id row[:ActivityLogID]
						char.add_message message unless message === nil
					end
				end
			}
			puts "100%"	
			print 'Loading item categories...'
			Wizzardry.show_wait_spinner{
				DB.fetch("SELECT * FROM `item_category`") do |row|
					Dimension::ItemCategory.load row
				end
			}
			puts "100%"	
			print 'Loading item types...'
			Wizzardry.show_wait_spinner{
				DB.fetch("SELECT * FROM `item_type`") do |row|
					Dimension::ItemType.load row
				end
			}
			puts "100%"
			print 'Loading items...'
			Wizzardry.show_wait_spinner{
				DB.fetch("SELECT * FROM `item_instance`") do |row|
					Dimension::Item.load row
				end
			}
			puts "100%"
			print 'Loading effects...'
			Wizzardry.show_wait_spinner{
				DB.fetch("SELECT * FROM `effect_type`") do |row|
					Dimension::Effect.add_type(row[:TypeName], row[:EffectTypeID])
				end
				DB.fetch("SELECT * FROM `effect` INNER JOIN `effect_type` ON `effect`.`EffectTypeID` = `effect_type`.`EffectTypeID`") do |row|
					Dimension::Effect.load row
				end
			}
			puts "100%"
			print 'Loading skills...'
			Wizzardry.show_wait_spinner{
				DB.fetch("SELECT * FROM `skill`") do |row|
					Dimension::Skill.load row
				end
			}
			puts "100%"	
			print 'Linking skills and effects...'
			Wizzardry.show_wait_spinner{
				DB.fetch("SELECT * FROM `skill_effect`") do |row|
					Dimension::Skill.find_by_id(row[:SkillID]).add_effect Dimension::Effect.find_by_id(row[:EffectID])
				end
			}
			puts "100%"		
			print 'Characters learning skills...'
			Wizzardry.show_wait_spinner{
				DB.fetch("SELECT * FROM `skill_instance`") do |row|
					Dimension::Character.find_by_id(row[:CharacterID]).learn Dimension::Skill.find_by_id(row[:SkillID])
				end
			}
			puts "100%"			
		end
	end
end