function resize()
{

    let re = /Android|webOS|iPhone|iPad|iPod|BlackBerry|BB|PlayBook|IEMobile|Windows Phone|Kindle|Silk|Opera Mini/i ;

    let mobile =  true; //re.test(navigator.userAgent) ;

    let wrapper = document.getElementById('wrapper');

    if( mobile )
    {
        wrapper.className = 'mobile';

    }
    else
    {
        wrapper.className = 'desktop';
    }

}

function show_or_hide_menu()
{

    var div = document.getElementById('mainmenu_mobile');
    div.classList.toggle('invisible');

}