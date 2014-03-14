
<div class="double-col">

	<h3 style="text-align: center; font-size: 1.6em;">Screenshots</h3>

	<div class="img-prev">
		<img src="Assets/images/arrowLeft.png" />
	</div>

	<div id="image-frame-inner">

		<img src="#" class="temp-image" /> <img src="#" class="enlarged-image" />

	</div>

	<div class="img-next">
		<img src="Assets/images/arrowRight.png" />
	</div>

	<br />

</div>

<?php
$i = 0;

foreach ( glob ( "Assets/images/Screenshots/*.jpg" ) as $image ) {
	$i ++;
	echo '<div class="image-col quad-col-'.$i.' empty"><img src="' . $image . '" class="image"/></div>';
	if ($i == 4) {
		$i = 0;
	}
}

?>