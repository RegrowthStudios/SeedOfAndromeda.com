<?php
    $query = $connection->prepare ( "SELECT * FROM downloads WHERE published = ? ORDER BY id DESC" );
    $query->execute( array ( 
        1
    ) );
    
    $downloads = array();
    while ( $row = $query->fetch () ) {
		$downloads[] = $row;
	}
    
    $i = 0;
?>
<div id="latest-download" class="row clearfix" style="background:url('/files/<?php echo $downloads[$i]["backgroundurl"] ?>');background-position:center center;background-repeat:no-repeat;background-size:cover;">
    <div class="header"><h1><?php if ( $downloads[$i]["disp-name"] ) { echo $downloads[$i]["name"] . " - " . $downloads[$i]["version"]; } else { echo $downloads[$i]["version"]; } ?></h1></div>
    <div class="col double-col-2">
        <div class="text">
            <?php echo $downloads[$i]['description']; ?>
        </div>
    </div>
</div>
<div class="row clearfix">
    <div class="divider"></div>
<?php
    $tot = $downloads[$i]["download-number"];
    for ( $j = 1; $j <= $tot; $j++ ) {
?>
    <div class="col <?php if ($tot == 1) { echo "double-col-2"; } elseif ($tot == 2) { echo "double-col-1"; } else { echo "tri-col-1"; } ?>">
        <?php insertButton($downloads[$i]["download-".$j."-text"], "http://files.seedofandromeda.com/game/" . $i . "_" . $j . ".zip" , "download center", "var that=this;_gaq.push(['_trackEvent','Download','ZIP',this.href]);setTimeout(function(){location.href=that.href;},400);return false;"); ?>
    </div>
<?php
    }
?>
</div>
