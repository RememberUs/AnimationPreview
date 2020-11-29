# AnimationPreview

# panup

ffmpeg -framerate 25 -i spongebob.png -t 7.00 \
-filter_complex "[0:v]loop=175:1:0,setpts=N/25/TB[tmp];[tmp]crop=h=ih:w='if(gt(a,16/9),ih*16/9,iw)':y=0:x='if(gt(a,16/9),(ow-iw)/2,0)'[tmp];\
[tmp]scale=-1:4000,crop=w=iw:h='min(iw*9/16,ih)':x=0:y='0.50*ih-((t/7.00)*min(0.50*ih,(ih-oh)/6))',trim=duration=7.00[tmp1];\
[tmp1]zoompan=z='if(lte(pzoom,1.0),1.15,max(1.0,pzoom-0.0005))':x='iw/2-(iw/zoom/2)':y='ih/2-(ih/zoom/2)':d=1,setsar=sar=1:1[animated];\
[animated]trim=duration=7.00[final]" -map "[final]" -pix_fmt yuv420p -s 1280x720 -y spongebob-up.mp4

# pandown

ffmpeg -framerate 25 -i spongebob.png -t 7.00 \
-filter_complex "[0:v]loop=175:1:0,setpts=N/25/TB[tmp];[tmp]crop=h=ih:w='if(gt(a,16/9),ih*16/9,iw)':y=0:x='if(gt(a,16/9),(ow-iw)/2,0)'[tmp];\
[tmp]scale=-1:4000,crop=w=iw:h='min(iw*9/16,ih)':x=0:y='max((ih-oh)/6,0.50*ih-((ih-oh)/6))+((t/7.00)*(ih-oh)/6)',trim=duration=7.00[tmp1];\
[tmp1]zoompan=z='min(pzoom+0.0005,1.5)':x='iw/2-(iw/zoom/2)':y='ih/2-(ih/zoom/2)':d=1,setsar=sar=1:1[animated];\
[animated]trim=duration=7.00[final]" -map "[final]" -pix_fmt yuv420p -s 1280x720 -y spongebob-down.mp4
