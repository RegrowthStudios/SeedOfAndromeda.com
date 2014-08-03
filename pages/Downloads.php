<?php
    $query = $connection->prepare ( "SELECT * FROM downloads ORDER BY id DESC" );
	$query->execute();
    
    $downloads = array();
    while ( $row = $query->fetch () ) {
		$downloads[] = $row;
	}
    
    function cmpVer($a, $b) {
        return version_compare ( $b['version'], $a['version'] );
    }
    usort($downloads, "cmpVer");
    
    $i = 0;
    while ( $downloads[$i]['published'] == 0 ) {
        if($i > ( sizeof($downloads) - 1 )) {
            break;
        }
        $i ++;
    }
?>
<div id="latest-download" class="row clearfix">
    <div class="header"><h1>Latest Version ( V<?php echo $downloads[$i]['version']; ?> )</h1><h3>&nbsp;-&nbsp;<a onclick="var that=this;_gaq.push(['_trackEvent','Download','ZIP',this.href]);setTimeout(function(){location.href=that.href;},400);return false;" href="<?php echo 'http://seedofandromeda.com' . $downloads[$i]['url']; ?>">download</a></h3></div>
    <div class="col double-col-2">
        <div class="text">
                <?php echo $downloads[$i]['description']; ?>
            </div>
    </div>
</div>

<?php
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
        <div class="col tri-col-1">
            <a class="btn download" onclick="var that=this;_gaq.push([\'_trackEvent\',\'Download\',\'ZIP\',this.href]);setTimeout(function(){location.href=that.href;},400);return false;" href="http://seedofandromeda.com' . $downloads[$i]['url'] . '">Download!</a>
        </div>
                ';
            }
        }
?>
</div>
<?php
    }
?>