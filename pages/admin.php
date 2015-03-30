<?php
    if (!$loggedIn) {
    
        echo '
            <div class="row clearfix">
                <div class="header"><h1 class="error">Admin - Error</h1></div>
                <div class="col double-col-2">
                    <div class="text">
                        <h3 class="error">Login requred to access this page!</h3>
                    </div>
                </div>';
                
    } else {
    
        if (!isset($connection)) {
        
		    echo '
                <div class="row clearfix">
                    <div class="header"><h1 class="error">Admin - Error</h1></div>
                    <div class="col double-col-2">
                        <div class="text">
                            <h3 class="error">No database connection!</h3>
                        </div>
                    </div>';
                    
        } else {
        
            if (!$canAccessAdmin) {
            
                echo '
                    <div class="row clearfix">
                        <div class="header"><h1 class="error">Admin - Error</h1></div>
                            <div class="col double-col-2">
                            <div class="text">
                                <h3 class="error">You don\'t have permissions to view this page!</h3>
                            </div>
                        </div>';
                        
            } else {
            
                if (isset ($_REQUEST['blogs'])) {
                
                    $canAccessBlogs = false;
                    foreach (array_unique( array_merge($manageOwnBlogsGroups, $manageAllBlogsGroups) ) as $groupId) {
                        if (in_array($groupId, $groups)) {
                            $canAccessBlogs = true;
                            break;
                        }
                    }
                    
                    if (!$canAccessBlogs) {
                    
                        echo '
                            <div class="row clearfix">
                                <div class="header"><h1 class="error">Blogs Manager - Error</h1></div>
                                    <div class="col double-col-2">
                                    <div class="text">
                                        <h3 class="error">You don\'t have permissions to view this page!</h3>
                                    </div>
                                </div>';
                                
                    } else {
                        
                        include ("pages/admin/blogs.php");
                    
                    }
                    
                } else if (isset ($_REQUEST['downloads'])) {
                
                    $canAccessDownloads = false;
                    foreach ($manageDownloadsGroups as $groupId) {
                        if (in_array($groupId, $groups)) {
                            $canAccessDownloads = true;
                            break;
                        }
                    }
                    
                    if (!$canAccessDownloads) {
                    
                        echo '
                            <div class="row clearfix">
                                <div class="header"><h1 class="error">Downloads Manager - Error</h1></div>
                                    <div class="col double-col-2">
                                    <div class="text">
                                        <h3 class="error">You don\'t have permissions to view this page!</h3>
                                    </div>
                                </div>';
                                
                    } else {
                    
                        include ("pages/admin/downloads.php");
                    
                    }
                    
                } else if (isset ($_REQUEST['images'])) {
                
                    $canAccessImages = false;
                    foreach ($manageImagesGroups as $groupId) {
                        if (in_array($groupId, $groups)) {
                            $canAccessImages = true;
                            break;
                        }
                    }
                    
                    if (!$canAccessImages) {
                    
                        echo '
                            <div class="row clearfix">
                                <div class="header"><h1 class="error">Images Manager - Error</h1></div>
                                    <div class="col double-col-2">
                                    <div class="text">
                                        <h3 class="error">You don\'t have permissions to view this page!</h3>
                                    </div>
                                </div>';
                                
                    } else {
                        
                        include ("pages/admin/images.php");
                        
                    }
                    
                } else if (isset ($_REQUEST['videos'])) {
                
                    $canAccessVideos = false;
                    foreach ($manageVideosGroups as $groupId) {
                        if (in_array($groupId, $groups)) {
                            $canAccessVideos = true;
                            break;
                        }
                    }
                    
                    if (!$canAccessVideos) {
                    
                        echo '
                            <div class="row clearfix">
                                <div class="header"><h1 class="error">Videos Manager - Error</h1></div>
                                    <div class="col double-col-2">
                                    <div class="text">
                                        <h3 class="error">You don\'t have permissions to view this page!</h3>
                                    </div>
                                </div>';
                                
                    } else {
                    
                        include ("pages/admin/videos.php");
                        
                    }
                    
                } else {
?>
                    <div class="row clearfix">
                        <div class="header"><h1>Admin Panel</h1></div>
                            <div class="col quad-col-1 centerInners">
                                <div class="btn">
                                    <a href="/admin?blogs">Blogs</a>
                                </div>
                            </div>
                            <div class="col quad-col-1 centerInners">
                                <div class="btn">
                                    <a href="/admin?downloads">Downloads</a>
                                </div>
                            </div>
                            <div class="col quad-col-1 centerInners">
                                <div class="btn">
                                    <a href="/admin?images">Images</a>
                                </div>
                            </div>
                            <div class="col quad-col-1 centerInners">
                                <div class="btn">
                                    <a href="/admin?videos">Videos</a>
                                </div>
                            </div>
                    </div>
<?php
                }
                
            }
            
        }
        
    }
?>