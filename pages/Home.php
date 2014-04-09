
<div class="double-col">

	<a href="/Assets/images/Screenshots/Mountains.jpg"
		data-lightbox="images" title="Mountains" class="clear right image"><img
		src="/Assets/images/Screenshots/Mountains_thumb_125x100.jpg"
		class="clear right image" /></a>

	<h3>Try The Game</h3>

	<br /> The pre-alpha version of the game can be downloaded <a
		href="/downloads">here</a>!

</div>

<br />

<div class="tri-col-3">

	<h3>About the Game</h3>

	Seed of Andromeda is a voxel based sandbox RPG. Set in the near future,
	the player crash lands on a planet with a harsh environment. In the
	desire to have a way to return to their mission, the player may be able
	to build up technologically and regain space flight, with the help of
	other survivors! The game focusses on modability and customisation,
	many tools will come packaged with the game, including world, tree,
	biome and block editors! <br /> <br /> <a href="/thegame"
		style="float: right;">Read more here!</a>

</div>

<div class="tri-double-col">

	<h3>Featured Video</h3>
	<div id="featured_video"></div>
	<script type="text/javascript">
    function showVideo(response) {
        if(response.data && response.data.items) {
            var items = response.data.items;
            if(items.length>0) {
                var item = items[0];
                var videoid = "https://www.youtube.com/embed/"+item.id+"?wmode=transparent";
                console.log("Latest ID: '"+videoid+"'");
                var video = "<iframe width='580' height='326' src='"+videoid+"' frameborder='0' allowfullscreen></iframe>"; 
                $('#featured_video').html(video);
            }
        }
    }
    </script>
	<script type="text/javascript"
		src="https://gdata.youtube.com/feeds/api/users/UCMlW2qG20hcFYo06rcit4CQ/uploads?max-results=1&orderby=published&v=2&alt=jsonc&callback=showVideo"></script>


</div>

<div class="double-col">

	<h3>Latest Dev News</h3>
	<br /> 
	
	<a
	href="/Assets/images/Screenshots/SavannaSillohuette.jpg" data-lightbox="images"
	title="Sillohuette of the Savanna" class="clear left image">
		<img src="/Assets/images/Screenshots/SavannaSillohuette_thumb_125x100.jpg"
		class="clear left image" />
	</a>
	Seed of Andromeda version 0.1.6 is incoming, with huge performance boosts
	and some much needed bug fixes. One of the most immense changes Ben has made is in 
	decoupling Physics and Player FPS, allowing for smooth control of the player even 
	when explosives are going going off left, right and center! Our two talented artists,
	Andreas and Georg have been working on two phenomenal texture packs, and have been adding 
	more textures and blocks than can be listed here!
	<br />
	<a
	href="/Assets/images/Screenshots/planet4.jpg" data-lightbox="images"
	title="Aldrin" class="clear left image">
		<img src="/Assets/images/Screenshots/planet4_thumb_125x100.jpg"
		class="clear left image" />
	</a>
	<br /> 
	On the website side of the coin, Matthew is currently working on setting up the new
	forum system, while Jesse has been optimising the sites code to make everything a
	much greater experience for users - no matter their internet connection (dial-up 
	excluded!).
	<br /> 
	<a
	href="/Assets/images/Screenshots/Kolasi.jpg" data-lightbox="images"
	title="Kolasi" class="clear left image">
		<img src="/Assets/images/Screenshots/Kolasi_thumb_125x100.jpg"
		class="clear left image" />
	</a>
	<br />
	Our designers have been pondering the depths of game mechanics, and some of this can be
	seen in Anthony's blogs showing off the method by which our worlds will develop and behave! 
	Sebastian has been working on some concepts for the PDA - a core component of the player-game 
	interaction.
	<br />
	<br />
	In the time between work, we have also been having a game of Civilisation which is 
	drawing to a close, so be on the lookout in the IRC and in the Steam group for announcements of 
	when we will be starting another game - we will be looking to play a large game after our exams 
	no doubt!
</div>
