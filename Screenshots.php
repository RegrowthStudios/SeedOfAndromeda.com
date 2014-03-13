
			<?php $page_id = 4; include $_SERVER[DOCUMENT_ROOT].'/header.php'; ?>
            <div class="topimg"></div>
            <div id="content-outer">
                <div class="double-col">
                    <h3 style="text-align:center;font-size:1.6em;">Screenshots</h3>
                    <div class="img-prev"><img src="Assets/images/arrowLeft.png" /></div>
                    <div id="image-frame-inner">
                        <img src="#" class="temp-image" />
                        <img src="#" class="enlarged-image" />
                    </div>
                    <div class="img-next"><img src="Assets/images/arrowRight.png" /></div>
                    <br />
                </div>
                <?php
                    $dirname = "Assets/images/Screenshots/";
                    $images = glob($dirname."*.jpg");
                    
                    $i = 0;
                    foreach($images as $image) {
                        $i++;
                        
                        if($i == 1) {
                            
                            echo
                            '
                            <div class="image-col quad-col-1 empty">
                                <img src="'.$image.'" class="image"/>
                            </div>
                            ';
                            
                        } else if($i == 2) {
                            
                            echo
                            '
                            <div class="image-col quad-col-2 empty">
                                <img src="'.$image.'" class="image"/>
                            </div>
                            ';
                            
                        } else if($i == 3) {
                            
                            echo
                            '
                            <div class="image-col quad-col-3 empty">
                                <img src="'.$image.'" class="image"/>
                            </div>
                            ';
                            
                        } else {
                            
                            echo
                            '
                            <div class="image-col quad-col-4 empty">
                                <img src="'.$image.'" class="image"/>
                            </div>
                            ';
                            
                        }
                        
                        if($i == 4) {
                            $i = 0;
                        }
                    }
                ?>
            </div>
            <div class="bottomimg"></div>
        </div>
    	<?php include $_SERVER[DOCUMENT_ROOT].'/footer.php'; ?>
        
    </body>

</html>
