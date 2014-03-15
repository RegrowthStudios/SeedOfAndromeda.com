</div>
<div class="bottomimg"></div>

</div>

<div id="footer">Copyright &copy; <?php echo date("Y"); ?> SeedofAndromeda.com</div>

<!-- AddThis Smart Layers BEGIN -->

<!-- Go to http://www.addthis.com/get/smart-layers to customize -->

<!--<script type="text/javascript" src="//s7.addthis.com/js/300/addthis_widget.js#pubid=xa-52ad9c1a1c64f85f"></script>
<script type="text/javascript">
  addthis.layers({
    'theme' : 'transparent',
    'share' : {
      'position' : 'right',
      'numPreferredServices' : 5
    }   
  });
</script>-->

<!-- AddThis Smart Layers END -->



<!-- Google Analytics BEGIN-->

<script>
  (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
  (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
  m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
  })(window,document,'script','//www.google-analytics.com/analytics.js','ga');

  ga('create', 'UA-46451794-1', 'seedofandromeda.com');
  ga('send', 'pageview');

</script>

<!-- Google Analytics END-->



<!-- Downloads Tracking -->

<script type="text/javascript">
if (typeof jQuery != 'undefined') {
  jQuery(document).ready(function($) {
    var filetypes = /\.(zip|exe|dmg|pdf|doc.*|xls.*|ppt.*|mp3|txt|rar|wma|mov|avi|wmv|flv|wav)$/i;
    var baseHref = '';
    if (jQuery('base').attr('href') != undefined) baseHref = jQuery('base').attr('href');
 
    jQuery('a').on('click', function(event) {
      var el = jQuery(this);
      var track = true;
      var href = (typeof(el.attr('href')) != 'undefined' ) ? el.attr('href') :"";
      var isThisDomain = href.match(document.domain.split('.').reverse()[1] + '.' + document.domain.split('.').reverse()[0]);
      if (!href.match(/^javascript:/i)) {
        var elEv = []; elEv.value=0, elEv.non_i=false;
        if (href.match(/^mailto\:/i)) {
          elEv.category = "Email";
          elEv.action = "click";
          elEv.label = href.replace(/^mailto\:/i, '');
          elEv.loc = href;
        }
        else if (href.match(filetypes)) {
          var extension = (/[.]/.exec(href)) ? /[^.]+$/.exec(href) : undefined;
          elEv.category = "Download";
          elEv.action = "click-" + extension[0];
          elEv.label = href.replace(/ /g,"-");
          elEv.loc = baseHref + href;
        }
        else if (href.match(/^https?\:/i) && !isThisDomain) {
          elEv.category = "External";
          elEv.action = "click";
          elEv.label = href.replace(/^https?\:\/\//i, '');
          elEv.non_i = true;
          elEv.loc = href;
        }
        else if (href.match(/^tel\:/i)) {
          elEv.category = "Telephone";
          elEv.action = "click";
          elEv.label = href.replace(/^tel\:/i, '');
          elEv.loc = href;
        }
        else track = false;
 
        if (track) {
          _gaq.push(['_trackEvent', elEv.category.toLowerCase(), elEv.action.toLowerCase(), elEv.label.toLowerCase(), elEv.value, elEv.non_i]);
          if ( el.attr('target') == undefined || el.attr('target').toLowerCase() != '_blank') {
            setTimeout(function() { location.href = elEv.loc; }, 400);
            return false;
      }
    }
      }
    });
  });
}
</script>

<!-- Downloads Tracking END -->



<?php if($cleanpageid != "screenshots" && $cleanpageid != "videos" && $cleanpageid != "irc") { ?>



<div class="cover imgSlider" style="display: none;">

	<div class="imageFrame">

		<div class="close"></div>

		<div class="imgPrev">
			<img src="Assets/images/arrowLeft.png" />
		</div>

		<div class="imageFrameInner">

			<img class="enlargedImage" src="#" /> <img class="tempImage" src="#" />

		</div>

		<div class="imgNext">
			<img src="Assets/images/arrowRight.png" />
		</div>

		<div class="imgPos">X / X</div>

	</div>

</div>



<?php } ?>



<div class="cover registration-cover" style="display: none;">

	<div class="registration-form">

		<div class="close"></div>

		<div class="registration-outer">

			<h2>Create Account</h2>

			<form name="create" action="/Register_Function.php" method="post">

				<table>

					<tr>

						<td><h4>First Name*</h4></td>

						<td><input type="text" maxlength="40" id="reg_first_name"
							name="reg_first_name" placeholder="First Name" /></td>

					</tr>

					<tr>

						<td><h4>Last Name*</h4></td>

						<td><input type="text" maxlength="40" id="reg_last_name"
							name="reg_last_name" placeholder="Last Name" /></td>

					</tr>

					<tr>

						<td><h4>Email Address*</h4></td>

						<td><input type="text" maxlength="100" id="reg_email"
							name="reg_email" placeholder="Email" /></td>

					</tr>

					<tr>

						<td><h4>User Name*</h4></td>

						<td><input type="text" maxlength="40" id="reg_username"
							name="reg_username" placeholder="User Name" /></td>

					</tr>

					<tr>

						<td><h4>Password*</h4></td>

						<td><input type="password" maxlength="40" id="reg_password"
							name="reg_password" placeholder="Password" /></td>

					</tr>

					<tr>

						<td><h4>Confirm Password*</h4></td>

						<td><input type="password" maxlength="40"
							id="reg_confirm_password" name="reg_confirm_password"
							placeholder="Password" />
							</form></td>

					</tr>

					<tr>

						<td></td>

						<td><input style="float: right;" type="button"
							onClick="validate_create();" value="Create Account" /></td>

					</tr>

				</table>
			</form>

		</div>

	</div>

</div>

<script src="/soa.js?ver=4"></script>

<?php

if ($cleanpageid == "screenshots") 

{
	
	?>

<script src="/EmbeddedImageSlider.js?ver=5"></script>

<?php
} 

else if ($cleanpageid == "videos") 

{
	
	?>

<script src="/EmbeddedVideoSlider.js?ver=4"></script>

<?php
}

?>
</body>
</html>