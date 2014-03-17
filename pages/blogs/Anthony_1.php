<div id="single-blog" class="double-col empty">
	<div id="blog-post-header">
		<p>Designing the World Character</p>
	</div>
	<div id="blog-post-body">
		<img id="blog-post-header-image" class="image xx-large-wide"
			src="/Assets/images/Blogs/Designing-World-Character-2014-03-17/SOA.png" style="border:none;" />
		<div>
			<p>Designing a sandbox game is one of the most intensive, yet
				rewarding experiences I've had in my few short years in the games
				industry. I'd like to share some of my design concepts, principles
				and the general direction I plan to work with when designing SoA
				alongside Ben and Sebastian.</p>
			<p>First is the concept of <em>"Pillars of Design"</em> or those areas of
				building a game that are built around the <em>"core feeling"</em> and major
				game-play concepts. Every game is first designed with a very simple
				question, with very simple answers...</p>
			<h2>"What do I want my players to feel?"</h2>
			<p>In Seed of Andromeda those answers are the following,</p>
			<p>&bull; Sense of Freedom to choose how to play the game.</p>
			<p>&bull; Desire to Create in the World</p>
			<p>&bull; Desire to Explore the World</p>
			<p>&bull; Desire to Interact (with the world / NPCS / other players
				). Or as it's commonly known: <em>"The Social Aspect"</em></p>
			<p>So of these base feelings we can design game-play features and
				functionality. If we want the player to have this sense of freedom
				to explore, and a desire to explore, we must first give them a world
				worth exploring. The same follows suit with the rest of the base
				feelings we want to instill, as well as holding to the three basic
				principles of the sandbox; which are Interactivity, Creativity, and
				Exploration.</p>


			<h2>So, now that we know all this...what next?</h2>
			<p>Simple, we design the <em>"World Character"</em>, as having a Planet, or
				even a solar system devoid of meaningful life, that begets
				meaningful interaction, is useless to us in the greater sense of
				game design and development. At least in regards to a sandbox game
				like SoA. While designing the <em>"World Character"</em> we keep to the core
				principles of sandbox design and the core feelings of the game that,
				as I mentioned earlier, we're trying to instill in our players. So,
				let's get started with an overview of our main storyline planet:
				Aldrin.</p>
			<h2>The Planet Aldrin</h2>

			<img class="blog-inline-image xx-large-wide image"
				src="/Assets/images/Blogs/Designing-World-Character-2014-03-17/Aldrin.jpg" />

			<p>
				Planet: Aldrin <br /> Name Origin: Edwin Eugene "Buzz" Aldrin, Jr. <br />
				Planet Type: Terrestrial <br />
			</p>

			<p>This planet pictured above, is the one that is the prime focus of
				our game and also the prime focus of this blog, at least in regards
				to the design of the <em>"World Character"</em>. So, let's move on now that
				you've got this little blue dot in front of you.</p>
			<h3>The Biosphere - Simulation</h3>
			<p>Moving forward we start talking about creating a 'biosphere' for
				our planet, and designing a dynamic simulation based system that
				will make this world feel alive. Let's start with the basics,
				talking about the simulation environment.</p>
			<h3>1. Micro Simulation</h3>
			<p>The Micro Simulation relates to anything we choose to simulate in
				regards to AI behavior, weather systems, etc., while the player is
				in the local area. We will define at a later point in development.</p>
			<h3>2. The Macro Simulation</h3>
			<p>The Macro Simulation relates to anything 'not seen', but implied
				around the planet. How do we simulate things that aren't loaded in
				to memory? This will be accomplished via an abstraction of local
				area data that the local micro simulation will use to populate the
				local area when the player enters it.</p>
			<p>How does this style of simulation actually affect game play you
				might ask? A simulation environment is a bit more complex to build,
				but entirely worth it in the long term in regards to making the
				world feel 'alive' and creating dynamic game play opportunities. An
				example would be that of groups of plant-eating animals coming
				together in a herd for protection, another, the migration patterns
				of animals to find more food sources.</p>
			<p>In the Macro Simulation we define large 'regions' as having X
				resources, and Y population of flora and fauna and we abstract this
				data and run 'update passes' on said data every so often. In the
				local simulation environment, the AI's are running their own
				internal scripts based on 'behaviors' and 'needs'.This creates
				extremely diverse and far ranging dynamic content.</p>
			<p>For example:</p>
			<p>An herbivore's internal clock for "Am I hungry?" counts down to
				zero, at this point we simply move into the bare bones basics of the
				behavior. Upon reaching zero we would scan the vicinity of the
				herbivore for food, if found, we would then move the herbivore to
				said food and have it consume the food. We would then remove that
				resource from that region's resource count.</p>

			<blockquote>
				<p>
					<code>
						<p>class Herbivore {</p>

						<p class="tab-indent">var scan = new Scanner();</p>
						<p class="tab-indent">var herbivore = null;</p>
						<p class="tab-indent">var hunger = null;</p>

						<p class="tab-indent">Herbivore( Animal animal ) {</p>
						<p class="tab-indent-2">herbivore = animal;</p>
						<p class="tab-indent-2">hunger = new Hunger();</p>
						<p class="tab-indent-2">hunger.hungerLevel = animal.initialHunger();</p>
						<p class="tab-indent">}</p>

						<p class="tab-indent">updateTick() {</p>
						<p class="tab-indent-2">hunger.updateTick();</p>
						<p class="tab-indent-2">handleHunger();</p>
						<p class="tab-indent">}</p>

						<p class="tab-indent">handleHunger() {</p>
						<p class="tab-indent-2">if( hunger.getHungerLevel() <= 0 ) {</p>
						<p class="tab-indent-3">var food = new Food();</p>
						<p class="tab-indent-3">var foodSource = scan.findFoodSource( herbivore.location() );</p>
				
				<p class="tab-indent-3">if( foodSource != false && food.isFood(foodSource) ) {</p>
				<p class="tab-indent-4">herbivore.moveTo( foodSource );</p>
				<p class="tab-indent-4">herbivore.consume( foodSource );</p>
				<p class="tab-indent-4">hunger += foodSource.fillAmount;</p>
				<p class="tab-indent-4">herbivore.region.depleteResource( foodSource );</p>
				<p class="tab-indent-3">}</p>
				<p class="tab-indent-2">}</p>
				<p class="tab-indent">}</p>

				<p>}</p>
				</code>
				</p>
			</blockquote>

			<p>Now the above is a far more bare bones, and inaccurate, code than
				I'd like it to be, but it should get the point across. If, for
				example, you want leather from cows, and you come to the realization
				that there are not a great deal of cows present in your local area,
				you may choose to hunt elsewhere, or attempt to cull local
				predators. When that happens, those predators are no longer present
				to hunt cows in the region, and as cows are now no longer being
				hunted down in your area, you get your leather. This entire
				simulation both on the macro and micro scale is designed to create a
				seemingly alive and realistic world full of dynamic content for you
				to enjoy.</p>
			<p>In the next part we'll talk about actually designing the
				biosphere, biodiversity, weather systems and their impact and more!
				Following dev blogs will discuss crafting, exploration, and more so
				stay tuned!</p>
		</div>
	</div>
	

	<div id="blog-post-footer">

		<p>Anthony "Damion Rayne" Keeton</p>

	</div>
</div>