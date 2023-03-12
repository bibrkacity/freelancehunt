function switch_field(field_name)
{
    let title_th=document.getElementById('table_caption');
    let colspan=parseInt(title_th.colSpan);
    let th=document.getElementById(field_name+'00');

    let button1=document.getElementById('th_'+field_name);

    let display;
    if(th.style.display=='none')
    {
        display='';
        title_th.colSpan=(colspan+1);

        if(button1!=null)
        {
            button1.value='-';
            button1.title='Скрыть';
        }
    }
    else
    {
        display='none';
        title_th.colSpan=(colspan-1);

        if(button1!=null)
        {
            button1.value='+';
            button1.title='Показать';
        }

    }

    th.style.display=display;

    let cells=document.getElementsByTagName('td');
    let re=new RegExp(field_name+'[0-9]+','g');
    for (let i=0;i<cells.length;i++)
    {
        if(cells[i].id!=null)
        {
            if(re.test(cells[i].id))
            {
                cells[i].style.display=display;
            }
        }

    }

    let links=document.links;
    re=/page[0-9]+/;
    for (let i=0;i<links.length;i++)
    {
        if(links[i].id!=null)
        {
            if(re.test(links[i].id))
            {
                links[i].href=links[i].href+'&'+field_name+'='+display;
            }
        }
        if(links[i].className!=null)
        {
            if(links[i].className=='sorting')
            {
                links[i].href=links[i].href+'&'+field_name+'='+display;
            }
        }
    }
}

//=============================================================

function open_filters()
{
    let div = document.getElementById('search_popup');

    let className = div.className;

    div.className = (className == 'hidden') ? 'show' : 'hidden' ;

    let span = document.getElementById('tri');

    span.textContent = (className == 'hidden') ? String.fromCharCode(9650) : String.fromCharCode(9658);
}
