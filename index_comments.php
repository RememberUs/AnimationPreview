<?php
$filename = isset($_GET['filename']) ? trim($_GET['filename']) : "";
$direction = isset($_GET['direction']) ? trim($_GET['direction']) : "down";
if($direction != 'down' && $direction != 'up'){
    $direction = 'down';
}

if(trim($filename) && file_exists('assets/'.$filename)){
	$clean_filename = pathinfo($filename)['filename'];

	// Let's generate the video file
	if(!file_exists('assets/'.$clean_filename . "-" . $direction . ".mp4")){

	    if($direction == 'down')
	    {
		    $cmd =
			    <<<EOH
 /usr/local/bin/ffmpeg -framerate 25 -i %s -t 7.00 \
 -filter_complex "[0:v]loop=175:1:0,setpts=N/25/TB[tmp];[tmp]crop=h=ih:w='if(gt(a,16/9),ih*16/9,iw)':y=0:x='if(gt(a,16/9),(ow-iw)/2,0)'[tmp];\
 [tmp]scale=-1:4000,crop=w=iw:h='min(iw*9/16,ih)':x=0:y='0.50*ih-((t/7.00)*min(0.50*ih,(ih-oh)/6))',trim=duration=7.00[tmp1];\
 [tmp1]zoompan=z='if(lte(pzoom,1.0),1.15,max(1.0,pzoom-0.0005))':x='iw/2-(iw/zoom/2)':y='ih/2-(ih/zoom/2)':d=1,setsar=sar=1:1[animated];\
 [animated]trim=duration=7.00[final]" -map "[final]" -pix_fmt yuv420p -s 1280x720 -y %s
EOH;
	    } else {
		    $cmd =
			    <<<EOH
 /usr/local/bin/ffmpeg -framerate 25 -i %s -t 7.00 -filter_complex "[0:v]loop=175:1:0,setpts=N/25/TB[tmp];\
 [tmp]crop=h=ih:w='if(gt(a,16/9),ih*16/9,iw)':y=0:x='if(gt(a,16/9),(ow-iw)/2,0)'[tmp];\
 [tmp]scale=-1:4000,crop=w=iw:h='min(iw*9/16,ih)':x=0:y='0.50*ih-((t/7.00)*min(0.50*ih,(ih-oh)/6))',trim=duration=7.00[tmp1];\
 [tmp1]zoompan=z='if(lte(pzoom,1.0),1.15,max(1.0,pzoom-0.0005))':x='iw/2-(iw/zoom/2)':y='ih/2-(ih/zoom/2)':d=1,setsar=sar=1:1[animated];\
 [animated]trim=duration=7.00[final]" -map "[final]" -pix_fmt yuv420p -s 1280x720 -y %s
EOH;
        }
	    $convert = sprintf($cmd, 'assets/'.$filename, 'assets/'.$clean_filename . "-" . $direction . ".mp4");
	    //exec($convert);
    }


	//Total video time
	$video_time = 7;

	/**
	 * Designed zoom transition could be calculated by this formula:
	 * 1) fps = 25, zoom_delta = 0.00005
	 * 2) zooming start with 1, and ends with 1.5
	 * 3) (max_zoom - start_zoom) / zoom_delta / fps = 1000 phrames / 25fps = 40 seconds
	 * 4) we're trimming the zoom with 7 seconds
	 * 5) hence, zooming will be stopped on 7 second with zoom_level = 7/40 * (1.5-1) = 0.0875
	 * 6) scaling value = 1 + 0.0875 = 1.0875.
	*/

	$zoom_delta = 0.0005;
	$fps = 25;

	if($direction == 'up'){
	    //[tmp1]zoompan=z='if(lte(pzoom,1.0),1.15,max(1.0,pzoom-0.0005))':x='iw/2-(iw/zoom/2)':y='ih/2-(ih/zoom/2)':d=1,setsar=sar=1:1[animated];\
		//zoom_start = 1.15;
		$zoom_start = 1.2;


		$css_scalling_value = $zoom_start - 0.0005 * 25 * 7;

		//$designed_zoom_time = abs($zoom_end - $zoom_start) / $zoom_delta / $fps;
//		$zoom_delta = $video_time / $designed_zoom_time * ($zoom_start - $zoom_end);
//		$css_scalling_value = $zoom_start - $zoom_delta;
    } else {
		$zoom_start = 1;
		$zoom_end = 1.5;


		$designed_zoom_time = ($zoom_end - $zoom_start) / $zoom_delta / $fps;
		$zoom_delta = $video_time / $designed_zoom_time * ($zoom_end - $zoom_start);
		$css_scalling_value = $zoom_start + $zoom_delta;
    }

	/**
	 * Now lets calculate the start/end crop points.
	 * First, we need to know the input image width/height params
	 */
	$image_params = getimagesize('assets/'.$filename);
	$iw = $image_params[0];
	$ih = $image_params[1];

	/**
	 * Now we have to calculate the designed output height and width.
	 * H = min(iw*9/16,ih)
	 * In our case it takes the less height value. For tall images the heigh will be calculated based on the width,
	 * so wide images it will take the input_height instead.
	 */

	/**
	 * Let's calculate the first Y position of the cropped frame
	 * x=0:y='max( (ih-oh)/6, 0.50*ih-((ih-oh)/6) ) + ( (t/7.00)*(ih-oh)/6)'
	 *
	 * The "t" parameter indicates time, so it's chaning from 1 to 7.
	 * "ih-oh" - that's the delta in height between cropped frame and orignal frame.
	 * Let's take the demo tall image, let's say 516x1076px
	 * ih = 1076,
	 * oh (output height) = min(516 * 9/16 || 1076) = 290;
	 * That's why ih-oh = 784px, that's the area heigth which will be "removed" from the input frame in order to crop 9/16 frame.
	 *
	 * Further params indicates that the start position will be changed a bit, and depending on the time parameter, it will
	 * crop another Y position.
	 */

	/**
	 *  Designed height = (min(516 * 9 / 16) || 1076) = 290;

		delta_H = 1080 - designed_h = 790
		Translate Y0 = 0.50*ih - delta_H/6 + delta_H * t/7 = 1076/2 - 790/6 + 790/6 * 1/7 = 538 - 132 + 19 = 425 = 39%
		Translate Y7 = 538 - 132 + 132 = 395 = 50%

		(ih-oh)/6, 0.50*ih-((ih-oh)/6) ) + ( (t/7.00)*(ih-oh)/6)
	 */

	$translateYdirection = 'minus';

	$scalled_heigh = $zoom_start * $ih;
	$zoom_height_delta = $scalled_heigh - $ih;

	$zoom_width_detla = $zoom_start * $iw - $iw;
	$delta_x = $zoom_width_detla / 2;
	$startX = (-1) * $delta_x / ($zoom_start * $iw) * 100;

// Now we have to use a geometry formula for a triangle: c^2 = a^2 + b^2
	$delta_zoom_effect = sqrt( pow($zoom_height_delta, 2)/2 ) / 2;

	$designed_crop_height = min($iw * 9/16 , $ih);

	$delta_height = $ih - $designed_crop_height;

    if($delta_height == 0){
        //no needs to crop
	    $translateOff = false;
	    $translateY0 = 0;
	    $translateY7 = 0;
	    $translateYdirection = 'plus';
    } else
    {
	    $translateOff = false;
	    if ($direction == 'down')
	    {
		    $translateY0 = 0.5 * $ih - $delta_height / 6 + $delta_height / 6 * 1 / 7;
		    $translateY7 = 0.5 * $ih;
	    }
	    else
	    {
		    //0.50*ih-((t/7.00)*min(0.50*ih,(ih-oh)/6))
		    $translateY0 = 0.5 * $ih - 0.01 / 7 * $delta_height / 6;

		    //Crop will be ignored here
		    if (round($translateY0 * 2 / $ih, 2) * 100 > 95)
		    {
			    $delta_2      = ($ih - $translateY0 * 2) / $ih * 100;
			    $translateOff = true;
		    }
		    else
		    {
			    $translateY7 = 0.5 * $ih - $delta_height / 6;
		    }
	    }
    }

    //Apply the zooming delta

	$translateY0 = round(abs($translateY0 - $delta_zoom_effect),2);


    if($translateOff){
	    $endY = $startY = round ($delta_height /$ih * 100, 2);
    } else
    {
	    $startY = round($translateY0 / $ih * 100, 2);
	    $endY   = round($translateY7 / $ih * 100, 2);

	    if ($startY == $endY)
	    {
		    $startY = 0;
		    $endY   = 0;
	    }
    }

} else {
	echo "File not found";
}

