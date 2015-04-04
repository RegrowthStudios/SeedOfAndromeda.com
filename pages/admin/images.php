<?php
if (isset ( $_REQUEST ['delete'] )) {
    $query = $connection->prepare ( "SELECT * FROM images WHERE id = ?" );
    $query->execute ( array (
		    $_REQUEST ['imageid'] 
    ) );
    $image = $query->fetch ();
	if (! $image) {
		echo '
            <div class="row clearfix">
                <div class="header"><h1>Image Manager - Error</h1></div>
                <div class="col double-col-2">
                    <div class="text">
                        <h3 class="error">Image not found or you don\'t have permissions to delete it!</h3>
                    </div>
                </div>';
    } else {
        foreach ( glob ( "assets/images/screenshots/*.jpg" ) as $img ) {
            if ( strpos ( $img, $_REQUEST ['imageid'] . "-" . clean_pageid ( str_replace ( " ", "-", $img ['title'] ) ) ) ) {
                unlink ( $img );
            }
        }
        $query = $connection->prepare ( "DELETE FROM images WHERE id = ?" );
        $query->execute ( array (
                $_REQUEST ['imageid']
        ) );
        echo '
            <div class="row clearfix">
                <div class="header"><h1>Image Manager</h1></div>
                <div class="col double-col-2">
                    <div class="text">
                        <div style="text-align:center;width:100%;"><h3><a style="color: white !important;" href="/' . $pageurl . '?images">Return</a></h3></div>
                    </div>
                </div>';
    }
} else if (isset ( $_REQUEST ['imageid'] )) {
    $query = $connection->prepare ( "SELECT * FROM images WHERE id = ?" );
    $query->execute ( array (
		    $_REQUEST ['imageid'] 
    ) );
    $image = $query->fetch ();
	if (! $image) {
		echo '
            <div class="row clearfix">
                <div class="header"><h1>Image Manager - Error</h1></div>
                <div class="col double-col-2">
                    <div class="text">
                        <h3 class="error">Image not found or you don\'t have permissions to edit it!</h3>
                    </div>
                </div>';
	} else {
		if (isset ( $_REQUEST ['submit'] )) {
                        
            if ( ! isset ( $_REQUEST ['title'] ) || ! isset ( $_REQUEST ['description'] )) {
				echo '
                    <div class="row clearfix">
                        <div class="header"><h1 class="error">Image Manager - Error</h1></div>
                        <div class="col double-col-2">
                            <div class="text">
                                <h3 class="error">Image title and description are required!</h3>
                            </div>
                        </div>';
            } else {
                if ( isset ( $_FILES['image'] ) && $_FILES['image']['size'] > 0 ) {
                            
                    $allowedExts = array("jpg");
                    $temp = explode(".", $_FILES['image']['name']);
                    $extension = end($temp);
                            
                    if (! in_array ( $extension, $allowedExts ) ) {
                        echo '
                            <div class="row clearfix">
                                <div class="header"><h1 class="error">Image Manager - Error</h1></div>
                                <div class="col double-col-2">
                                    <div class="text">
                                        <h3 class="error">Image file must be a .jpg!</h3>
                                    </div>
                                </div>';
                    } else if ( $_FILES['image']['error'] > 0 ) {
                        echo '
                            <div class="row clearfix">
                                <div class="header"><h1 class="error">Image Manager - Error</h1></div>
                                <div class="col double-col-2">
                                    <div class="text">
                                        <h3 class="error">Error: ' . $_FILES['image']['error'] . '</h3>
                                    </div>
                                </div>';
                    } else {
                                
                        if ( file_exists( $_SERVER{'DOCUMENT_ROOT'} . "/assets/images/screenshots/i" . $_REQUEST ['imageid'] . "." . $extension ) ) {
                            unlink ( $_SERVER{'DOCUMENT_ROOT'} . "/assets/images/screenshots/i" . $_REQUEST ['imageid'] . "." . $extension );
                        }
                        
                        move_uploaded_file( $_FILES['image']['tmp_name'],
                            $_SERVER{'DOCUMENT_ROOT'} . "/assets/images/screenshots/i" . $_REQUEST ['imageid'] . "." . $extension ); 
                               
                    }
                }
                $query = $connection->prepare ( "UPDATE images SET title = ?, description = ?, category = ?, updatetime = ?, published = ? WHERE id = ?" );
				$query->execute ( array (
						$_REQUEST ['title'],
						strip_tags($_REQUEST ['description']),
                        $_REQUEST ['category'],
						time (),
                        isset ( $_REQUEST ['published'] ) && $_REQUEST ['published'] == 1,
						$_REQUEST ['imageid']
				) );
                        
                header ( "Location: /" . $pageurl . "?images&imageid=" . $_REQUEST ['imageid'] );
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
        contextmenu: "link jbimages inserttable | cut copy paste | cell row column deletetable",
        image_advtab: true,
        add_unload_trigger: false,
        inline: true,
        statusbar: false,
        browser_spellcheck : true,
        relative_urls: false,
        remove_script_host: true,
        document_base_url: "/blogs/",
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
	action="/<?php echo $pageurl . '?images&imageid=' . $image ["id"]; ?>&submit&notemplate"
	enctype="multipart/form-data" method="post">
    <div class="row clearfix">
        <div class="header"><h1><p id="title" class="edittitle"><?php echo $image["title"];?></p></h1></div>
        <div class="col double-col-2">
            <div class="text">
		        <div id="description" class="editpost"><?php echo $image["description"];?></div>
                <br/>
                <label for="image">Image:</label> 
                <input id="image" value="1"
					type="file" name="image" />
                <br/><br/>
                <?php echo '<img class="img medium-wide right" style="margin-top:-2em;" src="' . $image["img_url"] . '" />'; ?>
                <div id="screenshot-category">
                    <div id="screenshot-category-header"><h1>Image Category:</h1></div>
                    <div id="screenshot-category-options">
                        <span>Gameplay:</span> <div class="checkbox"> <input id="category-gameplay" value="GAMEPLAY"
						    type="radio" name="category" <?php if( $image["category"] == "GAMEPLAY" ) echo "checked";?> />
						    <label for="category-gameplay"></label>
                        </div>
                        <br/>
                        <span>Concept:</span> <div class="checkbox"> <input id="category-concept" value="CONCEPT"
						    type="radio" name="category" <?php if( $image["category"] == "CONCEPT" ) echo "checked";?> />
						    <label for="category-concept"></label>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="row clearfix">
        <div class="divider"></div>
        <div class="col double-col-2">
            <div id="screenshot-settings" class="text clearfix">
                <span>Publish Image:</span> <div class="checkbox"> <input id="published" value="1"
				    type="checkbox" name="published" <?php if($image["published"] == "1") echo "checked";?> />
				    <label for="published"></label>
                </div>
                <br/><br/>
                <?php insertButton("Return", "/" . $pageurl . "?images"); ?>
                <input class="btn left" type="submit" value="Save" />
            </div>
        </div>
    </div>
</form>
<?php
        }
    }
} else if (isset ( $_REQUEST ['newimage'] )) {
	$query = $connection->prepare ( "INSERT INTO images (title, description, img_url, category, timestamp) VALUES (?, ?, ?, ?, ?)" );
	$query->execute ( array (
        "Image Title",
		"<p>Click here to write up a description of the screenshot.</p>",
        "empty",
        "GAMEPLAY",
		time ()
	) );
	$id = $connection->lastInsertId ();
    $query = $connection->prepare ( "UPDATE images SET img_url = ? WHERE id = " . $id );
    $query->execute ( array ( 
        "/assets/images/screenshots/i" . $id . ".jpg"
    ) );
	header ( "Location: /" . $pageurl . "?images&imageid=" . $id );
} else {
	echo '
    <div class="row clearfix">
        <div class="header"><h1>Image Manager</h1></div>
        <div class="col double-col-2">
            <div class="text">
                <br/>
                '; 
        insertButton("New Image", "/" . $pageurl . "?images&newimage&notemplate", "right");
        $query;
        if (isset ( $_REQUEST ['show'] ) && $_REQUEST ['show'] == "published") {
            insertButton("All Images", "/" . $pageurl . "?images", "right");
            insertButton("Private Images", "/" . $pageurl . "?images&show=private", "right");
            $query = $connection->prepare ( "SELECT * FROM images WHERE published = ? ORDER BY id DESC" );
		    $query->execute( array (
                1
            ) );
        } else if (isset ( $_REQUEST ['show'] ) && $_REQUEST ['show'] == "private") {
            insertButton("All Images", "/" . $pageurl . "?images", "right");
            insertButton("Public<br> Images", "/" . $pageurl . "?images&show=published", "right");
            $query = $connection->prepare ( "SELECT * FROM images WHERE published = ? ORDER BY id DESC" );
		    $query->execute( array (
                0
            ) );
        } else {
            insertButton("Private Images", "/" . $pageurl . "?images&show=private", "right");
            insertButton("Public<br> Images", "/" . $pageurl . "?images&show=published", "right");
            $query = $connection->prepare ( "SELECT * FROM images ORDER BY id DESC" );
		    $query->execute();
        }
        echo "<h2>Images:</h2><br/><br/><br/><br/>";
		while ( $row = $query->fetch() ) {
			echo '<div class="col quad-col-1"><img class="img medium admin-image" src="' . $row ["img_url"] . '"/><div class="admin-image-overlay">' . $row ["title"] . '<br/><a href="/' . $pageurl . '?images&imageid=' . $row ["id"] . '">Edit</a><br/><a onclick="return confirmAction(\'Are you sure you wish to delete this image? You will not be able to recover it.\');" href="/' . $pageurl . '?images&imageid=' . $row ["id"] . '&delete=1">Delete</a></div></div>';
		}
		echo '</ul>
        </div>
    </div>
    ';
}
?>