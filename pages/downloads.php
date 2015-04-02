<?php
    $query = $connection->prepare ( "SELECT * FROM downloads WHERE published = ? ORDER BY id DESC" );
    $query->execute( array ( 
        1
    ) );
    
    $downloads = array();
    while ( $row = $query->fetch () ) {
		$downloads[] = $row;
	}
    
    function cmpVer($a, $b) {
        return version_compare ( $b['version'], $a['version'] );
    }
    usort($downloads, "cmpVer");
    
    $i = 0;
?>
<div id="latest-download" class="row clearfix" style="background:url('/files/<?php echo $downloads[$i]["backgroundurl"] ?>');background-position:center center;background-repeat:no-repeat;background-size:cover;">
    <div class="header"><h1>Latest Version ( V<?php echo $downloads[$i]['version']; ?> )</h1></div>
    <div class="col tri-col-2">
        <div class="text">
            <?php echo $downloads[$i]['description']; ?>
        </div>
    </div>
    <div class="col tri-col-1">
        <?php insertButton("Download", "http://files.seedofandromeda.com/" . $downloads[$i]['url'], "download right", "var that=this;_gaq.push(['_trackEvent','Download','ZIP',this.href]);setTimeout(function(){location.href=that.href;},400);return false;"); ?>
    </div>
</div>

<?php
    if ( isset ( $_REQUEST ['show'] ) && $_REQUEST ['show'] == "archived" ) {
        $i ++;
        if ( $i < sizeof($downloads) ) {
?>
<div id="old-downloads" class="row clearfix">
    <div class="header"><h1>Older Versions</h1></div>
<?php
            for($i; $i < sizeof($downloads); $i++) {
                if ( $downloads[$i]['published'] == 1 ) {
                    echo '
            <div class="col tri-col-2">
                <div class="text ver-description">
                    <h4 class="version">V' . $downloads[$i]['version'] . '</h4>&nbsp;- ' . substr( $downloads[$i]['description'], 3, -4 ) . '
                </div>
            </div>
            <div class="col tri-col-1">';
                insertButton("Download", "http://files.seedofandromeda.com/" . $downloads[$i]['url'], "download right", "var that=this;_gaq.push([\'_trackEvent\',\'Download\',\'ZIP\',this.href]);setTimeout(function(){location.href=that.href;},400);return false;");
                echo '
            </div>';
                }
            }
?>
</div>
<?php
        }
    } else {
?>
<div id="old-downloads" class="row clearfix">
    <div class="header"><h1>Older Versions</h1></div>
    <div class="col double-col-2">
        <?php
            insertButton("Show Old Downloads", "/downloads?show=archived", "download center");
        ?>
    </div>
</div>
<?php
    }
?>