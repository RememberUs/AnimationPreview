<?php
/*
ffmpeg -framerate 25 -i fish-square.png -t 7.00 \
-filter_complex "[0:v]loop=175:1:0,setpts=N/25/TB[tmp];[tmp]crop=h=ih:w='if(gt(a,16/9),ih*16/9,iw)':y=0:x='if(gt(a,16/9),(ow-iw)/2,0)'[tmp];\
[tmp]scale=-1:4000,crop=w=iw:h='min(iw*9/16,ih)':x=0:y='0.50*ih-((t/7.00)*min(0.50*ih,(ih-oh)/6))',trim=duration=7.00[tmp1];\
[tmp1]zoompan=z='if(lte(pzoom,1.0),1.15,max(1.0,pzoom-0.0005))':x='iw/2-(iw/zoom/2)':y='ih/2-(ih/zoom/2)':d=1,setsar=sar=1:1[animated];\
[animated]trim=duration=7.00[final]" -map "[final]" -pix_fmt yuv420p -s 1280x720 -y fish-square-up.mp4
 */

$filename = isset($_GET['filename']) ? trim($_GET['filename']) : "";

if (!trim($filename) || !file_exists('assets/' . $filename))
{
	exit('file not found');
}

$clean_filename = pathinfo($filename)['filename'];

$image_params = getimagesize('assets/' . $filename);
$iw           = $image_params[0];
$ih           = $image_params[1];

$iw = ($iw / $ih > 16 / 9) ? $ih * 16 / 9 : $iw;

//Total video time
$video_time = 7;
$zoom_delta = 0.0005;
$fps        = 25;
$zoom_start = 1.15;
$zoom_end   = $zoom_start - 0.0005 * 25 * 7;

//get zooming Y/W change
$zoom_y0 = ($ih / 2 - $ih / $zoom_start / 2);

$oh = min($iw * 9 / 16, $ih);
$ow = $oh * 16 / 9;


$delta_height = $ih - $oh;
$delta_height = round($delta_height, 2);

$zoom_y0 = ($oh / 2 - $oh / $zoom_start / 2);
$zoom_x0 = ($ow / 2 - $ow / $zoom_start / 2);

//Initial time = 0.01 second
$startYposition = 0.5 * $ih - 0.01 / 7 * min(0.5 * $ih, $delta_height / 6) - $zoom_y0;
$endYposition   = 0.5 * $ih - 7 / 7 * min(0.5 * $ih, $delta_height / 6);

$zoomX_start = $zoom_start;
$zoomX_end   = $zoom_start;

$proportionX = 640 / $iw;
$proposalY   = $ih * $proportionX;

if ($proposalY * 0.5 < 360)
{
	$startYposition = $endYposition;
}

if ($iw / $ih >= 1.6)
{
	$planned_height = $iw * 9 / 16;
	$fact_heigt = $ih;
	$delta_height = abs($fact_heigt - $planned_height);
	$startYposition = $endYposition = $delta_height;
}
if ($ih / $iw == 1)
{
	$startYposition = $endYposition + $zoom_y0;
	$zoomX_start    = 1;
	$zoomX_end      = 1;
}
if ($iw / $ih > 1.33 && $iw / $ih < 1.6)
{
    $startYposition = $startYposition / 2;
    $endYposition = $startYposition;
    $zoomX_start    = 1;
    $zoomX_end      = 1;
}

if ($iw / $ih > 1.33 && $iw / $ih <= 1.5)
{
    $startYposition = $startYposition / 1.5 ;
    $endYposition = $startYposition;
    $zoomX_start    = 1;
    $zoomX_end      = 1;
}


if ($ih / $iw > 2)
{
	$startYposition = $startYposition + $zoom_y0 * 2;
}

$startY = round($startYposition / $ih, 4) * 100;
$endY   = round($endYposition / $ih, 4) * 100;

if ($proposalY * 0.5 < 360 && ($startY == $endY))
{
	if ($ih / $iw == 1)
	{
        // do nothing
	}elseif ($iw / $ih >= 1.6)
	{
		//do nothing
	}
    elseif ($iw / $ih > 1.33 && $iw / $ih < 1.6)
    {

    }
	else
	{
		$startY = $endY = 0;
	}
}

$startX = 0;

$preview_w = "640px";
$ratio     = $iw / $ih;

if (640 / $ratio <= 360)
{
	$preview_h = "360px";
	$preview_w = 640 * $zoom_start;
}
else
{
	$preview_h = "auto";
}

$debug = false;

if ($debug)
{
	$endY     = $startY;
	$zoom_end = $zoom_start;
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Up Effect</title>
    <link rel="stylesheet" href="assets/style.css">
    <style>
        @keyframes animatePreview {
            0% {
                transform: translateY(-<?php echo $startY;?>%) translateX(<?php echo $startX;?>%) scale(<?php echo $zoom_start;?>,<?php echo $zoom_start;?>);
            }
            100% {
                transform: translateY(-<?php echo $endY;?>%) translateX(<?php echo $startX;?>%) scale(<?php echo $zoom_end;?>,<?php echo $zoom_end;?>);
            }
        }

        .container img {
            width: <?php echo $preview_w;?>;
            height: <?php echo $preview_h ;?>;
        }
    </style>
</head>
<body>
<div class="demo">
    <div class="container">
        <img id="preview_image" style="" class="preview_off" src="assets/<?php echo $filename; ?>" alt="source image">
    </div>
    <div class="video">
        <video controls muted loop id="player">
            <source src="assets/<?php echo $clean_filename; ?>-up.mp4" type="video/mp4">
        </video>
    </div>
</div>

<script
        src="https://code.jquery.com/jquery-3.5.1.min.js"
        integrity="sha256-9/aliU8dGd2tb6OSsuzixeV4y/faTqgFtohetphbbj0="
        crossorigin="anonymous"></script>
<script type="text/javascript">
    $(function () {
		<?php if(!$debug){ ?>
        $("#player").get(0).play();
		<?php } ?>
        $("#preview_image").removeClass('preview_off').addClass('preview');
    });
</script>
</body>
</html>
