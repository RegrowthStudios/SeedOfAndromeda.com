<?php
if (isset ( $connection )) {
	if (isset ( $blogpost )) {
		$author = $sdk->getUser ($blogpost["author"]);
		?>

<div id="single-blog" class="double-col empty">
	<div id="blog-post-header">
		<p><?php echo $blogpost["title"];?></p>
	</div>
	<div id="blog-post-body" style="padding-top: 40px;">
		<?php echo $blogpost["post_body"];?>
	</div>
	<div id="blog-post-footer">
		<p><?php echo $author["username"]." - ".$author["custom_title"];?></p>
	</div>
</div>
<?php
		
		echo_disqus ( $blogpost ["title"], $cleanpageurl, $blogpost ["id"] );
	} else {
		$query = $connection->prepare ( "SELECT * FROM blog_posts" );
		$query->execute ();
		
		while ( $row = $query->fetch () ) {
			$postlink = gen_postlink ( $row );
			echo '
<div class="double-col empty">
	<div id="blog-post-header">
		<p>' . $row ["title"] . '</p>
	</div>
	<div id="blog-post-body">
		<p>asd asd</p>
	</div>
	<div id="blog-post-footer">
		<p>
			<a
				href="/blogs/' . $postlink . '">Read
				More...</a> <small> - (<a
				href="http://www.seedofandromeda.com/blogs/' . $postlink . '#disqus_thread" data-disqus-identifier="' . $row ["id"] . '">Comments</a>)
			</small>
		</p>
	</div>
</div>

	';
		}
		
		?>

<script type="text/javascript"> var disqus_shortname = 'seedofandromeda';
  (function () { var s = document.createElement('script'); s.async = true; s.type = 'text/javascript'; s.src = 'http://' + disqus_shortname + '.disqus.com/count.js'; (document.getElementsByTagName('HEAD')[0] || document.getElementsByTagName('BODY')[0]).appendChild(s); }()); </script>

<?php
	}
} else {
	echo '<div class="double-col empty"><h3 style="color: red; text-shadow: 0px 0px 10px rgba(255, 0, 0, 1);">No database connection!</h3></div>';
}

?>

