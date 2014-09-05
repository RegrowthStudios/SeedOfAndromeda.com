<?php
	require_once ("../db_connect.php");
    
    $VIDEOS_PER_PAGE = 12;
    
    if (! isset ( $connection )) {
        echo "";
    } else {
        if ( isset ( $_REQUEST['check'] )) {
            echo true;
        } else if ( isset ( $_REQUEST['pid'] )) {
            if ( isset ( $_REQUEST['category'] )) {
                $query = $connection->prepare ( "SELECT * FROM videos WHERE published = ? AND category = ? ORDER BY id DESC" ); //LIMIT " . ( $_REQUEST['pid'] * $IMAGES_PER_PAGE )
                $query->execute( array (
                    1,
                    $_REQUEST['category']
                ) );
                $fetched = $query->fetchAll();
                $i = $VIDEOS_PER_PAGE * ( $_REQUEST['pid'] - 1 );
                $end = $i + $VIDEOS_PER_PAGE;
                $videos = array();
                for ($i; $i < $end; $i++) {
                    if ( $i >= sizeof ( $fetched )) {
                        break;
                    }
                    $videos[] = array (
                        "title" => $fetched[$i]["title"],
                        "vid_id" => $fetched[$i]["vid_id"],
                        "thumb_url" => $fetched[$i]["thumb_url"],
                        "category" => $fetched[$i]["category"],
                    );
                }
                echo json_encode ( $videos );
            } else {
                $nextPageToken = '';
                for ($i = 4; $i < $_REQUEST["pid"]; $i+=4) {
                    $nextPageToken = json_decode( file_get_contents("https://www.googleapis.com/youtube/v3/playlistItems?part=id&maxResults=" . ( $VIDEOS_PER_PAGE * 4 ) . "&playlistId=UUMlW2qG20hcFYo06rcit4CQ&key=AIzaSyBb43dOH0L_dnbqKOQ8qpiXAOez7uGXO6o&pageToken=" . $nextPageToken) )->nextPageToken;
                }
                $vids = json_decode( file_get_contents("https://www.googleapis.com/youtube/v3/playlistItems?part=snippet&maxResults=" . ( $VIDEOS_PER_PAGE * 4 ) . "&playlistId=UUMlW2qG20hcFYo06rcit4CQ&key=AIzaSyBb43dOH0L_dnbqKOQ8qpiXAOez7uGXO6o&pageToken=" . $nextPageToken) )->items;
                $videos = array();
                $mult = ( ( $_REQUEST["pid"] % 4 ) - 1 );
                $j = $VIDEOS_PER_PAGE * ( $mult == -1 ? 3 : $mult );
                $end = $j + $VIDEOS_PER_PAGE;
                for ($j; $j < $end; $j++) {
                    if ( $j >= sizeof ( $vids )) {
                        break;
                    }
                    $vid = $vids[$j]->snippet;
                    $videos[] = array (
                        "title" => $vid->title,
                        "vid_id" => $vid->resourceId->videoId,
                        "thumb_url" => ( $vid->thumbnails->maxres ? $vid->thumbnails->maxres->url : ( $vid->thumbnails->standard ? $vid->thumbnails->standard->url : $vid->thumbnails->high->url ) ),
                        "category" => -1
                    );
                }
                echo json_encode ( $videos );
            }
        } else if ( isset ( $_REQUEST['getTotalPages'] )) {
            if ( isset ( $_REQUEST['category'] )) {
                $query = $connection->prepare( "SELECT * FROM videos WHERE published = ?" );
                $query->execute( array ( 
                    1
                ) );
                echo json_encode ( ceil ( $query->rowCount() / $VIDEOS_PER_PAGE ) );
            } else {
                echo json_encode ( ceil ( json_decode( file_get_contents("https://www.googleapis.com/youtube/v3/playlists?part=contentDetails&id=UUMlW2qG20hcFYo06rcit4CQ&key=AIzaSyBb43dOH0L_dnbqKOQ8qpiXAOez7uGXO6o&") )->items[0]->contentDetails->itemCount / $VIDEOS_PER_PAGE ) );
            }
        }
    }
?>