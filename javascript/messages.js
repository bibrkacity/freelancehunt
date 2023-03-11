function hide_messages(step)
	{
	if(step >= 10)
		document.getElementById('messages_outer').style.display='none';
	else
		{
		document.getElementById('messages_outer').style.opacity=(1-0.1*step);
		setTimeout('hide_messages('+(step+1)+')',200);
		}
	}

/*==========================================*/

function show_messages(need_open)
	{
	if(need_open)
		{
		var div = document.getElementById('messages_outer');
		div.style.display='block';

		div.style.width=document.documentElement.clientWidth+'px';
		div.style.height=document.documentElement.clientHeight+'px';

		}
	}

/*==========================================*/

function setRead(messageID)
	{

	var url = 'ajax/setread.php?id='+messageID;
	var iframe=frames[0];
	iframe.location.href = url;

	}