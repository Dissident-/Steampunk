module Library
	module Trigger
		class ExplosiveMurder
			def self.activated(user)
				return 'You are dead!' if user.location === nil or user.hp < 1
				return 'You need 1 AP in order to explode!' if user.ap < 1
				loc = user.location
				user.hp = 0
				user.ap = user.ap - 1
				loc.arrive user # temporarily resubscribe user to tile broadcasts
				total = 0
				kills = 0
				loc.occupants.each do |victim|
					unless user == victim then
						damage = victim.damage(Random.rand(10..20), :unholy)
						total = total + damage
						kills = kills + 1 if victim.hp <= 0
						Dimension::Message.send(user.link + ' has exploded violently! You take ' + damage.to_s + ' unholy damage from the blast.', victim, user)
					end
				end
				loc.depart user
				Dimension::Message.send('Focusing your hatred, you explode violently! You have dealt ' + total.to_s + ' damage and killed ' + kills.to_s + (kills == 1 ? ' person.' : ' people.'), user, user)
				return :success
			end
		end
	end
end