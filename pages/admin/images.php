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
                        
            if (! isset ( $_REQUEST ['title'] ) || ! isset ( $_REQUEST ['description'] )) {
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
                            
                    $allowedExts = array("jpg", "jpeg");
                    $temp = explode(".", $_FILES['image']['name']);
                    $extension = end($temp);
                            
                    if (! in_array ( $extension, $allowedExts ) ) {
                        echo '
                            <div class="row clearfix">
                                <div class="header"><h1 class="error">Image Manager - Error</h1></div>
                                <div class="col double-col-2">
                                    <div class="text">
                                        <h3 class="error">Image file must be a jpeg!</h3>
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
                                
                        if ( ! file_exists( dirname ( $_SERVER{'DOCUMENT_ROOT'} ) . "seedofandromeda_com/assets/images/screenshots/" . $_REQUEST ['imageid'] . "-" . clean_pageid ( str_replace ( " ", "-", $_REQUEST ['title'] ) ) . $extension ) ) {
                            mkdir ( dirname ( $_SERVER{'DOCUMENT_ROOT'} ) . "seedofandromeda_com/assets/images/screenshots/" . $_REQUEST ['imageid'] . "-" . clean_pageid ( str_replace ( " ", "-", $_REQUEST ['title'] ) ) . $extension, 0755, true );
                        } else if ( file_exists( dirname ( $_SERVER{'DOCUMENT_ROOT'} ) . "seedofandromeda_com/assets/images/screenshots/" . $_REQUEST ['imageid'] . "-" . clean_pageid ( str_replace ( " ", "-", $_REQUEST ['title'] ) ) . $extension ) ) {
                            unlink (dirname ( $_SERVER{'DOCUMENT_ROOT'} ) . "seedofandromeda_com/assets/images/screenshots/" . $_REQUEST ['imageid'] . "-" . clean_pageid ( str_replace ( " ", "-", $_REQUEST ['title'] ) ) . $extension );
                        }
                                
                        move_uploaded_file( $_FILES['image']['tmp_name'],
                            dirname ( $_SERVER{'DOCUMENT_ROOT'} ) . "seedofandromeda_com/assets/images/screenshots/" . $_REQUEST ['imageid'] . "-" . clean_pageid ( str_replace ( " ", "-", $_REQUEST ['title'] ) ) . $extension ); 
                            
                        //if ( ! file_exists( dirname ( $_SERVER{'DOCUMENT_ROOT'} ) . "/SoAWebDev/assets/images/screenshots/" ) ) {
                        //    mkdir ( dirname ( $_SERVER{'DOCUMENT_ROOT'} ) . "/SoAWebDev/assets/images/screenshots/", 0755, true );
                        //} else if ( file_exists( dirname ( $_SERVER{'DOCUMENT_ROOT'} ) . "/SoAWebDev/assets/images/screenshots/" . $_REQUEST ['imageid'] . "-" . clean_pageid ( str_replace ( " ", "-", $_REQUEST ['title'] ) ) . "." . $extension ) ) {
                        //    unlink (dirname ( $_SERVER{'DOCUMENT_ROOT'} ) . "/SoAWebDev/assets/images/screenshots/" . $_REQUEST ['imageid'] . "-" . clean_pageid ( str_replace ( " ", "-", $_REQUEST ['title'] ) ) . "." . $extension );
                        //}
                         
                        //move_uploaded_file( $_FILES['image']['tmp_name'],
                        //   dirname ( $_SERVER{'DOCUMENT_ROOT'} ) . "/SoAWebDev/assets/images/screenshots/" . $_REQUEST ['imageid'] . "-" . clean_pageid ( str_replace ( " ", "-", $_REQUEST ['title'] ) ) . "." . $extension ); 
                                    
                    }
                } else {
                    $q = $connection->prepare ( "SELECT * FROM images WHERE id = ?");
                    $q->execute ( array ( 
                        $_REQUEST ['imageid']
                    ) );
                    $img = $q->fetch();
                    foreach ( glob ( "assets/images/screenshots/*.jpg" ) as $image ) {
                        if ( strpos ( $image, $_REQUEST ['imageid'] . "-" . clean_pageid ( str_replace ( " ", "-", $img ['title'] ) ) ) ) {
                            $temp = explode(".", $img["img_url"]);
                            $extension = end($temp);
                            rename ( dirname ( $_SERVER{'DOCUMENT_ROOT'} ) . "/SoAWebDev" . $img["img_url"], dirname ( $_SERVER{'DOCUMENT_ROOT'} ) . "/SoAWebDev/assets/images/screenshots/" . $_REQUEST ['imageid'] . "-" . clean_pageid ( str_replace ( " ", "-", $_REQUEST ['title'] ) ) . "." . $extension );
                            break;
                        }
                    }
                }
                $query = $connection->prepare ( "UPDATE images SET title = ?, description = ?, img_url = ?, category = ?, updatetime = ?, published = ? WHERE id = ?" );
				$query->execute ( array (
						$_REQUEST ['title'],
						strip_tags($_REQUEST ['description']),
						"/assets/images/screenshots/" . $_REQUEST ['imageid'] . "-" . clean_pageid ( str_replace ( " ", "-", $_REQUEST ['title'] ) ) . "." . $extension,
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
	action="/<?php echo $pageurl . '?images&imageid=' . $image ["id"]; ?>&submit&notemplate"
	enctype="multipart/form-data" method="post">
    <div class="row clearfix">
        <div class="header"><h1><p id="title" class="edittitle"><?php echo $image["title"];?></p></h1></div>
        <div class="col double-col-2">
            <div class="text">
		        <div id="description" class="editpost"><?php echo $image["description"];?></div>
                <br/>
                <label for="image">Screenshot:</label> 
                <input id="image" value="1"
					type="file" name="image" />
                <br/><br/>
                <?php echo '<img class="img medium-wide right" style="margin-top:-2em;" src="' . $image["img_url"] . '" />'; ?>
                <div id="screenshot-category">
                    <div id="screenshot-category-header"><h1>Screenshot Category:</h1></div>
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
                <span>Publish Screenshot:</span> <div class="checkbox"> <input id="published" value="1"
				    type="checkbox" name="published" <?php if($image["published"] == "1") echo "checked";?> />
				    <label for="published"></label>
                </div>
                <br/><br/>
                <?php echo '<a class="btn" href="/' . $pageurl . '?images">Return</a>'; ?>
                <input class="btn" type="submit" value="Save" />
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
			"Screenshot Title",
			"<p>Click here to write up a description of the screenshot.</p>",
            "empty",
            "GAMEPLAY",
			time ()
	) );
                
	$id = $connection->lastInsertId ();
	header ( "Location: /" . $pageurl . "?images&imageid=" . $id );
} else {
	echo '
    <div class="row clearfix">
        <div class="header"><h1>Image Manager</h1></div>
        <div class="col double-col-2">
            <div class="text">
                <br/>
                <a class="btn right" href="/' . $pageurl . '?images&newimage&notemplate">New Image</a>';
					
        $query = $connection->prepare ( "SELECT * FROM images ORDER BY id DESC" );
		$query->execute();
                    
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