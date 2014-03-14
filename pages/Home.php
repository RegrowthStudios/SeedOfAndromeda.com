
<div class="double-col">

	<img src="/Assets/images/Screenshots/Mountains.jpg"
		class="clear right image" />

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
	biome and block editors! <br /> <br /> <a
		href="/thegame"
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
                var videoid = "https://www.youtube.com/embed/"+item.id;
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

	<br /> The long awaited public release of Seed of Andromeda came near
	the end of 2013. Serving to the players a main of realistic physics and
	a side of bugs. Straight after this release, Ben began work on solving
	reported bugs and bringing performance up to an even more incredible
	standard than the initial pre-alpha release. <br /> <br /> <img
		src="/Assets/images/Screenshots/Blossoms.jpg" class="clear left image" />

	Meanwhile, the Seed of Andromeda website got an overhaul thanks to the
	new Website Designer, Matthew, with the assistance of Sebastian's
	PhotoShop skills. The initial result was a huge increase in cat images
	and a reduction in useful information. Though that has been quickly
	changing over the last few days. <br /> <br /> Keep an eye on this
	space, as within the next few weeks you will be seeing updates on the
	game. In the mean time, have a good new year and if you haven't
	already, drop in to the IRC and say hi to fellow SoA supporters and the
	dev team! Also don't forget the Steam group where we occasionally
	organise games of Civilization V and Planetside 2!

</div>
