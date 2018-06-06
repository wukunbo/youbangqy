function getId(id){
    return document.getElementById(id);
}

function getClass(clas){
    return document.getElementsByClassName(clas);
}

function sett(ele,num,attr){
    switch (attr){
        case 'width':{
            ele.style.width=num+'px';
            break;
        }
        case 'height':{
            ele.style.height=num+'px';
            break;
        }
        case 'lh':{
            ele.style.lineHeight=num+'px';
            break;
        }
        case 'borderRadius':{
            ele.style.borderRadius=num+'px';
            break;
        }
        case 'marginTop':{
            ele.style.marginTop=num+'px';
            break;
        }
        default :{}
    }
}


//文字轮播
function startmarquee(lh,speed,delay) {
    var p=false;
    var t;
    var o=getId("marqueebox");
    o.innerHTML+=o.innerHTML;
    o.style.marginTop=0;
    o.onmouseover=function(){p=true;}
    o.onmouseout=function(){p=false;}

    function start(){
        t=setInterval(scrolling,speed);
        if(!p) o.style.marginTop=parseInt(o.style.marginTop)-1+"px";
    }

    function scrolling(){
        if(parseInt(o.style.marginTop)%lh!=0){
            o.style.marginTop=parseInt(o.style.marginTop)-1+"px";
            if(Math.abs(parseInt(o.style.marginTop))>=o.scrollHeight/2) o.style.marginTop=0;
        }else{
            clearInterval(t);
            setTimeout(start,delay);
        }
    }
    setTimeout(start,delay);
}
