module Dimension
	class Message
		attr_reader :timestamp
		attr_reader :message
		attr_reader :id
	
		def initialize(message, timestamp, id = nil)
			@timestamp = timestamp
			@message = message
			@id = id
		end
		
		def self.load(row)
			new = Message.new(row[:Activity], row[:Timestamp] , row[:ActivityLogID])
			char = Character.find row[:CharName]
			char.add_message(new) unless char == nil
		end
	end
end