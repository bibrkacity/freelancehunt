var tags=new Array('<b>','</b>','<i>','</i>','<div class=\"citate\">','</div>','<span style=\"color:blue;background-color:yellow;font-weight:bold\">','</span>','<span style=\"color:white;background-color:red;font-weight:bold\">','</span>','<u>','</u>','\n<p style=\"text-indent:2em\">','</p>\n','\n<p style=\"text-indent:0px\">','</p>\n','<a href=\"\">','</a>','&laquo;','&raquo;','\n<p class=\"annotation\" style=\"text-indent:2em\">','</p>\n','\n<p class=\"annotation\" style=\"text-indent:0px\">','</p>\n','<span style=\"font-family:qwerty\">','</span>','<span style=\"font-size:qwerty\">','</span>','<span style=\"color:qwerty\">','</span>','<p>','</p>','<h1>','</h1>','<h2>','</h2>','<strong>','</strong>');

function tag(n,textarea)
	{
	if ((navigator.appName=='Microsoft Internet Explorer') || (navigator.appName=='Opera') )
		{
		tagIE(n,textarea);
		}
	if (navigator.appName=='Netscape')
		{
		tagN(n,textarea);
		}
	}

//---------------------------------- 

function tagIE(n,textarea)
	{
	var openTag=tags[n];
	var closeTag=tags[n+1];
	textarea.focus();
	var theSelection = document.selection.createRange();
	var re=/ $/;
	if (theSelection.text!='')
		{
		var lastSpace='';
		var text=new String(theSelection.text);
		if(re.test(text))
			{
			lastSpace=' ';
			text=text.substr(0,(text.length-1));
			}
		caretPos = textarea.caretPos;
		document.selection.createRange().text=openTag+text+closeTag+lastSpace;
		if (caretPos!=null)
			{
			caretPos.select();
			}
		}

	}

//---------------------------------- 

function tagN(n,textarea)
	{
	var scroll=textarea.scrollTop;
	var openTag=tags[n];
	var closeTag=tags[n+1];
	if (textarea.selectionEnd && (textarea.selectionEnd - textarea.selectionStart > 0))
		{
  		mozWrap(textarea, openTag, closeTag);
		textarea.scrollTop=scroll;
		}

	}

//=============================================================

function mozWrap(txtarea, open, close)
{ 
	
var selLength = txtarea.textLength;
var selStart = txtarea.selectionStart;
var selEnd = txtarea.selectionEnd;
if (selEnd == 1 || selEnd == 2)
	{
	selEnd = selLength;
	}


 var s1 = (txtarea.value).substring(0,selStart);
 var s2 = (txtarea.value).substring(selStart, selEnd);

		var re=/ $/;
 		var lastSpace='';
		var text=new String(s2);
		if(re.test(text))
			{
			lastSpace=' ';
			text=text.substr(0,(text.length-1));
			}
		s2=text;


 var s3 = (txtarea.value).substring(selEnd, selLength);
 txtarea.value = s1 + open + s2 + close +lastSpace+s3;
 return;
}
//=========================

function set_font_family(textarea,font_family)
	{
	if(font_family=='-')
		{
		return;
		}
	tag(24,textarea);
	var scroll=textarea.scrollTop;
	var s=new String(textarea.value);
	textarea.value=s.replace(/qwerty/,font_family)
	textarea.scrollTop=scroll;
	}
//=========================	
function set_font_size(textarea,font_size)
	{
	if(font_size=='-')
		{
		return;
		}
	tag(26,textarea);
	var scroll=textarea.scrollTop;
	var s=new String(textarea.value);
	textarea.value=s.replace(/qwerty/,font_size)
	textarea.scrollTop=scroll;
	}
//=========================	
function set_font_color(textarea,font_color,paletteID)
	{
	if(font_color=='-')
		{
		return;
		}
	tag(28,textarea);
	var scroll=textarea.scrollTop;
	var s=new String(textarea.value);
	textarea.value=s.replace(/qwerty/,font_color)
	textarea.scrollTop=scroll;
	document.getElementById(paletteID).style.display='none';
	}

//=========================	

function auto_p(textarea)
	{
	var s=new String(textarea.value);
	
	if(s.substr(0,2)!='<p')
		{
		s='<p>'+s;
		}

	var pos=3;
	var i=2;

	do
		{
		pos++;
		var token=s.charAt(pos);
		if(token=='\n')
			{
			i++;

			if(i%2==0)//open
				{
				if(s.substr(pos,2)!='<p')
					{
					var s=s.substr(0,pos+1)+'<p>'+s.substr(pos+1);
					pos+=2;
					}
				}
			else//close
				{
				if(s.substr(pos-4,4)!='</p>')
					{
					var s=s.substr(0,pos)+'</p>\n'+s.substr(pos+1);
					pos+=3;
					}
				}
			}
		}
	while (pos<s.length);

	s=s.replace(/<p><\/p>/g,'\n');

	var re=/\s?$/;

	if(re.test(s))
		{
		s+='</p>';
		}


	textarea.value=s;
	}
