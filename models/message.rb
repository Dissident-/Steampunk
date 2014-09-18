module Dimension
	class Message
		attr_reader :timestamp
		attr_reader :message
		attr_reader :id
		attr_accessor :source
		
		@@list = ThreadSafe::Cache.new
	
		@@unsaved = ThreadSafe::Array.new
		
		attr_accessor :listeners
	
		def id=(id)
			@id = id
			@@unsaved.delete self
			@@list[id] = self
		end
		
		def save()
			return {:CharacterID => @source, :Activity => @message, :Timestamp => @timestamp}
		end
	
		def self.send(message, recipients, sender = nil)
		
			msg = Message.new(message, Time.now)
			msg.source = sender.id unless sender === nil
		
			if recipients.respond_to? :each then
				recipients.each do |recipient|
					recipient.add_message msg
					msg.listeners << recipient
				end
			else
				recipients.add_message msg
				msg.listeners << recipients
			end
			
			@@unsaved << msg
		end
	
		def initialize(message, timestamp, id = nil)
			@timestamp = timestamp
			@message = message
			@id = id
			@@list[id] = self unless id === nil
			@listeners = ThreadSafe::Array.new
			@source = nil
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