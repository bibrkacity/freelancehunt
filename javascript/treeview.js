function childs(id)
{

	var div = document.getElementById('childs' + id);
	if(div == null) 
		return;

	var display = div.style.display;
	if (display=='')
		display = 'none';

	var znak;

	if(display == 'none')
		{
		display = 'block';
		znak = '-';

		}
	else
		if(display == 'block')
		{
		display = 'none';
		znak = '+';
		}
	div.style.display = display;
	set_znak(id,znak);

}

function edit(file,id,qs,level)
	{

	var url = file+'?id='+id+qs+'&level='+level;
	location.href=url;

	}

/*===========================*/

function add(table, parent_id_field, name_field, id, criteria_field, criteria_value)
	{

		var url = 'ajax/tv_add.php?table='+table+'&parent_id_field='+parent_id_field+'&name_field='+name_field+'&id='+id;

		if(criteria_field != '')
			url += '&'+criteria_field+'='+criteria_value;

		var iframe = frames[0];
		iframe.location.href=url;
	}


/*===========================*/

function add_link(table, parent_id_field, name_field, id, criteria_field, criteria_value)
	{

		var url = 'ajax/tv_add_link.php?table='+table+'&parent_id_field='+parent_id_field+'&name_field='+name_field+'&id='+id;

		if(criteria_field != '')
			url += '&'+criteria_field+'='+criteria_value;

		var iframe = frames[0];
		iframe.location.href=url;
	}


/*===========================*/

function del(table,id_field, parent_id_field, id)
	{
		if(!confirm('Будут удален узел и все под-узлы. Продолжить?'))
			return;

		var url = 'ajax/tv_del.php?table='+table+'&id_field='+id_field+'&parent_id_field='+parent_id_field+'&id='+id;

		var iframe = frames[0];
		iframe.location.href=url;

	}




/*===========================*/

function	set_znak(id,znak)
	{
	var div = document.getElementById('node' + id);
	if(div == null) 
		return;

	var span = div.firstChild;

	span.firstChild.nodeValue = znak;


	}

/*===========================*/

function all_extract()
	{
	var divs = document.getElementsByTagName('div');

	var re1 = /childs\d+/;
	var re2 = /node\d+/;

	for(var i=0;i<divs.length;i++)
		{
		if(divs[i].id != null)
			{
			if(re1.test(divs[i].id))
				divs[i].style.display='block';
			if(re2.test(divs[i].id))
				{
				if(divs[i].firstChild.nodeType == 1)
					if (divs[i].firstChild.nodeName == 'SPAN')
						divs[i].firstChild.firstChild.nodeValue='-';
				}
			}
		}
	}

/*===========================*/

function all_collapse()
	{
	var divs = document.getElementsByTagName('div');

	var re1 = /childs\d+/;
	var re2 = /node\d+/;

	for(var i=0;i<divs.length;i++)
		{
		if(divs[i].id != null)
			{
			if(re1.test(divs[i].id))
				divs[i].style.display='none';
			if(re2.test(divs[i].id))
				{
				if(divs[i].firstChild.nodeType == 1)
					if (divs[i].firstChild.nodeName == 'SPAN')
						divs[i].firstChild.firstChild.nodeValue='+';
				}
			}
		}
	}