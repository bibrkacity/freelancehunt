let mobile = false;

function resize()
{

    let wrapper = document.getElementById('wrapper');
	mobile = (wrapper.className == 'mobile');
    console.log('mobile='+mobile);
    if( !mobile )
    {
        console.log('point');
        let h = document.documentElement.clientHeight;
        document.getElementById('content_outer').style.height=(h-180)+'px';
        document.getElementById('content').style.minHeight=(h-180)+'px';
    }

}

function show_or_hide_menu()
{

    let div = document.getElementById('mainmenu_mobile');
    div.classList.toggle('invisible');

}