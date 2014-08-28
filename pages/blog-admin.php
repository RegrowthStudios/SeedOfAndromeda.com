<?php
if (! $loggedIn) {
	echo '
        <div class="row clearfix">
            <div class="header"><h1 class="error">Blog Editor - Error</h1></div>
            <div class="col double-col-2">
                <div class="text">
                    <h3 class="error">Login requred to access this page!</h3>
                </div>
            </div>';
} else {
	if (! isset ( $connection )) {
		echo '
            <div class="row clearfix">
                <div class="header"><h1 class="error">Blog Editor - Error</h1></div>
                <div class="col double-col-2">
                    <div class="text">
                        <h3 class="error">No database connection!</h3>
                    </div>
                </div>';
	} else {
		// var_dump ( $userinfo );
		
		$groups = explode ( ",", $userinfo ["secondary_group_ids"] );
		$groups [] = $userinfo ["user_group_id"];
		
		$caneditown = false;
		$caneditall = false;
		
		// Groups that can edit/delete own posts:
		$editowngroups = array (
				7,
				13 
		); // 7 = Dev member, 13 = Dev leader
		   
		// Groups that can edit/delete all posts:
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
			echo '
                <div class="row clearfix">
                    <div class="header"><h1 class="error">Blog Editor - Error</h1></div>
                        <div class="col double-col-2">
                        <div class="text">
                            <h3 class="error">You don\'t have permissions to view this page!</h3>
                        </div>
                    </div>';
		} else {
            if (isset ( $_REQUEST ['delete'] )) {
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
					echo '
                        <div class="row clearfix">
                            <div class="header"><h1 class="error">Blog Editor - Error</h1></div>
                            <div class="col double-col-2">
                                <div class="text">
                                    <h3 class="error">Blog post not found or you don\'t have permissions to delete it!</h3><br /><a style="color: white !important;" href="/' . $pageurl . '">Return</a>
                                </div>
                            </div>';
                } else {
                    $query = $connection->prepare ( "DELETE FROM blog_posts WHERE id = ?" );
                    $query->execute ( array (
                            $_REQUEST ['postid']
                    ) );
                    echo '
                        <div class="row clearfix">
                            <div class="header"><h1>Blog Editor</h1></div>
                            <div class="col double-col-2">
                                <div class="text">
                                    <div style="text-align:center;width:100%;"><h3><a style="color: white !important;" href="/' . $pageurl . '">Return</a></h3></div>
                                </div>
                            </div>';
                }
            } else if (isset ( $_REQUEST ['postid'] )) {
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
					echo '
                        <div class="row clearfix">
                            <div class="header"><h1>Blog Editor - Error</h1></div>
                            <div class="col double-col-2">
                                <div class="text">
                                    <h3 class="error">Blog post not found or you don\'t have permissions to edit it!</h3>
                                </div>
                            </div>';
				} else {
					if (isset ( $_REQUEST ['submit'] )) {
						if (! isset ( $_REQUEST ['blog-post-title'] ) || ! isset ( $_REQUEST ['blog-post-content'] ) || ! isset ( $_REQUEST ['blog-brief'] )) {
							echo '
                                <div class="row clearfix">
                                    <div class="header"><h1 class="error">Blog Editor - Error</h1></div>
                                    <div class="col double-col-2">
                                        <div class="text">
                                            <h3 class="error">Blog post title, body and brief are required!</h3>
                                        </div>
                                    </div>';    
						} else {
                            $extension = "";
                            if ( isset ( $_FILES['dev-news-summary-background'] ) && $_FILES['dev-news-summary-background']['size'] > 0 ) {
                            
                                $allowedExts = array("jpg", "png", "jpeg", "gif");
                                $temp = explode(".", $_FILES['dev-news-summary-background']['name']);
                                $extension = end($temp);
                            
                                if (! in_array ( $extension, $allowedExts ) ) {
                                    echo '
                                        <div class="row clearfix">
                                            <div class="header"><h1 class="error">Download Editor - Error</h1></div>
                                            <div class="col double-col-2">
                                                <div class="text">
                                                    <h3 class="error">Image must be a jpeg, png or gif!</h3>
                                                </div>
                                            </div>';
                                } else if ( $_FILES['dev-news-summary-background']['error'] > 0 ) {
                                    echo '
                                        <div class="row clearfix">
                                            <div class="header"><h1 class="error">Download Editor - Error</h1></div>
                                            <div class="col double-col-2">
                                                <div class="text">
                                                    <h3 class="error">Error: ' . $_FILES['dev-news-summary-background']['error'] . '</h3>
                                                </div>
                                            </div>';
                                } else {
                                
                                    if ( ! file_exists( dirname ( $_SERVER{'DOCUMENT_ROOT'} ) . "seedofandromeda_com/assets/images/blogs/" . $_REQUEST ['postid'] . "-" . clean_pageid ( str_replace ( " ", "-", $_REQUEST ['blog-post-title'] ) ) ) ) {
                                        mkdir ( dirname ( $_SERVER{'DOCUMENT_ROOT'} ) . "seedofandromeda_com/assets/images/blogs/" . $_REQUEST ['postid'] . "-" . clean_pageid ( str_replace ( " ", "-", $_REQUEST ['blog-post-title'] ) ), 0755, true );
                                    } else if ( file_exists( dirname ( $_SERVER{'DOCUMENT_ROOT'} ) . "seedofandromeda_com/assets/images/blogs/" . $_REQUEST ['postid'] . "-" . clean_pageid ( str_replace ( " ", "-", $_REQUEST ['blog-post-title'] ) ) . "/DevNewsSummaryBackground." . $extension ) ) {
                                        unlink (dirname ( $_SERVER{'DOCUMENT_ROOT'} ) . "seedofandromeda_com/assets/images/blogs/" . $_REQUEST ['postid'] . "-" . clean_pageid ( str_replace ( " ", "-", $_REQUEST ['blog-post-title'] ) ) . "/DevNewsSummaryBackground." . $extension );
                                    }
                                
                                    move_uploaded_file($_FILES['dev-news-summary-background']['tmp_name'],
                                       dirname ( $_SERVER{'DOCUMENT_ROOT'} ) . "seedofandromeda_com/assets/images/blogs/" . $_REQUEST ['postid'] . "-" . clean_pageid ( str_replace ( " ", "-", $_REQUEST ['blog-post-title'] ) ) . "/DevNewsSummaryBackground." . $extension);
                                 
                                    //if ( ! file_exists( dirname ( $_SERVER{'DOCUMENT_ROOT'} ) . "/SoAWebDev/assets/images/blogs/" . $_REQUEST ['postid'] . "-" . clean_pageid ( str_replace ( " ", "-", $_REQUEST ['blog-post-title'] ) ) ) ) {
                                    //    mkdir ( dirname ( $_SERVER{'DOCUMENT_ROOT'} ) . "/SoAWebDev/assets/images/blogs/" . $_REQUEST ['postid'] . "-" . clean_pageid ( str_replace ( " ", "-", $_REQUEST ['blog-post-title'] ) ), 0755, true );
                                    //} else if ( file_exists( dirname ( $_SERVER{'DOCUMENT_ROOT'} ) . "/SoAWebDev/assets/images/blogs/" . $_REQUEST ['postid'] . "-" . clean_pageid ( str_replace ( " ", "-", $_REQUEST ['blog-post-title'] ) ) . "/DevNewsSummaryBackground." . $extension ) ) {
                                    //    unlink (dirname ( $_SERVER{'DOCUMENT_ROOT'} ) . "/SoAWebDev/assets/images/blogs/" . $_REQUEST ['postid'] . "-" . clean_pageid ( str_replace ( " ", "-", $_REQUEST ['blog-post-title'] ) ) . "/DevNewsSummaryBackground." . $extension );
                                    //}
                                
                                    //move_uploaded_file($_FILES['dev-news-summary-background']['tmp_name'],
                                    //   dirname ( $_SERVER{'DOCUMENT_ROOT'} ) . "/SoAWebDev/assets/images/blogs/" . $_REQUEST ['postid'] . "-" . clean_pageid ( str_replace ( " ", "-", $_REQUEST ['blog-post-title'] ) ) . "/DevNewsSummaryBackground." . $extension);
   
                                }
                            }
                            
							$query = $connection->prepare ( "UPDATE blog_posts SET title = ?, post_body = ?, post_brief = ?, updatetime = ?, disablecomments = ?, published = ?, devnews = ?, anonymous = ?, removesignoff = ?, dev_news_body = ?, dev_news_background = ?, prioritisescreenshots = ?, hidescreenshots = ?, draftIsLatest = ? WHERE id = ?" );
							$query->execute ( array (
									$_REQUEST ['blog-post-title'],
									$_REQUEST ['blog-post-content'],
									$_REQUEST ['blog-brief'],
									time (),
									isset ( $_REQUEST ['commentsoff'] ) && $_REQUEST ['commentsoff'] == 1,
									isset ( $_REQUEST ['publish'] ) && $_REQUEST ['publish'] == 1,
									isset ( $_REQUEST ['devnews'] ) && $_REQUEST ['devnews'] == 1,
									isset ( $_REQUEST ['anonymous'] ) && $_REQUEST ['anonymous'] == 1,
									isset ( $_REQUEST ['no-sign-off'] ) && $_REQUEST ['no-sign-off'] == 1,
                                    $_REQUEST ['dev-news-summary-content'],
                                    ("/assets/images/blogs/" . $_REQUEST ['postid'] . "-" . clean_pageid ( str_replace ( " ", "-", $_REQUEST ['blog-post-title'] ) ) . "/DevNewsSummaryBackground." . $extension),
                                    isset ( $_REQUEST ['prioritisescreenshots'] ) && $_REQUEST ['prioritisescreenshots'] == 1,
                                    isset ( $_REQUEST ['hidescreenshots'] ) && $_REQUEST ['hidescreenshots'] == 1,
									0,
                                    $_REQUEST ['postid']
							) );
							header ( "Location: /" . $pageurl . "?postid=" . $_REQUEST ['postid'] );
						}
					} else {
						$author = $sdk->getUser ( $blogpost ["author"] );
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
                        { title: 'Medium Wide', value: 'img medium-wide'},
                        { title: 'No', value: 'img xxx-small' },
                        { title: 'Tiny', value: 'img xx-small' },
                        { title: 'Very Small', value: 'img x-small' },
                        { title: 'Small', value: 'img small' },
                        { title: 'Medium', value: 'img medium'},
                        { title: 'Large', value: 'img large' },
                        { title: 'Very Large', value: 'img x-large' },
                        { title: 'Huge', value: 'img xx-large' },
                        { title: 'Gigantic', value: 'img xxx-large' },
                        { title: 'No Wide', value: 'img xxx-small-wide' },
                        { title: 'Tiny Wide', value: 'img xx-small-wide' },
                        { title: 'Very Small Wide', value: 'img x-small-wide' },
                        { title: 'Small Wide', value: 'img small-wide' },
                        { title: 'Large Wide', value: 'img large-wide' },
                        { title: 'Very Large Wide', value: 'img x-large-wide' },
                        { title: 'Huge Wide', value: 'img xx-large-wide' },
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
                                echo "{title: '" . pathinfo($file,PATHINFO_BASENAME) . "', value: '/assets/images/blogs/" . pathinfo($file,PATHINFO_BASENAME) . "'},";
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
	    enctype="multipart/form-data" method="post">
        <div class="row clearfix">
            <div class="header"><h1><p id="blog-post-title" class="edittitle"><?php echo $blogpost["title"];?></p></h1></div>
            <div class="col double-col-2">
                <div class="text">
	                <div id="blog-post" class="clearfix">
		                <div id="blog-post-content" class="editpost">
                            <?php 
                                if ( $blogpost["draftIsLatest"] == 1 ) {
                                    echo $blogpost["draft"];
                                } else {
                                    echo $blogpost["post_body"];
                                }
                            ?>
                        </div>
                        <span id="blog-post-footer" class="right">
                            <?php
                                if(! $blogpost["removesignoff"]) {
                                    if($blogpost["anonymous"]) {
                                        echo "Seed of Andromeda Team";
                                    } else {
                            ?>
			                    <?php echo $author["username"]." - ".$author["custom_title"];?>
                            <?php 
                                    }
                                }
                            ?>
                        </span>
	                </div>
                </div>
            </div>
        </div>
        <div class="row clearfix">
            <div class="divider"></div>
            <div class="col double-col-2">
                <div class="text">
                    <div style="width: 100%;">
                        <h2>Blog Brief:</h2>
                        <div id="blog-brief" class="editpost"><?php echo $blogpost["post_brief"];?></div>
                    </div>
                </div>
            </div>
        </div>
        <div id="dev-news-summary-content-cover" class="row clearfix" <?php if($blogpost["devnews"] != "1") echo "style='display: none;'";?>>
            <div class="divider"></div>
            <div class="col double-col-2">
                <div class="text">
                    <div style="width: 100%;">
                        <h2>Dev News Summary:</h2>
                        <div id="dev-news-summary-content" class="editpost"><?php echo $blogpost["dev_news_body"];?></div>
                    </div>
                    <div style="width: 100%;">
                        <label for="dev-news-summary-background"><h3>Dev News Background Image:</h3></label> 
                        <br />
                        <input id="dev-news-summary-background" value="1"
					        type="file" name="dev-news-summary-background" />
                        <?php echo '<img class="img medium-wide right" style="margin-top:-2em;" src="' . $blogpost["dev_news_background"] . '" />'; ?>
                    </div>
                </div>
            </div>
        </div>
        <div class="row clearfix">
            <div class="divider"></div>
            <div class="col double-col-2">
                <div id="post-settings" class="text clearfix">
		            <h3>Post settings</h3>
                    <br/>
                    <div id="blog-settings">
                        <span>Disable Commenting:</span> <div class="checkbox"> <input id="commentsoff" value="1"
						    type="checkbox" name="commentsoff" <?php if($blogpost["disablecomments"] == "1") echo "checked";?> />
						    <label for="commentsoff"></label>
                        </div>
                        <br/>
                        <span>Publish Blog Post:</span> <div class="checkbox"> <input id="publish" value="1"
						    type="checkbox" name="publish" <?php if($blogpost["published"] == "1") echo "checked";?> />
						    <label for="publish"></label>
                        </div>
                        <br/>
                        <span>Publish Blog to Dev News:</span> <div class="checkbox"> <input id="devnews" value="1"
						    type="checkbox" name="devnews" <?php if($blogpost["devnews"] == "1") echo "checked";?> />
						    <label for="devnews"></label>
                        </div>
                        <br/>
                        <span>Prioritise Screenshots:</span> <div class="checkbox"> <input id="prioritisescreenshots" value="1"
						    type="checkbox" name="prioritisescreenshots" <?php if($blogpost["prioritisescreenshots"] == "1") echo "checked";?> />
						    <label for="prioritisescreenshots"></label>
                        </div>
                        <br/>
                        <span>Hide Screenshot Slider:</span> <div class="checkbox"> <input id="hidescreenshots" value="1"
						    type="checkbox" name="hidescreenshots" <?php if($blogpost["hidescreenshots"] == "1") echo "checked";?> />
						    <label for="hidescreenshots"></label>
                        </div>
                        <br/>
                        <span>Publish Anonymously:</span> <div class="checkbox"> <input id="anonymous" value="1"
						    type="checkbox" name="anonymous" <?php if($blogpost["anonymous"] == "1") echo "checked";?> />
						    <label for="anonymous"></label>
                        </div>
                        <br/>
                        <span>Publish with No Sign Off:</span> <div class="checkbox"> <input id="no-sign-off" value="1"
						    type="checkbox" name="no-sign-off" <?php if($blogpost["removesignoff"] == "1") echo "checked";?> />
						    <label for="anonymous"></label>
                        </div> 
                    </div>
                    <br /> 
                    <?php echo '<a class="btn" href="/' . $pageurl . '">Return</a>'; ?>
                    <input class="btn" type="submit" value="Save" />
	            </div>
            </div>
        </div>
    </form>
<?php
					}
				}
			} elseif (isset ( $_REQUEST ['newpost'] )) {
				
				$query = $connection->prepare ( "INSERT INTO blog_posts (author, title, timestamp, post_body, post_brief, dev_news_body, dev_news_background) VALUES (?, ?, ?, ?, ?, ?, ?)" );
				$query->execute ( array (
						$userinfo ['user_id'],
						"New blog post",
						time (),
						"<h2>Click here to edit!</h2><p>Click the title to edit it.</p>",
                        "<p>Click here to write up a brief.</p>",
                        "<p>Click here to edit!</p>",
                        "assets/images/blogs/default/Plains.jpg"
				) );
				
				$id = $connection->lastInsertId ();
				header ( "Location: /" . $pageurl . "?postid=" . $id );
			} else {
				echo '
                <div class="row clearfix">
                    <div class="header"><h1>Blog Editor</h1></div>
                    <div class="col double-col-2">
                        <div class="text">';
				if ($caneditown) {
					
					echo '<a class="btn right" href="/' . $pageurl . '?newpost&notemplate">New post</a>';
					
					$query = $connection->prepare ( "SELECT * FROM blog_posts WHERE author = ? ORDER BY id DESC" );
					$query->execute ( array (
							$userinfo ['user_id'] 
					) );
					echo "<h2>Your blog posts:</h2><br><ul>";
					while ( $row = $query->fetch () ) {
						echo '<li>' . $row ["title"] . ' - <a href="/' . $pageurl . '?postid=' . $row ["id"] . '">Edit</a> - <a onclick="return confirmAction(\'Are you sure you wish to delete this blog?\');" href="/' . $pageurl . '?postid=' . $row ["id"] . '&delete=1">Delete</a></li>';
					}
					
					echo "</ul>";
				}
				if ($caneditall) {
					$query = $connection->prepare ( "SELECT * FROM blog_posts WHERE author != ? ORDER BY id DESC" );
					$query->execute ( array (
							$userinfo ['user_id'] 
					) );
					echo '
                        </div>
                    </div>
                </div>
                <div class="row clearfix">
                    <div class="divider"></div>
                    <div class="col double-col-2">
                        <div class="text">
                            <h2>Blog posts by others:</h2><br><ul>
                    ';
					while ( $row = $query->fetch () ) {
						$author = $sdk->getUser ( $row ["author"] );
						echo '<li>' . $row ["title"] . ' by ' . $author ["username"] . ' - <a href="/' . $pageurl . '?postid=' . $row ["id"] . '">Edit</a> - <a onclick="return confirmAction(\'Are you sure you wish to delete this blog?\');" href="/' . $pageurl . '?postid=' . $row ["id"] . '&delete=1">Delete</a></li></li>';
					}
					echo "</ul>";
				}
                echo '
                    </div>
                </div>
                ';
			}
		}
	}
}
?>