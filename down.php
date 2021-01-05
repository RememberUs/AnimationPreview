<?php
/*
ffmpeg -framerate 25 -i fish-tall.png -t 7.00 \
-filter_complex "[0:v]loop=175:1:0,setpts=N/25/TB[tmp];[tmp]crop=h=ih:w='if(gt(a,16/9),ih*16/9,iw)':y=0:x='if(gt(a,16/9),(ow-iw)/2,0)'[tmp];\
[tmp]scale=-1:4000,crop=w=iw:h='min(iw*9/16,ih)':x=0:y='max((ih-oh)/6,0.50*ih-((ih-oh)/6))+((t/7.00)*(ih-oh)/6)',trim=duration=7.00[tmp1];\
[tmp1]zoompan=z='min(pzoom+0.0005,1.5)':x='iw/2-(iw/zoom/2)':y='ih/2-(ih/zoom/2)':d=1,setsar=sar=1:1[animated];\
[animated]trim=duration=7.00[final]" -map "[final]" -pix_fmt yuv420p -s 1280x720 -y fish-tall-down.mp4
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
$zoom_start = 1;
$zoom_end   = $zoom_start + 0.0005 * 25 * 7;

$oh = min($iw * 9 / 16, $ih);
$ow = $oh * 16 / 9;

$zoom_yEnd = ($oh / 2 - $oh / $zoom_end / 2);

$delta_height = $ih - $oh;
$delta_height = round($delta_height, 2);

//Initial time = 0.01 second

$startYposition = max(($delta_height / 6), 0.5 * $ih - $delta_height / 6) + 0.01 / 7 * $delta_height / 6;
$endYposition   = max(($delta_height / 6), 0.5 * $ih - $delta_height / 6) + 7 / 7 * $delta_height / 6 - $zoom_yEnd;

$proportionX = 640 / $iw;
$proposalY   = $ih * $proportionX;


if ($iw / $ih > 1.5)
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

if ($iw / $ih > 1.33 && $iw / $ih < 1.6)
{
    $startYposition = $startYposition / 2;
    $endYposition = $startYposition;
    $zoomX_start    = 1;
    $zoomX_end      = 1;
}

if ($iw / $ih > 1.4 && $iw / $ih < 1.55)
{
    $startYposition = $startYposition / 1.5;
    $endYposition = $startYposition;
}
if ($ih / $iw == 1)
{
	// do nothing
}

if ($ih / $iw > 2)
{
	//do nothing
}

$startY = round($startYposition / $ih, 4) * 100;
$endY   = round($endYposition / $ih, 4) * 100;



if ($proposalY * 0.5 < 360 && ($startY == $endY))
{

	if ($ih / $iw == 1)
	{
        //do nothing
	}
	elseif ($iw / $ih >= 1.6)
	{
		//do nothing
	}elseif ($iw / $ih > 1.33 && $iw / $ih < 1.6)
    {

    }
	else
	{
		$startY = $endY = 0;
	}
}

$startX = 0;

$ratio = $iw / $ih;

if (640 / $ratio <= 360)
{
	$preview_h = "360px";
	$preview_w = "auto";
}
else
{
	$preview_h = "auto";
	$preview_w = "640px";
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
    <title>Pan-down Effect</title>
    <link rel="stylesheet" href="assets/style.css">
    <style>
        @keyframes animatePreview {
            0% {
                transform: translateY(-<?php echo $startY;?>%) translateX(-<?php echo $startX;?>%) scale(<?php echo $zoom_start;?>);
            }
            100% {
                transform: translateY(-<?php echo $endY;?>%) translateX(-<?php echo $startX;?>%) scale(<?php echo $zoom_end;?>);
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
            <source src="assets/<?php echo $clean_filename; ?>-down.mp4" type="video/mp4">
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
