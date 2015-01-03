<?php
if (isset ( $_REQUEST ['delete'] )) {
	$query = $connection->prepare ( "SELECT * FROM downloads WHERE id = ?" );
	$query->execute ( array (
			$_REQUEST ['downloadid'] 
	) );
	$download = $query->fetch ();
	if (! $download) {
		echo '
            <div class="row clearfix">
                <div class="header"><h1>Download Editor - Error</h1></div>
                <div class="col double-col-2">
                    <div class="text">
                        <h3 class="error">Download not found or you don\'t have permissions to delete it!</h3>
                    </div>
                </div>';
    } else {
        $query = $connection->prepare ( "DELETE FROM downloads WHERE id = ?" );
        $query->execute ( array (
                $_REQUEST ['downloadid']
        ) );
        echo '
            <div class="row clearfix">
                <div class="header"><h1>Blog Editor</h1></div>
                <div class="col double-col-2">
                    <div class="text">
                        <div style="text-align:center;width:100%;"><h3><a style="color: white !important;" href="/' . $pageurl . '?downloads">Return</a></h3></div>
                    </div>
                </div>';
    }
} else if (isset ( $_REQUEST ['downloadid'] )) {
	$query = $connection->prepare ( "SELECT * FROM downloads WHERE id = ?" );
	$query->execute ( array (
			$_REQUEST ['downloadid'] 
	) );
	$download = $query->fetch ();
	if (! $download) {
		echo '
            <div class="row clearfix">
                <div class="header"><h1>Download Editor - Error</h1></div>
                <div class="col double-col-2">
                    <div class="text">
                        <h3 class="error">Download not found or you don\'t have permissions to edit it!</h3>
                    </div>
                </div>';
	} else {
		if (isset ( $_REQUEST ['submit'] )) {
                        
            if (! isset ( $_REQUEST ['download-version'] ) || ! isset ( $_REQUEST ['download-description'] )) {
				echo '
                    <div class="row clearfix">
                        <div class="header"><h1 class="error">Download Editor - Error</h1></div>
                        <div class="col double-col-2">
                            <div class="text">
                                <h3 class="error">Download version and description are required!</h3>
                            </div>
                        </div>';
            } else {
                if ( ! file_exists( dirname( $_SERVER{'DOCUMENT_ROOT'} ) . "/files_seedofandromeda_com/game/" . $_REQUEST ['download-version'] ) ) {
                    mkdir ( dirname( $_SERVER{'DOCUMENT_ROOT'} ) . "/files_seedofandromeda_com/game/" . $_REQUEST ['download-version'], 0755, true );
                }
                if ( isset ( $_FILES['download'] ) && $_FILES['download']['size'] > 0 ) {
                            
                    $allowedExts = array("zip", "exe");
                    $temp = explode(".", $_FILES['download']['name']);
                    $extension = end($temp);
                            
                    if (! in_array ( $extension, $allowedExts ) ) {
                        echo '
                            <div class="row clearfix">
                                <div class="header"><h1 class="error">Download Editor - Error</h1></div>
                                <div class="col double-col-2">
                                    <div class="text">
                                        <h3 class="error">Download file must be a zip or executable!</h3>
                                    </div>
                                </div>';
                    } else if ( $_FILES['download']['error'] > 0 ) {
                        echo '
                            <div class="row clearfix">
                                <div class="header"><h1 class="error">Download Editor - Error</h1></div>
                                <div class="col double-col-2">
                                    <div class="text">
                                        <h3 class="error">Error: ' . $_FILES['download']['error'] . '</h3>
                                    </div>
                                </div>';
                    } else {
                                
                        if ( file_exists( dirname( $_SERVER{'DOCUMENT_ROOT'} ) . "/files_seedofandromeda_com/game/" . $_REQUEST ['download-version'] . "/SoA." . $extension ) ) {
                            unlink ( dirname( $_SERVER{'DOCUMENT_ROOT'} ) . "/files_seedofandromeda_com/game/" . $_REQUEST ['download-version'] . "/SoA." . $extension );
                        }
                                
                        move_uploaded_file($_FILES['download']['tmp_name'],
                            dirname( $_SERVER{'DOCUMENT_ROOT'} ) . "/files_seedofandromeda_com/game/" . $_REQUEST ['download-version'] . "/SoA." . $extension); 
                            
                    }
                }
                if ( isset ( $_FILES['bgImage'] ) && $_FILES['bgImage']['size'] > 0 ) {
                            
                    $allowedExts = array("jpg", "jpeg");
                    $temp = explode(".", $_FILES['bgImage']['name']);
                    $bgExtension = end($temp);
                            
                    if (! in_array ( $bgExtension, $allowedExts ) ) {
                        echo '
                            <div class="row clearfix">
                                <div class="header"><h1 class="error">Download Manager - Error</h1></div>
                                <div class="col double-col-2">
                                    <div class="text">
                                        <h3 class="error">Background image file must be a jpeg!</h3>
                                    </div>
                                </div>';
                    } else if ( $_FILES['bgImage']['error'] > 0 ) {
                        echo '
                            <div class="row clearfix">
                                <div class="header"><h1 class="error">Download Manager - Error</h1></div>
                                <div class="col double-col-2">
                                    <div class="text">
                                        <h3 class="error">Error: ' . $_FILES['bgImage']['error'] . '</h3>
                                    </div>
                                </div>';
                    } else {
                    
                        if ( file_exists( dirname( $_SERVER{'DOCUMENT_ROOT'} ) . "/files_seedofandromeda_com/game/" . $_REQUEST ['download-version'] . "/DlBackground." . $bgExtension ) ) {
                            unlink ( dirname ( $_SERVER{'DOCUMENT_ROOT'} ) . "/files_seedofandromeda_com/game/" . $_REQUEST ['download-version'] . "/DlBackground." . $bgExtension );
                        }
                                
                        move_uploaded_file( $_FILES['bgImage']['tmp_name'],
                            dirname( $_SERVER{'DOCUMENT_ROOT'} ) . "/files_seedofandromeda_com/game/" . $_REQUEST ['download-version'] . "/DlBackground." . $bgExtension ); 
                            
                    }
                }
                if ( isset ( $_FILES['download'] ) && $_FILES['download']['size'] > 0 ) {
                    if ( isset ( $_FILES['bgImage'] ) && $_FILES['bgImage']['size'] > 0 ) {
                        $query = $connection->prepare ( "UPDATE downloads SET version = ?, description = ?, url = ?, backgroundurl = ?, updatetime = ?, published = ? WHERE id = ?" );
				        $query->execute ( array (
						        $_REQUEST ['download-version'],
						        $_REQUEST ['download-description'],
						        "game/" . $_REQUEST ['download-version'] . "/SoA." . $extension,
						        "game/" . $_REQUEST ['download-version'] . "/DlBackground." . $bgExtension,
						        time (),
                                isset ( $_REQUEST ['published'] ) && $_REQUEST ['published'] == 1,
						        $_REQUEST ['downloadid']
				        ) );
                    } else {
                        $query = $connection->prepare ( "UPDATE downloads SET version = ?, description = ?, url = ?, updatetime = ?, published = ? WHERE id = ?" );
				        $query->execute ( array (
						        $_REQUEST ['download-version'],
						        $_REQUEST ['download-description'],
						        "game/" . $_REQUEST ['download-version'] . "/SoA." . $extension,
						        time (),
                                isset ( $_REQUEST ['published'] ) && $_REQUEST ['published'] == 1,
						        $_REQUEST ['downloadid']
				        ) );
                    }
                } else 
                    if ( isset ( $_FILES['bgImage'] ) && $_FILES['bgImage']['size'] > 0 ) {
                        $query = $connection->prepare ( "UPDATE downloads SET version = ?, description = ?, backgroundurl = ?, updatetime = ?, published = ? WHERE id = ?" );
				        $query->execute ( array (
						        $_REQUEST ['download-version'],
						        $_REQUEST ['download-description'],
						        "game/" . $_REQUEST ['download-version'] . "/DlBackground." . $bgExtension,
						        time (),
                                isset ( $_REQUEST ['published'] ) && $_REQUEST ['published'] == 1,
						        $_REQUEST ['downloadid']
				        ) );
                    } else {
                        $query = $connection->prepare ( "UPDATE downloads SET version = ?, description = ?, updatetime = ?, published = ? WHERE id = ?" );
				        $query->execute ( array (
						        $_REQUEST ['download-version'],
						        $_REQUEST ['download-description'],
						        time (),
                                isset ( $_REQUEST ['published'] ) && $_REQUEST ['published'] == 1,
						        $_REQUEST ['downloadid']
				        ) );
                    }
                
                }
                        
                header ( "Location: /" . $pageurl . "?downloads");
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
action="/<?php echo $pageurl . '?downloads&downloadid=' . $download ["id"]; ?>&submit&notemplate"
enctype="multipart/form-data" method="post">
    <div class="row clearfix">
        <div class="header"><h1><p id="download-version" class="edittitle"><?php echo $download["version"];?></p></h1></div>
        <div class="col double-col-2">
            <div class="text">
	            <div id="download-description" class="editpost"><?php echo $download["description"];?></div>
            </div>
        </div>
    </div>
    <div class="row clearfix">
        <div class="divider"></div>
        <div class="col double-col-2">
            <div id="download-settings" class="text clearfix">
                <label for="download">File:</label> 
                <input id="download" value="1"
		            type="file" name="download" />
                <br/>
                <br/>
                <label for="bgImage">Background Image:</label> 
                <input id="bgImage" value="1"
					type="file" name="bgImage" />
                <br/>
                <br/>
                <span>Publish Download:</span> <div class="checkbox"> <input id="published" value="1"
		            type="checkbox" name="published" <?php if($download["published"] == "1") echo "checked";?> />
		            <label for="published"></label>
                </div>
                <br/> 
                <br/>
                <?php echo '<a class="btn" href="/' . $pageurl . '?downloads">Return</a>'; ?>
                <input class="btn" type="submit" value="Save" />
            </div>
        </div>
    </div>
</form>
<?php
        }
    }
} else if (isset ( $_REQUEST ['newdownload'] )) {
	$query = $connection->prepare ( "INSERT INTO downloads (version, description, timestamp, url) VALUES (?, ?, ?, ?)" );
	$query->execute ( array (
			"0.0.0",
			"<p>Click here to write up a description of the download.</p>",
			time (),
			"game/0.0.0/SoA.zip"
	) );
                
	$id = $connection->lastInsertId ();
	header ( "Location: /" . $pageurl . "?downloads&downloadid=" . $id );
} else {
	echo '
    <div class="row clearfix">
        <div class="header"><h1>Download Editor</h1></div>
        <div class="col double-col-2">
            <div class="text">
                <a class="btn right" href="/' . $pageurl . '?downloads&newdownload&notemplate">New post</a>';
					
        $query = $connection->prepare ( "SELECT * FROM downloads ORDER BY id DESC" );
		$query->execute();
                    
        echo "<h2>Downloads:</h2><br><ul>";
		while ( $row = $query->fetch() ) {
			echo '<li>' . $row ["version"] . ' - <a href="/' . $pageurl . '?downloads&downloadid=' . $row ["id"] . '">Edit</a> - <a onclick="return confirmAction(\'Are you sure you wish to delete this blog?\');" href="/' . $pageurl . '?downloads&downloadid=' . $row ["id"] . '&delete=1">Delete</a></li>';
		}
		echo '</ul>
        </div>
    </div>
    ';
}
?>