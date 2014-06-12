<?php
$debug = false;
$ary = parse_ini_file("BlockData.ini", true);

		echo '<table><tr><th>Block</th><th>Top</th><th>Side</th><th>Bottom</th></tr>';
		$i = 0;
foreach($ary as $block=>$data){
	if(isset($data['texture']) || isset($data['textureTop']) ||isset($data['textureSide']) ||isset($data['textureBottom'])){
		$texture_top = isset($data['textureTop']) ? $data['textureTop'] : $data['texture'];
		$texture_side = isset($data['textureSide']) ? $data['textureSide'] : $data['texture'];
		$texture_bottom = isset($data['textureBottom']) ? $data['textureBottom'] : $data['texture'];
		$useMapColorTop = isset($data['useMapColorTop']) &&  $data['useMapColorTop'] == 1 ? true : false;
		$name = md5($block);
		cubize($name, $texture_top, $texture_side, $texture_bottom, $useMapColorTop, $debug);
		echo '<tr><td>'.$block.'<br><img src="cubes/'.$name.'.png"/></td><td>Top: '.$texture_top.'<br><img src="'.$texture_top.'"/></td><td>'.$texture_side.'<br><img src="'.$texture_side.'"/></td><td>'.$texture_bottom.'<br><img src="'.$texture_bottom.'"/></td></tr>';
		$i++;
		if($i > 10){
			die();
		}	
	}
}
echo "</table>";


/* 
You could just use the first part of the code if you did not want the imagemap effect. 
*/ 
function cubize($name, $texture_top, $texture_side, $texture_bottom, $useMapColorTop = false, $debug = false){

	runcmd('convert'
	.'( '.$texture_top.' -alpha set -virtual-pixel transparent '
	.($useMapColorTop ? '-size 1x1 xc:Green -fx "u*v.p{0,0}" ' : ' ')
		.'+distort Affine "0,256 0,0   0,0 -87,-50  256,256 87,-50" ) '
	.'( '.$texture_side.' -alpha set -virtual-pixel transparent -fill black -colorize 25% '
		.'+distort Affine "256,0 0,0   0,0 -87,-50  256,256 0,100" ) '
	.'( '.$texture_side.' -alpha set -virtual-pixel transparent -fill black -colorize 50% '
		.'+distort Affine "  0,0 0,0   0,256 0,100    256,0 87,-50" ) '
	.'-background none -compose plus -layers merge +repage -compose over cubes/'.$name.'.png',$debug);

	
	/*runcmd("convert \\
     \\( $texture_top -alpha set -virtual-pixel transparent \\
        +distort Affine '0,512 0,0   0,0 -87,-50  512,512 87,-50' \\) \\
     \\( $texture_side -alpha set -virtual-pixel transparent \\
        +distort Affine '512,0 0,0   0,0 -87,-50  512,512 0,100' \\) \\
     \\( $texture_side -alpha set -virtual-pixel transparent \\
        +distort Affine '  0,0 0,0   0,320 0,100    320,0 87,-50' \\) \\
     \\
     -background none -compose plus -layers merge +repage \\
     -bordercolor black -compose over -border 5x2     isometric_cube.png",$debug);*/

  # Create some square images for the cube
  //system('convert '.$texture_top.' -resize 256x256 top.png');
    //runcmd('convert '.$texture_top.' -resize 256x256^ -gravity center -extent 256x256 top.png',$debug);
    //runcmd('convert '.$texture_side.' -resize 256x256 side.png',$debug);
  //system('convert '.$texture_side.'       -resize 256x256 right.jpg');

  # top image shear.
  //system('convert top.png -resize  256x256! -alpha set -background none -shear 0x30 -rotate -60 -gravity center  top_shear.png');
    //runcmd('convert top.png -resize  260x301! -alpha set -background none -shear 0x30 top_shear0.png',$debug);
    //runcmd('convert top_shear0.png -alpha set -background none -rotate -60 -gravity center -crop 520x301+0+0 top_shear3.png',$debug);
  # left image shear
  //system('convert side.png  -resize  256x256! -alpha set -background none -shear 0x30  left_shear.png');
	//runcmd('convert side.png  -resize  260x301! -alpha set -background none -shear 0x30  left_shear.png',$debug);
  # right image shear
  //system('convert side.png  -resize  256x256! -alpha set -background none -shear 0x-30  right_shear.png');
	//runcmd('convert side.png  -resize  260x301! -alpha set -background none -shear 0x-30  right_shear.png',$debug);

  # combine them.
  //system('convert left_shear.png right_shear.png +append ( top_shear.png -repage +33-220 ) -background none -layers merge +repage cubes/'.$name.'.png');
    //runcmd('convert left_shear.png right_shear.png +append \\( top_shear.png -repage +0-149 \\) -background none -layers merge +repage -resize 30%  isometric_shears7.png 2>&1',$debug);

  # cleanup
  //rm -f top.jpg left.jpg right.jpg
  //rm -f top_shear.png left_shear.png right_shear.png
  

}

function runcmd($cmd, $debug = false){
	exec($cmd, $out, $rcode);
  if($debug){
  echo $cmd."\n<br>";
  echo "Return code is $rcode <br>"; //Print the return code: 0 if OK, nonzero if error.
	var_dump($out); //Print the output
  }
}
 ?>