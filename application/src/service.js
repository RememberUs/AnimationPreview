function down(iw, ih){
    if (iw / ih > 16/9) {
        iw = ih * 16/9;
    }
    let video_time = 7;
    let zoom_delta = 0.0005;
    let fps        = 25;
    let zoom_start = 1;
    let zoom_end   = zoom_start + zoom_delta * fps * video_time;
    let oh = ih;
    if(iw * 9/16 < ih){
        oh = iw * 9/16
    }
    //let ow = oh * 16/9;

    let zoom_yEnd = (oh / 2 - oh/zoom_end/2);

    let delta_height = ih - oh;
    let startYposition = Math.max((delta_height / 6), 0.5 * ih - delta_height / 6) + 0.01 / video_time * delta_height / 6;
    let endYposition   = Math.max((delta_height / 6), 0.5 * ih - delta_height / 6) + video_time / video_time * delta_height / 6 - zoom_yEnd;

    let proportionX = 640 / iw;
    let proposalY   = ih * proportionX;

    if (iw / ih > 1.5) {
        startYposition = endYposition;
    }

    if (iw / ih >= 1.6) {
        let planned_height = iw * 9 / 16;
        let delta_height = Math.abs(ih - planned_height);
        startYposition = endYposition = delta_height;
    }

    if (iw / ih < 1.6 && iw / ih >1.33) {
        startYposition = startYposition / 2;
        endYposition = startYposition;
        // zoom_start = zoom_end = 1;
    }
    if (iw / ih <= 1.55 && iw / ih >1.4) {
        startYposition = startYposition / 1.5;
        endYposition = startYposition;
    }

    let startY = (startYposition / ih) * 100;
    let endY   = (endYposition / ih) * 100;

    if (proposalY * 0.5 < 360 && (startY === endY)) {
        if (ih/iw === 1 || iw / ih >= 1.6 ||  (iw / ih > 1.33 && iw/ih < 1.6) || (iw / ih >= 1.33 && iw / ih < 1.6) )
        {
            //do nothing
        } else {
            startY = endY = 0;
        }
    }
    let startX = 0;
    let ratio = iw / ih;

    let preview_h = "auto";
    let preview_w = "25vw";
    let internal_w = "25vw";

    if (640 / ratio <= 360)
    {
        // preview_h = "360px";
        //preview_h = "auto";
        internal_w = 25 * 1.15 + "vw";
    } else {
        preview_h = (25 / (16/9) ) + "vw";
    }
    let result = [];
    result['startX'] = startX;
    result['startY'] = startY;
    result['zoom_start'] = zoom_start;
    result['zoom_end'] = zoom_end;
    result['endY'] = endY;
    result['preview_w'] = preview_w;
    result['preview_h'] = preview_h;
    result['internal_w'] = internal_w;


    return result;
}

function up(iw, ih){
    let video_time = 7;
    let zoom_delta = 0.0005;
    let fps        = 25;
    let zoom_start = 1.15;
    let zoom_end   = zoom_start - zoom_delta * fps * video_time;
    let oh = ih;
    if(iw * 9/16 < ih){
        oh = iw * 9/16
    }
    let zoom_y0 = (ih / 2 - ih / zoom_start / 2);

    oh = Math.min(iw * 9 / 16, ih);
    let ow = oh * 16 / 9;
    let delta_height = ih - oh;

    zoom_y0 = (oh / 2 - oh / zoom_start / 2);
    let zoom_x0 = (ow / 2 - ow / zoom_start / 2);

    let startYposition = 0.5 * ih - 0.01 / 7 * Math.min(0.5 * ih, delta_height / 6) - zoom_y0;
    let endYposition   = 0.5 * ih - 7 / 7 * Math.min(0.5 * ih, delta_height / 6);

    let zoomX_start = zoom_start;
    let zoomX_end   = zoom_start;

    let proportionX = 640 / iw;
    let proposalY   = ih * proportionX;

    if (proposalY * 0.5 < 360)
    {
        startYposition = endYposition;
    }
    if (iw / ih >= 1.6)
    {
        let planned_height = iw * 9 / 16;
        delta_height = Math.abs(ih - planned_height);
        startYposition = endYposition = delta_height;
    }
    if (ih / iw === 1)
    {
        startYposition = endYposition + zoom_y0;
        zoomX_start    = 1;
        zoomX_end      = 1;
    }
    if (iw / ih < 1.6 && iw / ih >1.33) {
        startYposition = startYposition / 2;
        endYposition = startYposition;
    }
    if (iw / ih <= 1.55 && iw / ih >1.4) {
        startYposition = startYposition / 1.5;
        endYposition = startYposition;
    }


    if (ih / iw > 2)
    {
        startYposition = startYposition + zoom_y0 * 2;
    }

    let startY = (startYposition / ih) * 100;
    let endY   = (endYposition / ih) * 100;

    if (proposalY * 0.5 < 360 && (startY === endY))
    {
        if (ih / iw === 1 || (iw / ih >= 1.6 && iw / ih <= 1.9) || (iw / ih >= 1.33 && iw / ih < 1.6))
        {
            // do nothing
        } else {
            startY = endY = 0;
        }
    }

    let startX = 0;

    let preview_h = "auto";
    let preview_w = "25vw";
    let internal_w = "25vw";

    let ratio = iw/ih;
    if (ratio > 2 )
    {
        internal_w = 25 * 1.15 + "vw";
        preview_h = (25 / (16/9) ) + "vw";
    } else {
        preview_h = (25 / (16/9) ) + "vw";
    }

    let result = [];
    result['startX'] = startX;
    result['startY'] = startY;
    result['zoom_start'] = zoom_start;
    result['zoom_end'] = zoom_end;
    result['endY'] = endY;
    result['preview_w'] = preview_w;
    result['preview_h'] = preview_h;
    result['internal_w'] = internal_w;

    return result;
}

const Calculate = function(type, iw, ih){
    if(type === 'up'){
        return up(iw, ih);
    }
    return down(iw, ih);
}

export default Calculate;
