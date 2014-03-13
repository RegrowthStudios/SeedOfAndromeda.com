
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
			<a href="/blog">Read More...</a>
		</p>

	</div>

</div>