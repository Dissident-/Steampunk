module Dimension
	class Character
	
		@@list = ThreadSafe::Cache.new # by object id
		@@list_by_name = ThreadSafe::Cache.new # by name
		@@list_by_id = ThreadSafe::Cache.new # by db id
		
		attr_accessor :log
		
		attr_reader :owner
		
		attr_accessor :name
		attr_accessor :hp
		attr_accessor :ap
		attr_accessor :xp
		attr_accessor :level
		attr_accessor :cp
		attr_reader :id
		attr_reader :inventory
		attr_reader :inventory_by_category
		attr_accessor :location
		
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
		
		def add_message(message)
			@log << message
			self.send_socket({'type' => 'log', 'message' => message.timestamp.getutc.strftime("%Y-%m-%d %H:%M:%S") + ' ' + message.message}.to_json)
		end
		
		def say(message)
			message = Rack::Utils.escape_html(message)
			if message.start_with?('/me ', '/ME ') then
				Dimension::Message.send('<a data-ajax="#page_content" href="/character/profile/' + self.name + '">' + self.name + '</a> \'' +  message[4..-1] + '\'', self.location.occupants, self)
			else
				Dimension::Message.send('<a data-ajax="#page_content" href="/character/profile/' + self.name + '">' + self.name + '</a> said \'' +  message + '\'', self.location.occupants, self)
			end
		end
		
		def respawn()
			@location = Location.find_by_id 1
			@location.arrive self
			@hp = 50
		end
		
		def move(destination)
			@location.depart self
			@location = destination
			@location.arrive self
		end
		
		def save()
			return {:CharacterID => @id, :AccountID => @owner.id, :CharName => @name, :ActionPoints => @ap, :HitPoints => @hp, :Experience => @xp, :Level => @level, :SkillPoints => @cp, :LocationID => (@location == nil ? nil : @location.id)}
		end
	end
end