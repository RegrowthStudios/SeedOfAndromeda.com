
<div class="double-col empty">
	<div id="blog-post-header">

		<p>Crafting, Research and Intergroup Cooperation - Volume I.I</p>

	</div>

	<div id="blog-post-body">


		<p>
			To start this volume off, I would like to open up with a comment on what 
			I consider to be good practice in creating an immersive experience for the 
			player. Firstly, I do not believe in the use of GUIs where there is no reason 
			or explanation for the use of them: they simply work to slow down the 
			flow of the game, and they do not reflect real-life processes (if they are 
			without explanation). The latter point is of great concern when the 
			crafting process has to incorporate things as advanced as the technologies 
			that will be attainable and creatable in SoA. Take the example of Minecraft, 
			its crafting system is unintuitive, lacks any form of scalability and so for 
			that reason, almost every mod either has to introduce a whole new crafting 
			system for its own items, or it has to have an expansive wiki that players 
			refer to for every creation. This issue runs nicely into the second good 
			practice I believe in: keeping the player in-game and enthused to do so at 
			all times. This means, rather than just not allowing wikis to be created, 
			or making alt+tab kill the game to discourage the use of wikis, instead, 
			make sure your system is so engaging - and so rewarding - that the player 
			finds themselves wanting to beat the system rather than let others beat it 
			for them. This won't stop all players, but it will do what you should only 
			ever aspire to do as a developer of a sandbox game: encourage a play-style 
			rather than force it.
		</p>



	</div>

	<div id="blog-post-footer">

		<p>
			<a href="/blogs/crafting-research-and-intergroup-cooperation-volume-one-part-one">Read More...</a>
		</p>

	</div>
	
	<div id="blog-post-header">

		<p>Designing the World Character</p>

	</div>

	<div id="blog-post-body">


		<p>
			Designing a sandbox game is one of the most intensive, yet rewarding
			experiences I've had in my few short years in the games industry. I'd
			like to share some of my design concepts, principles and the general
			direction I plan to work with when designing SoA alongside Ben and
			Sebastian. First is the concept of "Pillars of Design" or those areas
			of building a game that are built around the "core feeling" and major
			game-play concepts. Every game is first designed with a very simple
			question, with very simple answers...<br /> So of these base feelings
			we can design game-play features and functionality. If we want the
			player to have this sense of freedom to explore, and a desire to
			explore, we must first give them a world worth exploring. The same
			follows suit with the rest of the base feelings we want to instill,
			as well as holding to the three basic principles of the sandbox;
			which are Interactivity, Creativity, and Exploration. Simple, we
			design the "World Character", as having a Planet, or even a solar
			system devoid of meaningful life, that begets meaningful interaction,
			is useless to us in the greater sense of game design and development.
			At least in regards to a sandbox game like SoA. While designing the
			"World Character" we keep to the core principles of sandbox design
			and the core feelings of the game that, as I mentioned earlier, we're
			trying to instill in our players. So, let's get started with an
			overview of our main storyline planet: Aldrin...
		</p>



	</div>

	<div id="blog-post-footer">

		<p>
			<a href="/blogs/designing-the-world-character">Read More...</a>
		</p>

	</div>

</div>
<div class="double-col empty">

	<div id="blog-post-header">

		<p>Creating a Region File System for a Voxel Game</p>

	</div>

	<div id="blog-post-body">



		<p>Voxel worlds are typically organized into pages of data called
			chunks. In Seed Of Andromeda, I have chosen to use a chunk size of
			32^3. In contrast, the minecraft chunk size is 16x16x256 [1]. When
			designing a saving and loading for your game, you need to be
			interested not only in the speed of your IO, but the resulting file
			size. Nobody is going to want to play your game if the save file
			takes up gigabytes upon gigabytes of disk space, and lags when saving
			data! The naive method would be to save each chunk in its own file.
			We could save each file using the chunks coordinates to construct the
			file name. Something like s.X.Y.Z.dat could work. I went ahead and
			implemented this to see just how bad the performance was... It was
			about as bad as it can get. When saving 2475 chunks to disk, each in
			their own file, it took about 70 seconds to save them all without any
			compression. With run length encoding compression, it was brought
			down to about 30 seconds, which means on average each chunk took 12ms
			to save. That is unacceptable! Why is it so slow? It is slow because
			we have to open and close 2475 file handles, causing a lot of
			blocking as we wait for the OS to open the files. So how can we speed
			this up? Since the obvious issue is that we are using too many files,
			we should try to pack many chunks into a single file. We could try to
			put all of the chunks in one file, but that would cause the file to
			get very large. As a file gets larger, some of the operations such as
			resizing a chunk...</p>



	</div>

	<div id="blog-post-footer">

		<p>
			<a href="/blogs/creating-a-region-file-system-for-a-voxel-game">Read
				More...</a>
		</p>

	</div>

</div>