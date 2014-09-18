module Dimension
	class Character
	
		@@list = ThreadSafe::Cache.new # by object id
		@@list_by_name = ThreadSafe::Cache.new # by name
		@@list_by_id = ThreadSafe::Cache.new # by db id
		
		attr_accessor :log
		
		attr_reader :owner
		
		attr_accessor :name
		attr_reader :hp
		
		def hp=(hp)
			if @hp != nil && @hp > 0 && hp <= 0 then
				@hp = hp
				loc = self.location
				self.despawn
				loc.occupants.broadcast(self.link + ' has died!', self)
				Dimension::Message.send('You have died!', self, self)
			else
				@hp = hp
			end
		end
		
		attr_accessor :ap
		
		attr_accessor :xp
		attr_accessor :level
		attr_accessor :cp
		attr_reader :id
		attr_reader :inventory
		attr_reader :inventory_by_category
		attr_accessor :location
		
		attr_reader :skills
		attr_reader :compiled_effects
		
		def attach_socket(ws)
			@sockets << ws unless @sockets.include? ws
		end
		
		def detach_socket(ws)
			@sockets.delete ws
		end
		
		def send_socket(message)
			@sockets.each do |ws|
				ws.send(message)
			end
		end
		
		def self.list()
			@@list_by_name
		end
	
		def id=(id)
			@id = id
			@@list_by_id[@id] = self unless id === nil
		end
		
		def obtain(item)
			@inventory << item
			@inventory_by_category[item.type.category] << item
		end
		

		def initialize(owner, charname)
			@sockets = ThreadSafe::Array.new
			@log = ThreadSafe::Array.new
			@name = charname
			@owner = owner
			
			@ap = 100
			@xp = 0
			@cp = 10
			@level = 1
			
			@inventory = ThreadSafe::Array.new
			@inventory_by_category = ThreadSafe::Cache.new{|hash, key| hash[key] = ThreadSafe::Array.new}
			
			@skills = ThreadSafe::Cache.new
			@compiled_effects = ThreadSafe::Cache.new{|hash, key| hash[key] = ThreadSafe::Array.new}
			
			@@list_by_name[@name] = self
			@@list[self.object_id] = self
		end
		
		def self.find(objid)
			return @@list[objid]
		end
		
		def self.find_by_name(name)
			return @@list_by_name[name]
		end
		
		def self.find_by_id(name)
			return @@list_by_id[name]
		end
		
		def self.load(values)
			new = Account.find_by_id(values[:AccountID]).add_character values[:CharName]
			new.hp = values[:HitPoints]
			new.ap = values[:ActionPoints]
			new.xp = values[:Experience]
			new.level = values[:Level]
			new.cp = values[:SkillPoints]
			new.id = values[:CharacterID]
			new.location = Location.find_by_id values[:LocationID]
			new.location.arrive new unless new.location == nil

			return new
		end
		
		def damage(amount, type = :unsoakable)
			self.hp = self.hp - amount
		end
		
		def learn(skill)
			@skills[skill.object_id] = skill
			skill.effects.each do |effect|
				@compiled_effects[effect.type] << effect
			end
		end
		
		def recompile_effects()
			compiled_effects = ThreadSafe::Cache.new{|hash, key| hash[key] = ThreadSafe::Array.new}
			@skills.values.each do |skill|
				skill.effects.each do |effect|
					compiled_effects[effect.type] << effect
				end
			end
			@compiled_effects = compiled_effects
		end
		
		def add_message(message)
			@log << message
			self.send_socket({'type' => 'log', 'message' => message.timestamp.getutc.strftime("%Y-%m-%d %H:%M:%S") + ' ' + message.message}.to_json)
		end
		
		def say(message)
			if message.tainted? === true then
				message = Rack::Utils.escape_html(message)
				message.untaint
			end
			if message.start_with?('/me ', '/ME ') then
				Dimension::Message.send(self.link + '\'' +  message[4..-1] + '\'', self.location.occupants, self)
			else
				Dimension::Message.send(self.link + ' said \'' +  message + '\'', self.location.occupants, self)
			end
		end
		
		def link()
			return '<a data-ajax="#page_content" href="/character/profile/' + self.name + '">' + self.name + '</a>'
		end
		
		def respawn()
			@location.depart self unless @location === nil
			@location = Location.find_by_id 1
			@location.arrive self
			@hp = 50
		end
		
		def despawn()
			@location.depart self unless @location === nil
			@location = nil
		end
		
		def move(destination)
			@location.depart self unless @location === nil
			oldlocation = @location			
			@location = destination
			@location.arrive self unless destination === nil
			
			unless oldlocation === nil then
				Thread.new do
					oldoccupance = oldlocation.occupants.count
					
					if oldoccupance > 0 then
						# Inform occupants of departure
						packet = {'type' => 'occupants', 'list' => oldlocation.occupants.map { |r| Hash['name' => r.name] } }.to_json
						oldlocation.occupants.each do |char|
							char.send_socket(packet)
						end
					else # inform area of emptiness
						people = []
						oldlocation.surrounds.each do |loc|
							unless loc === nil then
								people.concat(loc.occupants)
							end
						end
						packet = {'type' => 'remoteoccupance', 'mapid' => oldlocation.object_id, 'occupied' => 0}.to_json
						people.each do |char|
							char.send_socket(packet) unless char == self
						end
					end
				end
			end
			unless @location === nil then
				Thread.new do		
					newoccupance = @location.occupants.count
					if newoccupance > 1 then
						# Inform occupants of arrival
						packet = {'type' => 'occupants', 'list' => @location.occupants.map { |r| Hash['name' => r.name] }}.to_json
						@location.occupants.each do |char|
							char.send_socket(packet)
						end
					else # inform area of arrival
						people = []
						@location.surrounds.each do |loc|
							unless loc === nil then
								people.concat(loc.occupants)
							end
						end
						packet = {'type' => 'remoteoccupance', 'mapid' => @location.object_id, 'occupied' => 1}.to_json
						people.each do |char|
							char.send_socket(packet) unless char == self
						end
					end
				end
			end
			
	
		end
		
		def attempt_move(dest)
			if dest === nil or @location === nil then
				return {:message => "You can't move to or from nowhere!", :success => false}
			else
				if @ap < 1 then
					return {:message => "You are too tired to move!", :success => false}
				else
					if @location.plane == dest.plane and (((@location.x - dest.x).abs < 2 and (@location.y - dest.y).abs < 2 and @location.z - dest.z == 0) or (@location.x - dest.x == 0 and @location.y - dest.y == 0 and (@location.z - dest.z).abs == 1)) then
						@ap = @ap - 1
						self.move dest
						return {:success => true}
					else
						return {:message => "You can only move to adjacent locations!", :success => false}
					end
				end
			end
		end
		
		def save()
			return {:CharacterID => @id, :AccountID => @owner.id, :CharName => @name, :ActionPoints => @ap, :HitPoints => @hp, :Experience => @xp, :Level => @level, :SkillPoints => @cp, :LocationID => (@location == nil ? nil : @location.id)}
		end
	end
end