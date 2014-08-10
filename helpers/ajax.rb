module AJAX
	class Templating
	
		attr_accessor :ajax
		attr_accessor :target
		
		def initialize(env, params)
			@ajax = env['HTTP_X_REQUESTED_WITH'] == "XMLHttpRequest"
			if params.has_key? :target then
				@target = params[:target]
			else
				@target = '#page_content'
			end
			if @ajax then
				@render = {:head => false, :initialbody => false, :footer => false, :end => false} if @target == "#page_content"
				@render = {:head => false, :initialbody => true, :footer => true, :end => false} if @target == "body"
			else
				@render = {:head => true, :initialbody => true, :footer => true, :end => true}
			end
		end
		
		def render?(item)
			return @render[item]
		end
	

	end
end