if($translateYdirection == 'minus'){
	$startY =(-1) * $startY;
	$endY =(-1) * $endY;
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<title>Forest Tall</title>
	<link rel="stylesheet" href="assets/style.css">
	<style>
        @keyframes animatePreview {
            0% {
                transform: translateY(<?php echo $startY;?>%) translateX(<?php echo $startX;?>%) scale(<?php echo $zoom_start;?>);
            }
            100% {
                transform: translateY(<?php echo $endY;?>%) translateX(<?php echo $startX;?>%) scale(<?php echo $css_scalling_value;?>);
            }
        }
	</style>
</head>
<body>
<div class="demo">
	<div class="container">
		<img id="preview_image" style="" class="preview_off" src="assets/<?php echo $filename;?>" alt="source image">
	</div>
	<div class="video">
		<video controls muted loop id="player">
			<source src="assets/<?php echo $clean_filename. "-" . $direction;?>.mp4" type="video/mp4">
            <!--<source src="assets/1.mp4" type="video/mp4">-->
		</video>
	</div>
</div>

<script
        src="https://code.jquery.com/jquery-3.5.1.min.js"
        integrity="sha256-9/aliU8dGd2tb6OSsuzixeV4y/faTqgFtohetphbbj0="
        crossorigin="anonymous"></script>
<script type="text/javascript">
    $(function(){
        $("#player").get(0).play();
        $("#preview_image").removeClass('preview_off').addClass('preview');
    });
</script>
</body>
</html>
