require 'json'

module Dimension
	class Effect
	
		@@list = ThreadSafe::Cache.new # by object id
		@@list_by_name = ThreadSafe::Cache.new # by name
		@@list_by_id = ThreadSafe::Cache.new # by db id
		
		attr_accessor :params
		attr_accessor :name
		attr_accessor :type
		attr_reader :code_class
		
		@@types = ThreadSafe::Cache.new
		
		def self.add_type(type, id)
			@@types[type.to_sym] = id
		end
		
		def code=(code)
			@code_class = code
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
			@params = []
			@code = nil
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
			new = Dimension::Effect.new values[:EffectName]
			new.id = values[:EffectID]
			new.code = values[:EffectClass]
			new.type = values[:TypeName].to_sym
			new.params = JSON.parse values[:Information]
			return new
		end
		
		def run(typeparams = [])
			return nil if @code === nil
			return nil unless @code.respond_to?(@type)
			return @code.send(@type, *typeparams, *@params)
		end
		
		def save()
			return {:EffectID => @id, @EffectName => @name, :EffectTypeID => @@types[@type], :Information => @params.to_json, :EffectClass => @code_class}
		end
	
	end
end