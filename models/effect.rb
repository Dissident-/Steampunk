module Dimension
	class Effect
	
		@@list = ThreadSafe::Cache.new # by object id
		@@list_by_name = ThreadSafe::Cache.new # by name
		@@list_by_id = ThreadSafe::Cache.new # by db id
		
		@params = []
		
		attr_accessor :name
		attr_accessor :type
		
		def code=(code)
			code = code.split('::')
			@code = Object
			code.each do |mod|
				@code = @code.const_get(mod)
			end
		end
				
		def self.list()
			@@list_by_name
		end
	
		def id=(id)
			@id = id
			@@list_by_id[@id] = self unless id === nil
		end
		
		def initialize(name)
			@name = name
			@@list_by_name[@name] = self
			@@list[self.object_id] = self
			@code = nil
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
			new = Dimension::Skill.new values[:EffectName]
			new.id = values[:EffectID]
			return new
		end
		
		def run(typeparams = [])
			return if @code === nil
			return unless @code.respond_to?(@type)
			return @code.send(@type, *typeparams, *@params)
		end
	
	end
end