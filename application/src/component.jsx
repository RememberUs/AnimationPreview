import {default as UUID} from "node-uuid";
import ReactPlayer from 'react-player'
import Calculate from './service';

function Element(props) {
    let uid = UUID.v1();
    let filename = props.filename;
    let video_name = props.videoname;

    let iw = props.iw;
    let ih = props.ih;

    let coordinates = Calculate(props.direction, iw, ih);

    let animationName = `animatePreview_`+uid;
    let keyframes =
        `@keyframes ${animationName} {
        0% { transform: translateY(-${coordinates['startY']}%) translateX(-${coordinates['startX']}%) scale(${coordinates['zoom_start']}) }
        100% { transform: translateY(-${coordinates['endY']}%) translateX(-${coordinates['startX']}%) scale(${coordinates['zoom_end']}); }
    }`;

    let preview_img_name = "preview-"+uid;
    let preview_container = "container-"+uid;
    let img = `.${preview_img_name} {
        animation: ${animationName} 7s linear;
        animation-iteration-count: infinite;
        width: ${coordinates['internal_w']}
      }`;

    let container=`.${preview_container}{
        width: ${coordinates['preview_w']};
        height: ${coordinates['preview_h']};
        overflow: hidden;
        margin-bottom: 1rem;
        margin-right: 1rem;
    }`;

    let styleSheet = document.styleSheets[0];
    styleSheet.insertRule(keyframes, styleSheet.cssRules.length);
    styleSheet.insertRule(img, styleSheet.cssRules.length);
    styleSheet.insertRule(container, styleSheet.cssRules.length);

    return (
        <div className="element_container">
            <h2>{props.title}</h2>
            <div className="demo">
                <div className={preview_container}>
                    <img id="preview_image" alt="preview" className={preview_img_name} src={filename}/>
                </div>
                <div className="video">
                    <ReactPlayer width={"25vw"} height={"auto"} controls playing={true} muted loop url={video_name} />
                </div>
            </div>
        </div>
    );
}

export default Element;
