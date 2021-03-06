<?php
if (isset ( $_REQUEST ['delete'] )) {
	$query = $connection->prepare ( "SELECT * FROM videos WHERE id = ?" );
	$query->execute ( array (
			$_REQUEST ['videoid'] 
	) );
	$video = $query->fetch ();
    if (! $video) {
	    echo '
            <div class="row clearfix">
                <div class="header"><h1 class="error">Video Manager - Error</h1></div>
                <div class="col double-col-2">
                    <div class="text">
                        <h3 class="error">Video not found or you don\'t have permissions to delete it!</h3><br /><a style="color: white !important;" href="/' . $pageurl . '?videos">Return</a>
                    </div>
                </div>';
    } else {
        $query = $connection->prepare ( "DELETE FROM videos WHERE id = ?" );
        $query->execute ( array (
                $_REQUEST ['videoid']
        ) );
        echo '
            <div class="row clearfix">
                <div class="header"><h1>Video Manager</h1></div>
                <div class="col double-col-2">
                    <div class="text">
                        <div style="text-align:center;width:100%;"><h3><a style="color: white !important;" href="/' . $pageurl . '?videos">Return</a></h3></div>
                    </div>
                </div>';
    }
} else if (isset ( $_REQUEST ['videoid'] )) {
	$query = $connection->prepare ( "SELECT * FROM videos WHERE id = ?" );
	$query->execute ( array (
			$_REQUEST ['videoid'] 
	) );
	$video = $query->fetch ();
    if (! $video) {
	    echo '
            <div class="row clearfix">
                <div class="header"><h1>Video Manager - Error</h1></div>
                <div class="col double-col-2">
                    <div class="text">
                        <h3 class="error">Video not found or you don\'t have permissions to edit it!</h3>
                    </div>
                </div>';
    } else {
	    if (isset ( $_REQUEST ['submit'] )) {
		    if (! isset ( $_REQUEST ['video-title'] ) || ! isset ( $_REQUEST ['video-url'] )|| ! isset ( $_REQUEST ['category'] )) {
			    echo '
                    <div class="row clearfix">
                        <div class="header"><h1 class="error">Video Manager - Error</h1></div>
                        <div class="col double-col-2">
                            <div class="text">
                                <h3 class="error">Video title, url, and category are required!</h3>
                            </div>
                        </div>';    
		    } else {
                $vOffset = strpos($_REQUEST ['video-url'], "?v=");
                $vidID = substr($_REQUEST ['video-url'], $vOffset + 3);
                $thumburl = "https://i.ytimg.com/vi/" . $vidID . "/maxresdefault.jpg";
                            
			    $query = $connection->prepare ( "UPDATE videos SET title = ?, vid_id = ?, thumb_url = ?, category = ?, updatetime = ?, published = ? WHERE id = ?" );
			    $query->execute ( array (
					    $_REQUEST ['video-title'],
					    $vidID,
					    $thumburl,
					    $_REQUEST ['category'],
					    time (),
					    isset ( $_REQUEST ['publish'] ) && $_REQUEST ['publish'] == 1,
                        $_REQUEST ['videoid']
			    ) );
			    header ( "Location: /" . $pageurl . "?videos&videoid=" . $_REQUEST ['videoid'] );
		    }
	    } else {
?>
<script src="./tinymce/tinymce.min.js"></script>
<script>
    tinymce.init({
    selector: "div.editpost",
    //theme: "modern",
    skin: "darktheme",
    plugins: [
        "advlist autolink lists link image charmap hr anchor pagebreak",
        "searchreplace wordcount visualblocks visualchars code fullscreen",
        "insertdatetime media nonbreaking save table contextmenu directionality",
        "emoticons template paste textcolor"
    ],
    external_plugins: {
        "jbimages": "/jbimages/plugin.min.js"
    },
    toolbar1: "bold italic | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | blockquote code",
    //toolbar2: "blockquote code | emoticons link media image jbimages",
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
        { title: 'Freeform', value: 'img' },
        { title: 'Tote', value: 'img xxx-small' },
        { title: 'Tote Wide', value: 'img xxx-small-wide' },
        { title: 'Tiny', value: 'img xx-small' },
        { title: 'Tiny Wide', value: 'img xx-small-wide' },
        { title: 'Very Small', value: 'img x-small' },
        { title: 'Very Small Wide', value: 'img x-small-wide' },
        { title: 'Small', value: 'img small' },
        { title: 'Small Wide', value: 'img small-wide' },
        { title: 'Medium', value: 'img medium'},
        { title: 'Medium Wide', value: 'img medium-wide'},
        { title: 'Large', value: 'img large' },
        { title: 'Large Wide', value: 'img large-wide' },
        { title: 'Very Large', value: 'img x-large' },
        { title: 'Very Large Wide', value: 'img x-large-wide' },
        { title: 'Huge', value: 'img xx-large' },
        { title: 'Huge Wide', value: 'img xx-large-wide' },
        { title: 'Gigantic', value: 'img xxx-large' },
        { title: 'Gigantic Wide', value: 'img xxx-large-wide' }
    ],
    image_list: [
<?php
        $di = new RecursiveDirectoryIterator("assets/images/blogs/",RecursiveDirectoryIterator::SKIP_DOTS);
        $it = new RecursiveIteratorIterator($di);
        foreach($it as $file)
        {
            if( pathinfo($file,PATHINFO_EXTENSION) == "jpg" || pathinfo($file,PATHINFO_EXTENSION) == "png" || pathinfo($file,PATHINFO_EXTENSION) == "gif" ) {
                echo "{title: '" . pathinfo($file,PATHINFO_BASENAME) . "', value: '/assets/images/blogs/" . pathinfo($file,PATHINFO_BASENAME) . "'},";
            }
        }
        $di = new RecursiveDirectoryIterator("assets/images/screenshots/",RecursiveDirectoryIterator::SKIP_DOTS);
        $it = new RecursiveIteratorIterator($di);
        foreach($it as $file)
        {
            if( pathinfo($file,PATHINFO_EXTENSION) == "jpg" || pathinfo($file,PATHINFO_EXTENSION) == "png" || pathinfo($file,PATHINFO_EXTENSION) == "gif" ) {
                echo "{title: '" . pathinfo($file,PATHINFO_BASENAME) . "', value: '/assets/images/screenshots/" . pathinfo($file,PATHINFO_BASENAME) . "'},";
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
	action="/<?php echo $pageurl . '?videos&videoid=' . $video["id"]; ?>&submit&notemplate"
	method="post">
    <div class="row clearfix">
        <div class="header"><h1><p id="video-title" class="edittitle"><?php echo $video["title"];?></p></h1></div>
        <div class="col double-col-2">
            <div class="text">
	            <input type="text" name="video-url" <?php echo 'value="https://www.youtube.com/?v=' . $video["vid_id"] . '"'; ?> />
                <br/><br/>
                <div id="video-category">
                    <div id="video-category-header"><h1>Video Category:</h1></div>
                    <div id="video-category-options">
                        <span>Stream:</span> <div class="checkbox"> <input id="category-stream" value="STREAM"
						    type="radio" name="category" <?php if( $video["category"] == "STREAM" ) echo "checked";?> />
						    <label for="category-stream"></label>
                        </div>
                        <br/>
                        <span>Featured:</span> <div class="checkbox"> <input id="category-featured" value="FEATURED"
						    type="radio" name="category" <?php if( $video["category"] == "FEATURED" ) echo "checked";?> />
						    <label for="category-featured"></label>
                        </div>
                    </div>
                </div>
                <br/>
            </div>
        </div>
    </div>
    <div class="row clearfix">
        <div class="divider"></div>
        <div class="col double-col-2">
            <div id="post-settings" class="text clearfix">
		        <h3>Video Settings</h3>
                <br/>
                <div id="blog-settings">
                    <span>Publish Video:</span> <div class="checkbox"> <input id="publish" value="1"
						type="checkbox" name="publish" <?php if($video["published"] == "1") echo "checked";?> />
						<label for="publish"></label>
                    </div>
                </div>
                <br /> 
                <?php insertButton("Return", "/" . $pageurl . "?videos"); ?>
                <input class="btn left" type="submit" value="Save" />
	        </div>
        </div>
    </div>
</form>
<?php
		}
	}
} elseif (isset ( $_REQUEST ['newvideo'] )) {
				
	$query = $connection->prepare ( "INSERT INTO videos (title, vid_id, thumb_url, category, timestamp) VALUES (?, ?, ?, ?, ?)" );
	$query->execute ( array (
			"Video Title",
			"Video URL",
            "empty",
            "STREAM",
			time ()
	) );
				
	$id = $connection->lastInsertId ();
	header ( "Location: /" . $pageurl . "?videos&videoid=" . $id );
} else {
	echo '
    <div class="row clearfix">
        <div class="header"><h1>Video Manager</h1></div>
        <div class="col double-col-2">
            <div class="text">
                ' . insertButton("New Videos", "/" . $pageurl . "?videos&newimage&notemplate", "right");
                
		$query = $connection->prepare ( "SELECT * FROM videos ORDER BY id DESC" );
		$query->execute();
		echo '
                <h2>Videos:</h2><br><ul>
        ';
		while ( $row = $query->fetch() ) {
			echo '<li>' . $row ["title"] . ' - <a href="/' . $pageurl . '?videos&videoid=' . $row ["id"] . '">Edit</a> - <a onclick="return confirmAction(\'Are you sure you wish to delete this video? You will not be able to recover it.\');" href="/' . $pageurl . '?videos&videoid=' . $row ["id"] . '&delete=1">Delete</a></li></li>';
		}
		echo '  </ul>
            </div>
        </div>
    </div>
    ';
}
?>