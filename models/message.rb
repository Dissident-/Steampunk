module Dimension
	class Message
		attr_reader :timestamp
		attr_reader :message
		attr_reader :id
		attr_accessor :source
		
		@@list = ThreadSafe::Cache.new
	
		@@unsaved = ThreadSafe::Array.new
		
		attr_accessor :listeners
		@listeners = ThreadSafe::Array.new
	
		def id=(id)
			@id = id
			@@unsaved.delete self
			@@list[id] = self
		end
		
		def save()
			return {:CharacterID => @source, :Activity => @message, :Timestamp => @timestamp}
		end
	
		def send(message, recipients, sender = nil)
		
			msg = Message.new(message, Time.now)
			msg.source = sender.id unless sender === nil
		
			recipients.each do |recipient|
				char = Character.find recipient
				char.add_message msg unless char === nil 
				msg.listeners << char unless char === nil
			end
			
			@@unsaved << msg
		end
	
		def initialize(message, timestamp, id = nil)
			@timestamp = timestamp
			@message = message
			@id = id
			@@list[id] = self unless id === nil
		end
		
		def self.find_by_id(id)
			return @@list[id]
		end
		
		def self.load(row)
			new = @@list[row[:ActivityLogID]]
			new = Message.new(row[:Activity], row[:Timestamp] , row[:ActivityLogID]) if new === nil
			return new
		end
		
		def self.unsaved()
			@@unsaved
		end
	end
end