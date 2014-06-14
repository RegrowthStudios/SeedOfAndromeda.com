<?php
if (! isset ( $_REQUEST ['notemplate'] )) {
	echo '<div id="single-blog" class="double-col empty">';
}
if (! $loggedIn) {
	echo '<h3 style="color: red; text-shadow: 0px 0px 10px rgba(255, 0, 0, 1);">Login requred to access this page!</h3>';
} else {
	if (! isset ( $connection )) {
		echo '<h3 style="color: red; text-shadow: 0px 0px 10px rgba(255, 0, 0, 1);">No database connection!</h3>';
	} else {
		// var_dump ( $userinfo );
		
		$groups = explode ( ",", $userinfo ["secondary_group_ids"] );
		$groups [] = $userinfo ["user_group_id"];
		
		$caneditown = false;
		$caneditall = false;
		
		// Groups that can edit own posts:
		$editowngroups = array (
				7,
				13 
		); // 7 = Dev member, 13 = Dev leader
		   
		// Groups that can edit all posts:
		$editallgroups = array (
				3 
		); // 3 = Admins
		
		foreach ( $editallgroups as $groupid ) {
			if (in_array ( $groupid, $groups )) {
				$caneditall = true;
				$caneditown = true;
				break;
			}
		}
		if (! $caneditown) {
			foreach ( $editowngroups as $groupid ) {
				if (in_array ( $groupid, $groups )) {
					$caneditall = true;
					$caneditown = true;
					break;
				}
			}
		}
		if (! $caneditown && ! $caneditall) {
			echo '<h3 style="color: red; text-shadow: 0px 0px 10px rgba(255, 0, 0, 1);">You don\'t have permissions to view this page!</h3>';
		} else {
			if (isset ( $_REQUEST ['postid'] )) {
				$blogpost = false;
				if ($caneditall) {
					$query = $connection->prepare ( "SELECT * FROM blog_posts WHERE id = ?" );
					$query->execute ( array (
							$_REQUEST ['postid'] 
					) );
					$blogpost = $query->fetch ();
				} elseif ($caneditown) {
					
					$query = $connection->prepare ( "SELECT * FROM blog_posts WHERE id = ? AND author = ?" );
					$query->execute ( array (
							$_REQUEST ['postid'],
							$userinfo ['user_id'] 
					) );
					$blogpost = $query->fetch ();
				}
				if (! $blogpost) {
					echo '<h3 style="color: red; text-shadow: 0px 0px 10px rgba(255, 0, 0, 1);">Blog post not found or you don\'t have permissions to edit it!</h3>';
				} else {
					if (isset ( $_REQUEST ['submit'] )) {
						// var_dump ( $_REQUEST );
						if (! isset ( $_REQUEST ['blog-post-title'] ) || ! isset ( $_REQUEST ['blog-post-content'] )) {
							echo '<h3 style="color: red; text-shadow: 0px 0px 10px rgba(255, 0, 0, 1);">Blog post title and body are required!</h3>';
						} else {
							$query = $connection->prepare ( "UPDATE blog_posts SET title = ?, post_body = ?, updatetime = ?, disablecomments = ?, published = ? WHERE id = ?" );
							$query->execute ( array (
									$_REQUEST ['blog-post-title'],
									$_REQUEST ['blog-post-content'],
									time (),
									isset ( $_REQUEST ['commentsoff'] ) && $_REQUEST ['commentsoff'] == 1,
									isset ( $_REQUEST ['publish'] ) && $_REQUEST ['publish'] == 1,
									$_REQUEST ['postid'] 
							) );
							header ( "Location: /" . $pageurl . "?postid=" . $_REQUEST ['postid'] );
						}
					} else {
						$author = $sdk->getUser ( $blogpost ["author"] );
						?>
<script src="//tinymce.cachefly.net/4.0/tinymce.min.js"></script>
<script>
    tinymce.init({
    selector: "div.editpost",
    theme: "modern",
    plugins: [
              "advlist autolink lists link image charmap hr anchor pagebreak",
              "searchreplace wordcount visualblocks visualchars code fullscreen",
              "insertdatetime media nonbreaking save table contextmenu directionality",
              "emoticons template paste textcolor"
          ],
    external_plugins: {
        "jbimages": "/jbimages/plugin.min.js"
    },
    toolbar1: "styleselect | bold italic | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent",
    toolbar2: "save insertfile undo redo cut copy paste | forecolor backcolor emoticons | link media image jbimages",
    contextmenu: "link image jbimages inserttable | cut copy paste | cell row column deletetable",
    image_advtab: true,
    add_unload_trigger: false,
    inline: true,
    statusbar: false,
    browser_spellcheck : true,
    relative_urls: false,
    remove_script_host: true,
    document_base_url: "/blogs/",
    image_class_list: [
                       {title: 'None', value: ''},
                       {title: 'Large', value: 'large'},
                       {title: 'X-large', value: 'x-large'},
                       {title: 'XX-large', value: 'xx-large'},
                       {title: 'XXX-large', value: 'xxx-large'},
                       {title: 'Large wide', value: 'large-wide'},
                       {title: 'X-large wide', value: 'x-large-wide'},
                       {title: 'XX-large wide', value: 'xx-large-wide'},
                       {title: 'XXX-large wide', value: 'xxx-large-wide'}
                   ],
    image_list: [
                 <?php
						foreach ( glob ( "Assets/images/Blogs/*.*" ) as $filename ) {
							$file = pathinfo ( $filename );
							if ($file ["extension"] == "jpg" || $file ["extension"] == "png" || $file ["extension"] == "gif") {
								echo "{title: '" . $file ["basename"] . "', value: '/Assets/images/Blogs/" . $file ["basename"] . "'},";
							}
						}
						
						?>
    ],
});
    tinymce.init({
        selector: "p.edittitle",
        theme: "modern",
        inline: true,
        plugins: [
                  "save"
              ],
        toolbar: "save undo redo",
        statusbar: false,
        menubar: false,
        valid_elements : "dummyelem"
    });
    </script>
<form
	action="/<?php echo $pageurl . '?postid=' . $blogpost ["id"]; ?>&submit&notemplate"
	method="post">
	<div id="blog-post-header">
		<p id="blog-post-title" class="edittitle"><?php echo $blogpost["title"];?></p>
	</div>
	<div id="blog-post-body" style="padding-top: 40px;">
		<div class="editpost" id="blog-post-content"><?php echo $blogpost["post_body"];?></div>
	</div>
	<div id="blog-post-footer">
		<p>
			<?php echo $author["username"]." - ".$author["custom_title"];?>
		</p>
	</div>
	<div>
		<h3>Post settings</h3>
		<input type="checkbox" name="commentsoff" value="1"
			<?php if($blogpost["disablecomments"] == "1") echo "checked";?> />
		Disable commenting<br /> <input type="checkbox" name="publish"
			value="1" <?php if($blogpost["published"] == "1") echo "checked";?> />
		Publish blog post<br /> <input type="submit" value="Save" />
	</div>
</form>
<?php
					}
				}
			} elseif (isset ( $_REQUEST ['newpost'] )) {
				
				$query = $connection->prepare ( "INSERT INTO blog_posts (author, title, timestamp, post_body) VALUES (?, ?, ?, ?)" );
				$query->execute ( array (
						$userinfo ['user_id'],
						"New blog post",
						time (),
						"<h2>Click here to edit</h2><p>Click the title to edit it.</p>" 
				) );
				
				$id = $connection->lastInsertId ();
				header ( "Location: /" . $pageurl . "?postid=" . $id );
			} else {
				echo '<h3>Blog admin panel</h3>';
				if ($caneditown) {
					
					echo '<p><a href="/' . $pageurl . '?newpost&notemplate">New post</a></p>';
					
					$query = $connection->prepare ( "SELECT * FROM blog_posts WHERE author = ? ORDER BY id DESC" );
					$query->execute ( array (
							$userinfo ['user_id'] 
					) );
					echo "Your blog posts:<br><ul>";
					while ( $row = $query->fetch () ) {
						echo '<li>' . $row ["title"] . ' - <a href="/' . $pageurl . '?postid=' . $row ["id"] . '">Edit</a></li>';
					}
					
					echo "</ul>";
				}
				if ($caneditall) {
					$query = $connection->prepare ( "SELECT * FROM blog_posts WHERE author != ? ORDER BY id DESC" );
					$query->execute ( array (
							$userinfo ['user_id'] 
					) );
					
					echo "Blog posts by others:<br><ul>";
					while ( $row = $query->fetch () ) {
						$author = $sdk->getUser ( $row ["author"] );
						echo '<li>' . $row ["title"] . ' by ' . $author ["username"] . ' - <a href="/' . $pageurl . '?postid=' . $row ["id"] . '">Edit</a></li>';
					}
					echo "</ul>";
				}
			}
		}
	}
}
if (! isset ( $_REQUEST ['notemplate'] )) {
	echo '</div>';
}
?>