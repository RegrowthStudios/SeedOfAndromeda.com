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
                        <br/><br/>
                        <h3><a href="/' . $pageurl . '?downloads">Return</a></h3>
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
                        <br/><br/>
                        <h3><a href="/' . $pageurl . '?downloads&downloadid=' . $_REQUEST ['downloadid'] . '">Return</a></h3>
                    </div>
                </div>';
	} else {
		if (isset ( $_REQUEST ['submit'] )) {
            $success = true;   
            if (! isset ( $_REQUEST ['download-version'] ) || ! isset ( $_REQUEST ['download-description'] )) {
				echo '
                    <div class="row clearfix">
                        <div class="header"><h1 class="error">Download Editor - Error</h1></div>
                        <div class="col double-col-2">
                            <div class="text">
                                <h3 class="error">Download version and description are required!</h3>
                                <br/><br/>
                                <h3><a href="/' . $pageurl . '?downloads&downloadid=' . $_REQUEST ['downloadid'] . '">Return</a></h3>
                            </div>
                        </div>';
                $success = false;   
            } else {
                if ( ! file_exists( dirname( $_SERVER{'DOCUMENT_ROOT'} ) . "/files_seedofandromeda_com/game/" . $_REQUEST ['downloadid'] ) ) {
                    mkdir ( dirname( $_SERVER{'DOCUMENT_ROOT'} ) . "/files_seedofandromeda_com/game/" . $_REQUEST ['downloadid'], 0755, true );
                }
                $extension;
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
                                        <br/><br/>
                                        <h3><a href="/' . $pageurl . '?downloads&downloadid=' . $_REQUEST ['downloadid'] . '">Return</a></h3>
                                    </div>
                                </div>';
                        $success = false;  
                    } else if ( $_FILES['download']['error'] > 0 ) {
                        echo '
                            <div class="row clearfix">
                                <div class="header"><h1 class="error">Download Editor - Error</h1></div>
                                <div class="col double-col-2">
                                    <div class="text">
                                        <h3 class="error">Error: ' . $_FILES['download']['error'] . '</h3>
                                        <br/><br/>
                                        <h3><a href="/' . $pageurl . '?downloads&downloadid=' . $_REQUEST ['downloadid'] . '">Return</a></h3>
                                    </div>
                                </div>';
                        $success = false;  
                    } else {
                                
                        if ( file_exists( dirname( $_SERVER{'DOCUMENT_ROOT'} ) . "/files_seedofandromeda_com/game/" . $_REQUEST ['downloadid'] . "/SoA." . $extension ) ) {
                            unlink ( dirname( $_SERVER{'DOCUMENT_ROOT'} ) . "/files_seedofandromeda_com/game/" . $_REQUEST ['downloadid'] . "/SoA." . $extension );
                        }
                                
                        move_uploaded_file($_FILES['download']['tmp_name'],
                            dirname( $_SERVER{'DOCUMENT_ROOT'} ) . "/files_seedofandromeda_com/game/" . $_REQUEST ['downloadid'] . "/SoA." . $extension); 
                            
                    }
                }
                if ( isset ( $_FILES['bgImage'] ) && $_FILES['bgImage']['size'] > 0 ) {
                            
                    $allowedExts = array("jpg");
                    $temp = explode(".", $_FILES['bgImage']['name']);
                    $bgExtension = end($temp);
                            
                    if (! in_array ( $bgExtension, $allowedExts ) ) {
                        echo '
                            <div class="row clearfix">
                                <div class="header"><h1 class="error">Download Manager - Error</h1></div>
                                <div class="col double-col-2">
                                    <div class="text">
                                        <h3 class="error">Background image file must be a .jpg!</h3>
                                        <br/><br/>
                                        <h3><a href="/' . $pageurl . '?downloads&downloadid=' . $_REQUEST ['downloadid'] . '">Return</a></h3>
                                    </div>
                                </div>';
                        $success = false;  
                    } else if ( $_FILES['bgImage']['error'] > 0 ) {
                        echo '
                            <div class="row clearfix">
                                <div class="header"><h1 class="error">Download Manager - Error</h1></div>
                                <div class="col double-col-2">
                                    <div class="text">
                                        <h3 class="error">Error: ' . $_FILES['bgImage']['error'] . '</h3>
                                        <br/><br/>
                                        <h3><a href="/' . $pageurl . '?downloads&downloadid=' . $_REQUEST ['downloadid'] . '">Return</a></h3>
                                    </div>
                                </div>';
                        $success = false;  
                    } else {
                    
                        if ( file_exists( dirname( $_SERVER{'DOCUMENT_ROOT'} ) . "/files_seedofandromeda_com/game/" . $_REQUEST ['downloadid'] . "/DlBackground.jpg" ) ) {
                            unlink ( dirname ( $_SERVER{'DOCUMENT_ROOT'} ) . "/files_seedofandromeda_com/game/" . $_REQUEST ['downloadid'] . "/DlBackground.jpg" );
                        }
                                
                        move_uploaded_file( $_FILES['bgImage']['tmp_name'],
                            dirname( $_SERVER{'DOCUMENT_ROOT'} ) . "/files_seedofandromeda_com/game/" . $_REQUEST ['downloadid'] . "/DlBackground.jpg" ); 
                            
                    }
                }
                if (isset ( $_REQUEST ['published'] ) && $_REQUEST ['published'] == 1) {
                    if ( ! file_exists( dirname( $_SERVER{'DOCUMENT_ROOT'} ) . "/files_seedofandromeda_com/game/" . $_REQUEST ['downloadid'] . "/SoA." . $extension ) ) {
                        echo '
                            <div class="row clearfix">
                                <div class="header"><h1 class="error">Download Manager - Error</h1></div>
                                <div class="col double-col-2">
                                    <div class="text">
                                        <h3 class="error">A download must be provided to publish!</h3>
                                        <br/><br/>
                                        <h3><a href="/' . $pageurl . '?downloads&downloadid=' . $_REQUEST ['downloadid'] . '">Return</a></h3>
                                    </div>
                                </div>';
                        $success = false;  
                    } else if ( ! file_exists( dirname( $_SERVER{'DOCUMENT_ROOT'} ) . "/files_seedofandromeda_com/game/" . $_REQUEST ['downloadid'] . "/DlBackground.jpg" ) ) {
                        echo '
                            <div class="row clearfix">
                                <div class="header"><h1 class="error">Download Manager - Error</h1></div>
                                <div class="col double-col-2">
                                    <div class="text">
                                        <h3 class="error">A download background must be provided to publish!</h3>
                                        <br/><br/>
                                        <h3><a href="/' . $pageurl . '?downloads&downloadid=' . $_REQUEST ['downloadid'] . '">Return</a></h3>
                                    </div>
                                </div>';
                        $success = false;  
                    }
                }
                if ( $success ) {
                    if ( isset ( $_FILES['download'] ) && $_FILES['download']['size'] > 0 ) {
                        $query = $connection->prepare ( "UPDATE downloads SET version = ?, description = ?, url = ?, updatetime = ?, published = ? WHERE id = ?" );
				        $query->execute ( array (
						        $_REQUEST ['download-version'],
						        $_REQUEST ['download-description'],
						        "game/" . $_REQUEST ['downloadid'] . "/SoA." . $extension,
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
                    header ( "Location: /" . $pageurl . "?downloads");
                }
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
            "advlist autolink lists link charmap hr anchor pagebreak",
            "searchreplace wordcount visualblocks visualchars code fullscreen",
            "insertdatetime media nonbreaking save table contextmenu directionality",
            "emoticons template paste textcolor"
        ],
        external_plugins: {
        "jbimages": "/jbimages/plugin.min.js"
        },
        toolbar1: "bold italic | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | blockquote code",
        //toolbar2: "blockquote code | emoticons link media jbimages",
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
                <?php echo '<div class="btn"><a href="/' . $pageurl . '?downloads">Return</a></div>'; ?>
                <input class="btn left" type="submit" value="Save" />
            </div>
        </div>
    </div>
</form>
<?php
        }
    }
} else if (isset ( $_REQUEST ['newdownload'] )) {
	$query = $connection->prepare ( "INSERT INTO downloads (version, description, timestamp) VALUES (?, ?, ?)" );
	$query->execute ( array (
		"0.0.0",
		"<p>Click here to write up a description of the download.</p>",
		time ()
	) );
	$id = $connection->lastInsertId ();
    $query = $connection->prepare ( "UPDATE downloads SET backgroundurl = ? WHERE id = ?" );
    $query->execute ( array (
		"game/" . $id . "/DlBackground.jpg",
        $id
    ) );
	header ( "Location: /" . $pageurl . "?downloads&downloadid=" . $id );
} else {
	echo '
    <div class="row clearfix">
        <div class="header"><h1>Download Editor</h1></div>
        <div class="col double-col-2">
            <div class="text">
                <div class="right btn"><a href="/' . $pageurl . '?downloads&newdownload&notemplate">New post</a></div>';
					
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