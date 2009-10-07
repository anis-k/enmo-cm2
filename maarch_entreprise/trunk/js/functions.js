<!--
// Adding prototype and other js scripts
document.write('<script type="text/javascript" src="'+app_path+'js/prototype.js"></script>');
document.write('<script type="text/javascript" src="'+app_path+'js/scriptaculous.js?load=effects,slider"></script>');
document.write('<script type="text/javascript" src="'+app_path+'js/maarch.js"></script>');
document.write('<script type="text/javascript" src="'+app_path+'js/scrollbox.js"></script>');
document.write('<script type="text/javascript" src="'+app_path+'js/effects.js"></script>');
document.write('<script type="text/javascript" src="'+app_path+'js/controls.js"></script>');
document.write('<script type="text/javascript" src="'+app_path+'js/concertina.js"></script>');
document.write('<script type="text/javascript" src="'+app_path+'js/protohuds.js"></script>');
document.write('<script type="text/javascript" src="'+app_path+'js/tabricator.js"></script>');

document.write('<script type="text/javascript" src="'+app_path+'js/indexing.js"></script>');

var isAlreadyClick = false;

function repost(php_file,update_divs,fields,action,timeout)
	{
		//alert('php file : '+php_file);
		var event_count = 0;

		//Observe fields
		for (var i = 0; i < fields.length; ++i) {

			$(fields[i]).observe(action,send);
		}

		function send(event)
		{
			params = '';
			event_count++;

			for (var i = 0; i < fields.length; ++i)
			{
				params += $(fields[i]).serialize()+'&';
			}

			setTimeout(function() {
				event_count--;

				if(event_count == 0)
					new Ajax.Request(php_file,
					  {
						method:'post',
					    onSuccess: function(transport){

						var response = transport.responseText;
						var reponse_div = new Element("div");
						reponse_div.innerHTML = response;
						var replace_div = reponse_div.select('div');

						for (var i = 0; i < replace_div.length; ++i)
							for(var j = 0; j < update_divs.length; ++j)
							{
								if(replace_div[i].id == update_divs[j])
									$(update_divs[j]).replace(replace_div[i]);
							}
						},
					    onFailure: function(){ alert('Something went wrong...'); },
						parameters: params
					  });
			}, timeout);
		}
	}


	/**
	* List used for autocompletion
	*
	*/
	var initList = function (idField, idList, theUrlToListScript, paramNameSrv, minCharsSrv)
	{
	    new Ajax.Autocompleter(
	        idField,
	        idList,
	        theUrlToListScript,
	        {
	            paramName: paramNameSrv,
	            minChars: minCharsSrv
	        });
	};


/*********** Init vars for the calendar ****************/
	var allMonth=[31, 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31];
	var allNameOfWeekDays=["Lu","Ma", "Me", "Je", "Ve", "Sa", "Di"];
	var allNameOfMonths=["Janvier","Fevrier","Mars","Avril","Mai","Juin","Juillet","Aout","Septembre","Octobre","Novembre","Decembre"];
	var newDate=new Date();
	var yearZero=newDate.getFullYear();
	var monthZero=newDate.getMonth();
	var day=newDate.getDate();
	var currentDay=0, currentDayZero=0;
	var month=monthZero, year=yearZero;
	var yearMin=1950, yearMax=2050;
	var target='';
	var hoverEle=false;
/***************************************

/***********Functions used by the calendar ****************/
	function setTarget(e){
		if(e) return e.target;
		if(event) return event.srcElement;
	}
	function newElement(type, attrs, content, toNode) {
		var ele=document.createElement(type);
		if(attrs) {
			for(var i=0; i<attrs.length; i++) {
				eval('ele.'+attrs[i][0]+(attrs[i][2] ? '=\u0027' :'=')+attrs[i][1]+(attrs[i][2] ? '\u0027' :''));
			}
		}
		if(content) ele.appendChild(document.createTextNode(content));
		if(toNode) toNode.appendChild(ele);
		return ele;
	}
	function setMonth(ele){month=parseInt(ele.value);calender()}
	function setYear(ele){year=parseInt(ele.value);calender()}
	function setValue(ele) {
		if(ele.parentNode.className=='week' && ele.firstChild){
			var dayOut=ele.firstChild.nodeValue;
			if(dayOut < 10) dayOut='0'+dayOut;
			var monthOut=month+1;
			if(monthOut < 10) monthOut='0'+monthOut;
			target.value=dayOut+'-'+monthOut+'-'+year;
			removeCalender();
		}
	}
	function removeCalender() {
		var parentEle=$("calender");
		while(parentEle.firstChild) parentEle.removeChild(parentEle.firstChild);
		$('basis').parentNode.removeChild($('basis'));
	}
	function calender() {
		var parentEle=$("calender");
		parentEle.onmouseover=function(e) {
			var ele=setTarget(e);
			if(ele.parentNode.className=='week' && ele.firstChild && ele!=hoverEle) {
				if(hoverEle) hoverEle.className=hoverEle.className.replace(/hoverEle ?/,'');
				hoverEle=ele;
				ele.className='hoverEle '+ele.className;
			} else {
				if(hoverEle) {
					hoverEle.className=hoverEle.className.replace(/hoverEle ?/,'');
					hoverEle=false;
				}
			}
		}
		while(parentEle.firstChild) parentEle.removeChild(parentEle.firstChild);
		function check(){
			if(year%4==0&&(year%100!=0||year%400==0))allMonth[1]=29;
			else allMonth[1]=28;
		}
		function addClass (name) { if(!currentClass){currentClass=name} else {currentClass+=' '+name} };
		if(month < 0){month+=12; year-=1}
		if(month > 11){month-=12; year+=1}
		if(year==yearMax-1) yearMax+=1;
		if(year==yearMin) yearMin-=1;
		check();
		var close_window=newElement('p',[['id','close',1]],false,parentEle);

		var close_link = newElement('a', [['href','javascript:removeCalender()',1],['className','close_window',1]], 'Fermer', close_window);
		var img_close=newElement('img', [['src','img/close_small.gif',1], ['id','img_close',1]], false, close_link);
		var control=newElement('p',[['id','control',1]],false,parentEle);
		var controlPlus=newElement('a', [['href','javascript:month=month-1;calender()',1],['className','controlPlus',1]], '<', control);
		var select=newElement('select', [['onchange',function(){setMonth(this)}]], false, control);
		for(var i=0; i<allNameOfMonths.length; i++) newElement('option', [['value',i,1]], allNameOfMonths[i], select);
		select.selectedIndex=month;
		select=newElement('select', [['onchange',function(){setYear(this)}]], false, control);
		for(var i=yearMin; i<yearMax; i++) newElement('option', [['value',i,1]], i, select);
		select.selectedIndex=year-yearMin;
		controlPlus=newElement('a', [['href','javascript:month++;calender()',1],['className','controlPlus',1]], '>', control);
		check();
		currentDay=1-new Date(year,month,1).getDay();
		if(currentDay > 0) currentDay-=7;
		currentDayZero=currentDay;
		var newMonth=newElement('table',[['cellSpacing',0,1],['onclick',function(e){setValue(setTarget(e))}]], false, parentEle);
		var newMonthBody=newElement('tbody', false, false, newMonth);
		var tr=newElement('tr', [['className','head',1]], false, newMonthBody);
		tr=newElement('tr', [['className','weekdays',1]], false, newMonthBody);
		for(i=0;i<7;i++) td=newElement('td', false, allNameOfWeekDays[i], tr);
		tr=newElement('tr', [['className','week',1]], false, newMonthBody);
		for(i=0; i<allMonth[month]-currentDayZero; i++){
			var currentClass=false;
			currentDay++;
			if(currentDay==day && month==monthZero && year==yearZero) addClass ('today');
			if(currentDay <= 0 ) {
				if(currentDayZero!=-7) td=newElement('td', false, false, tr);
			}
			else {
				if((currentDay-currentDayZero)%7==0) addClass ('holiday');
				td=newElement('td', (!currentClass ? false : [['className',currentClass,1]] ), currentDay, tr);
				if((currentDay-currentDayZero)%7==0) tr=newElement('tr', [['className','week',1]], false, newMonthBody);
			}
			if(i==allMonth[month]-currentDayZero-1){
				i++;
				while(i%7!=0){i++;td=newElement('td', false, false, tr)};
			}
		}

	}
	function showCalender(ele) {
		if($('basis')) { removeCalender() }
		else {
			target=$(ele.id.replace(/for_/,''));
			var basis=ele.parentNode.insertBefore(document.createElement('div'),ele);
			basis.id='basis';
			newElement('div', [['id','calender',1]], false, basis);
			calender();

		}
	}


	if(!window.Node){
	  var Node = {ELEMENT_NODE : 1, TEXT_NODE : 3};
	}

	function checkNode(node, filter){
	  return (filter == null || node.nodeType == Node[filter] || node.nodeName.toUpperCase() == filter.toUpperCase());
	}

	function getChildren(node, filter){
	  var result = new Array();
	   if(node != null)
	  {
	  	var children = node.childNodes;
	  	for(var i = 0; i < children.length; i++)
		{
			if(checkNode(children[i], filter)) result[result.length] = children[i];
	 	}
	  }
	  return result;
	}

	function getChildrenByElement(node){
	  return getChildren(node, "ELEMENT_NODE");
	}

	function getFirstChild(node, filter){
	  var child;
	  var children = node.childNodes;
	  for(var i = 0; i < children.length; i++){
		child = children[i];
		if(checkNode(child, filter)) return child;
	  }
	  return null;
	}

	function getFirstChildByText(node){
	  return getFirstChild(node, "TEXT_NODE");
	}

	function getNextSibling(node, filter){
	  for(var sibling = node.nextSibling; sibling != null; sibling = sibling.nextSibling){
		if(checkNode(sibling, filter)) return sibling;
	  }
	  return null;
	}
	function getNextSiblingByElement(node){
			return getNextSibling(node, "ELEMENT_NODE");
	}
/****************************************/


/********** Menu Functions & Properties   ******************/

	var activeMenu = null;

	function showMenu() {
	  if(activeMenu){
		activeMenu.className = "";
		getNextSiblingByElement(activeMenu).style.display = "none";
	  }
	  if(this == activeMenu){
		activeMenu = null;
	  } else {
		this.className = "on";
		getNextSiblingByElement(this).style.display = "block";
		activeMenu = this;
	  }
	  return false;
	}

	function initMenu(){
	  var menus, menu, text, aRef, i;
	  menus = getChildrenByElement($("menu"));
	  for(i = 0; i < menus.length; i++){
		menu = menus[i];
		text = getFirstChildByText(menu);
		aRef = document.createElement("a");
		if(aRef == null){
			menu.replaceChild(aRef, text);
			aRef.appendChild(text);
			aRef.href = "#";
			aRef.onclick = showMenu;
			aRef.onfocus = function(){this.blur()};
		}
	  }
	}

	if(document.createElement) window.onload = initMenu;

	function cacher_menu() {
		with ($('menu_container')){
			if (className=='active')
			{
				className='inactive';
				$('limage').src="images/sortirmenu.gif";
			}
			else
			{
				className='active';
				$('limage').src="images/rentrermenu.gif";
			}
		}
	}


	/************** fonction pour afficher/cacher le menu     ***********/

	function ShowHideMenu(menu,onouoff) {
		if ($) {
			monmenu = $(menu);
			mondivmenu = $("menu");
			monadmin = $("admin");
			monhelp = $("aide");
		}
		else if(document.all) {
			monmenu = document.all[menu];
			mondivmenu = document.all["menu"];
			monadmin = document.all["admin"];
			monhelp = document.all["aide"];
		}
		else return;

		if (menu == "ssnav") {
			if (onouoff == "fermee") {
				monmenu.style.display = "block";
				monadmin.className = "on";
			} else if (onouoff == "ouverte") {
				monmenu.style.display = "none";
				monadmin.className = "off";
			}
		}
		else if (menu == "ssnavaide") {
			if (onouoff == "fermee") {
				monmenu.style.display = "block";
				monhelp.className = "on";
			} else if (onouoff == "ouverte") {
				monmenu.style.display = "none";
				monhelp.className = "off";
			}
		}
		else {
			if (onouoff == "on") {
				monmenu.style.display = "block";
				mondivmenu.className = "on";
			} else if (onouoff == "off") {
				monmenu.style.display = "none";
				mondivmenu.className = "off";
			}
		}
	}

	function HideMenu(menu) {
		var massnav = null ;
		if ($) {

			if (menu == "ssnav")
			{
				massnav = $("ssnav");
			}
			else if (menu == "ssnavaide")
			{
				massnav = $("ssnavaide");
			}
		}
		else if(document.all) {
			if (menu == "ssnav")
			{
				massnav = document.all["ssnav"];
			}
			else if (menu == "ssnavaide")
			{
				massnav = document.all["ssnavaide"];
			}
		}
		else return;

		if(massnav != null)
		{
			massnav.style.display = "none";
		}
	}
/****************************************/

	function changeCouleur(ligne,couleurPolice,isBold) { //, couleurFond
			//ligne.style.backgroundColor = couleurFond;
			ligne.style.color=couleurPolice;
			}

	function changeCouleur2(cellule,couleurPolice,isBold)
	{
		var ligne = cellule.parentNode;
		ligne.style.color=couleurPolice;
	}



function ouvreFenetre(page, largeur, hauteur)
	 {
	  window.open(page, "", "scrollbars=yes,menubar=no,toolbar=no,resizable=yes,width="
	  + largeur + ",height=" + hauteur );
	}

/************** Fonction utilisées pour la gestion des listes multiples  ***********/

/**
* Move item(s) from a multiple list to another
*
* @param  list1 Select Object Source list
* @param  list2 Select Object Destination list
*/
function Move(list1,list2)
{
	for (i=0;i<list1.length;i++)
	{
		if(list1[i].selected)
		{
			o = new Option(list1.options[list1.options.selectedIndex].text,list1.options[list1.options.selectedIndex].value,false, true);
			list2.options[list2.options.length]=o;
			list1.options[list1.options.selectedIndex]=null;
			i--;
		}
	}
}

/**
* Move an item from a multiple list to another
*
* @param  list1 Select Object Source list
* @param  list2 Select Object Destination list
*/
function moveclick(list1,list2)
{
	o = new Option(list1.options[list1.options.selectedIndex].text,list1.options[list1.options.selectedIndex].value,false, true);
	list2.options[list2.options.length]=o;
	list1.options[list1.options.selectedIndex]=null;
}

/**
* Select all items from a multiple list
*
* @param  list Select Object Source list
*/
function selectall(list)
{
	for (i=0;i<list.length;i++)
	{
		list[i].selected = true;
	}
}

/**
* Move an item from a multiple list to another
*
* @param  list1 Select identifier of the Source list
* @param  list2 Select identifier of the Destination list
*/
function moveclick_ext( id_list1, id_list2)
{
	var list1 = $(id_list1);
	var list2 = $(id_list2);
	moveclick(list1,list2);
}

/**
* Select all items from a multiple list
*
* @param  list Select identifier of the Source list
*/
function selectall_ext(id_list)
{
	var list = $(id_list);
	selectall(list);
}

/**
* Move item(s) from a multiple list to another
*
* @param  list1 Select identifier of the Source list
* @param  list2 Select identifier of the Destination list
*/
function Move_ext( id_list1, id_list2)
{
	var list1 = $(id_list1);
	var list2 = $(id_list2);
	Move(list1,list2);
}
/*********************************************************/


var BrowserDetect = {
	init: function () {
		this.browser = this.searchString(this.dataBrowser) || "An unknown browser";
		this.version = this.searchVersion(navigator.userAgent)
			|| this.searchVersion(navigator.appVersion)
			|| "an unknown version";
		this.OS = this.searchString(this.dataOS) || "an unknown OS";
	},
	searchString: function (data) {
		for (var i=0;i<data.length;i++)	{
			var dataString = data[i].string;
			var dataProp = data[i].prop;
			this.versionSearchString = data[i].versionSearch || data[i].identity;
			if (dataString) {
				if (dataString.indexOf(data[i].subString) != -1)
					return data[i].identity;
			}
			else if (dataProp)
				return data[i].identity;
		}
	},
	searchVersion: function (dataString) {
		var index = dataString.indexOf(this.versionSearchString);
		if (index == -1) return;
		return parseFloat(dataString.substring(index+this.versionSearchString.length+1));
	},
	dataBrowser: [
		{
			string: navigator.userAgent,
			subString: "Chrome",
			identity: "Chrome"
		},
		{ 	string: navigator.userAgent,
			subString: "OmniWeb",
			versionSearch: "OmniWeb/",
			identity: "OmniWeb"
		},
		{
			string: navigator.vendor,
			subString: "Apple",
			identity: "Safari",
			versionSearch: "Version"
		},
		{
			prop: window.opera,
			identity: "Opera"
		},
		{
			string: navigator.vendor,
			subString: "iCab",
			identity: "iCab"
		},
		{
			string: navigator.vendor,
			subString: "KDE",
			identity: "Konqueror"
		},
		{
			string: navigator.userAgent,
			subString: "Firefox",
			identity: "Firefox"
		},
		{
			string: navigator.vendor,
			subString: "Camino",
			identity: "Camino"
		},
		{		// for newer Netscapes (6+)
			string: navigator.userAgent,
			subString: "Netscape",
			identity: "Netscape"
		},
		{
			string: navigator.userAgent,
			subString: "MSIE",
			identity: "Explorer",
			versionSearch: "MSIE"
		},
		{
			string: navigator.userAgent,
			subString: "Gecko",
			identity: "Mozilla",
			versionSearch: "rv"
		},
		{ 		// for older Netscapes (4-)
			string: navigator.userAgent,
			subString: "Mozilla",
			identity: "Netscape",
			versionSearch: "Mozilla"
		}
	],
	dataOS : [
		{
			string: navigator.platform,
			subString: "Win",
			identity: "Windows"
		},
		{
			string: navigator.platform,
			subString: "Mac",
			identity: "Mac"
		},
		{
			   string: navigator.userAgent,
			   subString: "iPhone",
			   identity: "iPhone/iPod"
	    },
		{
			string: navigator.platform,
			subString: "Linux",
			identity: "Linux"
		}
	]

};

BrowserDetect.init();

/**
* Resize frames in a modal
*
* @param  id_modal String Modal identifier
* @param  id_frame String Frame identifier of the frame to resize
* @param  resize_width Integer New width
* @param  resize_height Integer New Height
*/
function resize_frame_process(id_modal, id_frame, resize_width, resize_height)
{
	var modal = $(id_modal);
	if(modal)
	{
		var newwith = modal.getWidth();
		//alert(newwith);
		var newheight = modal.getHeight() - 30;

		//console.log('modal width '+newwith);
		var frame2 = $(id_frame);
		var div_left = $('validleft');
		var windowSize = new Array();
		if(resize_width == true && frame2 != null)
		{
			windowSize = getWindowSize();
			//console.log('window '+windowSize);
			navName = BrowserDetect.browser;
			navVersion = BrowserDetect.version;

			if(id_frame == 'file_iframe')
			{
				//~ var div_right = $('validright');
				//~ if(div_right && div_left)
				//~ {
					//~ Element.setStyle(div_right, {width : ((newwith - div_left.getWidth()) -50) +'px' });
					//~ //div_right.style.width=((newwith - div_left.getWidth()) -50) +'px';
					//~ newwith = (newwith - div_left.getWidth())- 30;
				//~ }
				if(navName == 'Explorer')
				{
					if(navVersion < 7)
					{
						newwith = (windowSize[0] - 800) - 10;
					}
					else
					{
						newwith = (windowSize[0] - 520) - 10;
					}
				}
				else if(navName == 'Firefox' || navName == 'Mozilla')
				{
					 newwith = (windowSize[0] - 550) - 10;
				}
				else
				{
					newwith = (windowSize[0] - 550) - 10;
				}
			}
			else if(id_frame == 'viewframe')
			{
				if(navName == 'Explorer')
				{
					if(navVersion < 7)
					{
						newwith = (windowSize[0] - 510);
					}
					else
					{
						newwith = (windowSize[0] - 480);
					}

				}
				else if(navName == 'Firefox')
				{
					newwith = (windowSize[0] - 500);
				}
				else
				{
					newwith = (windowSize[0] - 500);
				}
			}
			else if(id_frame == 'viewframevalid')
			{
				if(navName == 'Explorer')
				{
					newwith = (windowSize[0] - 520) - 10;
				}
				else if(navName == 'Firefox')
				{
					newwith = (windowSize[0] - 550) - 10;
				}
				else
				{
					newwith = (windowSize[0] - 550) - 10;
				}
			}
			else
			{
				newwith = (windowSize[0] - 600);
			}
			frame2.style.width =  newwith +"px";
		}
		if(resize_height == true && frame2 != null)
		{
			frame2.style.height = newheight +"px";
		}
	}
}

function getWindowSize(){
	if (window.innerWidth || window.innerHeight){
		var width = window.innerWidth;
		var height = window.innerHeight;
	}
	else{
		var width = $(document.documentElement).getWidth();
		var height = $(document.documentElement).getHeight();
	}
	return [width,height];
}


/**
 * Redirect to a given url
 *
 * @param url String Url to redirect to
 */
function redirect_to_url(url)
{
   location.href=url;
}

/**
 * redirect to a given url when the session expirates
 *
 * @param expiration Integer Expiration time (in minutes))
 * @param url String Url to redirect to
 */
function session_expirate(expiration, url)
{
	var chronoExpiration=setTimeout('redirect_to_url(\''+url+'\')', expiration*60*1000);
}

/*************** Tabs functions *****************/

function opentab(eleframe, url)
{
	var eleframe1 = $(eleframe);
	eleframe1.src = url;
}

function opentab_window(eleframe, url)
{
	var eleframe1 = window.top.$(eleframe);
	eleframe1.src = url;
}
/********************************/


/*************** Usergroups administration functions *****************/

function expertmodehide()
{
	var frm = $('frm_expert_mode');
	var input = $('where');
	var label1 = $('label_expert_hide');
	var label2 = $('label_expert_show');
	frm.width = "1000";
	frm.height = "370";
	input.className  = "input_expert_hide";
	label1.className  = "input_expert_hide";
	label2.className  = "input_expert_show";

}

function expertmodeview(coll_id)
{
	var frm = $('frm_expert_mode');
	var input = $('where');
	var label1 = $('label_expert_hide');
	var label2 = $('label_expert_show');
	frm.width = "1";
	frm.height = "1";
	input.className  = "input_expert_show";
	label1.className  = "input_expert_show";
	label2.className  = "input_expert_hide";
	//document.location.reload();
	document.location.href = 'add_grant.php?expertmode=true&collection=' + coll_id;
}
/********************************/

/*************** Modal functions *****************/

/**
 * Create a modal window
 *
 * @param txt String Text of the modal (innerHTML)
 * @param id_mod String Modal identifier
 * @param height String Modal Height in px
 * @param width String Modal width in px
 * @param mode_frm String Modal mode : fullscreen or ''
 */
 function createModal(txt, id_mod,height, width, mode_frm){
	if(height == undefined || height=='')
	{
		height = '100px';
	}
	if(width == undefined || width=='')
	{
		width = '400px';
	}
	if( mode_frm == 'fullscreen')
	{
		width = (screen.availWidth-10)+'px';
		height = (screen.availHeight-10)+'px';
	}
	if(id_mod && id_mod!='')
	{
		id_layer = id_mod+'_layer';
	}
	else
	{
		id_mod = 'modal';
		id_layer = 'lb1-layer';
	}
	var tmp_width = width;
	var tmp_height = height;

    var layer = new Element('div', {'id':id_layer, 'class' : 'lb1-layer', 'style' : "display:block;filter:alpha(opacity=70);opacity:.70;z-index:"+get_z_indexes()['layer']+';width :'+document.getElementsByTagName('html')[0].offsetWidth+"px;height:"+(document.getElementsByTagName('body')[0].offsetHeight - 20)+'px;'});


	if( mode_frm == 'fullscreen')
	{
   		var fenetre = new Element('div', {'id' :id_mod,'class' : 'modal', 'style' :'top:0px;left:0px;width:'+width+';height:'+height+";z-index:"+get_z_indexes()['modal']+";position:absolute;" });
	}
	else
	{
		var fenetre = new Element('div', {'id' :id_mod,'class' : 'modal', 'style' :'top:50px;left:50px;'+'width:'+width+';height:'+height+";z-index:"+get_z_indexes()['modal']+";margin-top:"+getScrollXY()[1]+15+'px;margin-left:'+getScrollXY()[0]+15+'px;position:absolute;' });
	}

	//~ if( mode_frm == 'fullscreen')
	//~ {
		//~ //fenetre.writeAttribute('style','top:0px;left:0px;width:'+width+';height:'+height+";z-index:"+get_z_indexes()['modal']+";");
		//~ fenetre.setStyle({top: '0px', left :'0px', width: tmp_width, height :tmp_height, zIndex:get_z_indexes()['modal']});
	//~ }
	//~ else
	//~ {
		//~ fenetre.writeAttribute('style','top:50px;left:50px;'+'width:'+width+';height:'+height+";z-index:"+get_z_indexes()['modal']+";");
		//~ fenetre.style.marginTop = getScrollXY()[1]+15+'px';
		//~ fenetre.style.marginLeft = getScrollXY()[0]+15+'px';
	//~ }
	//alert('test 5');
	//$(document.body).insert(layer);
	//$(document.body).insert(fenetre);
	Element.insert(document.body,layer);
	Element.insert(document.body,fenetre);
   // layer.style.width=document.getElementsByTagName('html')[0].offsetWidth+"px";
	//alert('test 6');
	//var layer_height = document.getElementsByTagName('body')[0].offsetHeight + 500;
    //layer.style.height=layer_height+'px';
	//layer.style.height=(document.getElementsByTagName('body')[0].offsetHeight - 20)+'px';
	//alert('test 7');
	if( mode_frm == 'fullscreen')
	{
		fenetre.style.width = (document.getElementsByTagName('html')[0].offsetWidth - 30)+"px";
		fenetre.style.height = layer.style.height;
	}
//	alert('test 8');
	//fenetre.update(txt);
	Element.update(fenetre,txt);
//	alert('test 9');
    Event.observe(layer, 'mousewheel', function(event){Event.stop(event);}.bindAsEventListener(), true);
    Event.observe(layer, 'DOMMouseScroll', function(event){Event.stop(event);}.bindAsEventListener(), true);
	//alert('test 10');
}

/**
 * Destroy a modal window
 *
 * @param id_mod String Modal identifier
 */
function destroyModal(id_mod){
	if(id_mod == undefined || id_mod=='')
	{
		id_mod = 'modal';
		id_layer = 'lb1-layer';
	}
	else
	{
		id_layer = id_mod+'_layer';
	}
	if(isAlreadyClick)
	{
		isAlreadyClick = false;
	}
    document.getElementsByTagName('body')[0].removeChild($(id_mod));
    document.getElementsByTagName('body')[0].removeChild($(id_layer));
}

/**
 * Calculs the z indexes for a modal
 *
 * @return array The z indexes of the layer and the modal
 */
function get_z_indexes()
{

	//var elem = document.getElementsByClassName('modal');
	var elem = $$('modal');
	if(elem == undefined || elem == NaN)
	{
		return {layer : 995, modal : 1000};
	}
	else
	{
		var max_modal = 1000;
		for(var i=0; i< elem.length; i++)
		{
			if(elem[i].style.zIndex > max_modal)
			{
				max_modal = elem[i].style.zIndex;
			}
		}
		max_layer = max_modal +5;
		max_modal = max_modal +10;

		return {layer : max_layer, modal : max_modal};
	}
}

/**
 * Calculs the scroll X and Y of the window
 *
 * @return array The ScrollX and the ScrollY of the window
 */
 function getScrollXY(){
    if (window.top.scrollX || window.top.scrollY){
        var scrollX = window.scrollX;
        var scrollY = window.scrollY;
    }else{
        var scrollX = document.body.scrollLeft;
        var scrollY = document.body.scrollTop;
    }
    return [scrollX,scrollY];
 }

/***********************************************************************/

/*************** Actions management functions and vars *****************/

/**
* Pile of the actions to be executed
* Object
*/
var pile_actions = { values :[],
			 action_push:function(val){this.values.push(val);},
			 action_pop:function(){return this.values.pop();}
			};
var res_ids = '';
var do_nothing = false;
/**
 * Executes the last actions in the actions pile
 *
 */
function end_actions()
{

	var req_action = pile_actions.action_pop();
	if(req_action)
	{
		if(req_action.match('to_define'))
		{
			req_action = req_action.replace('to_define', res_ids);
			do_nothing = true;
		}
		//console.log('end_action : '+req_action);
		try{
			eval(req_action);
		}
		catch(e)
		{
			alert('Error during pop action : '+req_action);
		}
	}
}

/**
 * If the action has open a modal, destroy the action modal, and if this is the last action of the pile, reload the opener window
 *
 */
function close_action(id_action, page)
{
	var modal = $('modal_'+id_action);
	if(modal)
	{
		destroyModal('modal_'+id_action);
	}
	if(pile_actions.values.length == 0)
	{
		//console.log('close');
//		alert('close');
		//console.log(page);

		if(page != '' && page != NaN && page && page != null )
		{
			do_nothing = false;
			window.top.location.href=page;

		}
		else if(do_nothing == false)
		{
			window.top.location.reload();
		}
		do_nothing = false;
	}
}

/**
 * Validates the form of an action
 *
 * @param current_form_id String  Identifier of the form to validate
 * @param path_manage_script String  Path to the php script called in the Ajax object to validates the form
 * @param id_action String  Action identifier
 * @param values String  Action do something on theses items  listed in this string
 * @param table String  Table used for the action
 * @param module String  Action is this module
 * @param coll_id String  Collection identifier
 * @param mode String Action mode : mass or page
 */
function valid_action_form(current_form_id, path_manage_script, id_action, values, table, module, coll_id, mode)
{
	var frm_values = get_form_values(current_form_id);
	frm_values = frm_values.replace("\'", "\\'", 'g');
	frm_values = frm_values.replace('\"', '\\"', 'g');
	var chosen_action_id = get_chosen_action(current_form_id);
	//console.log('values : '+values+', table : '+table+', module : '+module+', coll_id : '+coll_id+', chosen_action_id : '+chosen_action_id+' frm_values : '+frm_values);
	if(values &&  table && module && coll_id && chosen_action_id != '')
	{
		new Ajax.Request(path_manage_script,
		{
			method:'post',
			parameters: { action_id : id_action,
					  form_to_check : current_form_id,
					  req : 'valid_form',
					  form_values : frm_values
					},
					onSuccess: function(answer){
					//console.log('valid form answer  '+answer.responseText);
					//alert('valid form answer  '+answer.responseText);
					eval('response='+answer.responseText);
					if(response.status == 0 ) //form values checked
					{
						if(response.manage_form_now == false)
						{
							//console.log('manage_form_now false');
							pile_actions.action_push("action_send_form_confirm_result( '"+path_manage_script+"', '"+mode+"', '"+id_action+"', '"+values+"','"+table+"', '"+module+"','"+coll_id+"',  '"+frm_values+"');");

							if(chosen_action_id == 'end_action')
							{
							//	alert('last_action');
								//console.log('last_action');
								end_actions();
							}
							else
							{
								//console.log('not last');
								//alert('not last');
								action_send_first_request(path_manage_script, mode, chosen_action_id, values, table, module, coll_id);
							}
						}
						else
						{
							pile_actions.action_push("action_send_first_request( '"+path_manage_script+"', '"+mode+"', '"+chosen_action_id+"', 'to_define','"+table+"', '"+module+"','"+coll_id+"');");
							action_send_form_confirm_result(path_manage_script, mode, id_action, values, table, module, coll_id, frm_values);
						}
					}
					else //  Form Params errors
					{
						//console.log(response.error_txt);
						try{
								$('frm_error_'+id_action).innerHTML = response.error_txt;
							}
						catch(e){}
					}
				},
				onFailure: function(){
				}
			});
	}
	else
	{
		if(console)
		{
			console.log('Action Error!');
		}
		//alert('Action Error!');
	}
}

/**
 * Get the chosen action identifier in the form
 *
 * @param form_id String  Identifier of the form
 */
function get_chosen_action(form_id)
{
	var frm = $(form_id);
	for(var i=0; i< frm.elements.length;i++)
	{
		if(frm.elements[i].id == 'chosen_action')
		{
			if(frm.elements[i].tagName == 'INPUT')
			{
				return frm.elements[i].value;
			}
			else if(frm.elements[i].tagName == 'SELECT')
			{
				return frm.elements[i].options[frm.elements[i].selectedIndex].value;
			}
			else
			{
				break;
			}
		}
	}
	return '';
}

/**
 * Get the values of the form in an string (Id_field1#field_value1$$Id_field2#field_value2$$)
 *
 * @param form_id String  Identifier of the form
 * @return String  Values of the form
 */
function get_form_values(form_id)
{
	var frm = $(form_id);
	var val = '';
	if(frm)
	{
		for(var i=0; i< frm.elements.length;i++)
		{
			if(frm.elements[i].tagName == 'INPUT' || frm.elements[i].tagName == 'TEXTAREA')
			{
				if((frm.elements[i].tagName == 'INPUT' && frm.elements[i].type != 'checkbox' && frm.elements[i].type != 'radio') || frm.elements[i].tagName == 'TEXTAREA' )
				{
					val += frm.elements[i].id+'#'+frm.elements[i].value+'$$';
				}
				else
				{
					if(frm.elements[i].checked == true)
					{
						val += frm.elements[i].id+'#'+frm.elements[i].value+'$$';
					}
				}
			}
			else if(frm.elements[i].tagName == 'SELECT') // to do : multiple list
			{
				val += frm.elements[i].id+'#'+frm.elements[i].options[frm.elements[i].selectedIndex].value+'$$';
			}
		}
		val.substring(0, val.length -3);
	}
	//console.log(val);
	return val;
}

/**
 * Sends the first ajax request to create a form or resolve a simple action
 *
 * @param path_manage_script String  Path to the php script called in the Ajax object
 * @param mode_req String Action mode : mass or page
 * @param id_action String  Action identifier
 * @param res_id_values String  Action do something on theses items listed in this string
 * @param tablename String  Table used for the action
 * @param modulename String  Action is this module
 * @param id_coll String  Collection identifier
 */
function action_send_first_request( path_manage_script, mode_req,  id_action, res_id_values, tablename, modulename, id_coll)
{
	//alert('action_send_first_request');
	if(id_action == undefined || id_action == null || id_action  == '')
	{
		window.top.$('main_error').innerHTML = arr_msg_error['choose_action'];
		//console.log('Choisissez une action !');
	}
	if(res_id_values == undefined || res_id_values == null || res_id_values == '')
	{
		window.top.$('main_error').innerHTML += '<br/>' + arr_msg_error['choose_one_doc'];
		//console.log('Choisissez au moins un doc !');
	}
	//alert('res_id_values : '+res_id_values+', id_action '+id_action+', tablename '+tablename+', modulename : '+modulename+', id_coll : '+id_coll+', mode_req : '+mode_req);
	if(res_id_values != ''  && id_action != '' && tablename != '' && modulename != ''  && id_coll != '' && (mode_req == 'page' || mode_req == 'mass'))
	{
		//alert('values : '+res_id_values+', id_action : '+id_action+', table : '+tablename+', module : '+modulename+', coll_id : '+id_coll+', mode : '+mode_req);
		new Ajax.Request(path_manage_script,
		{
		    method:'post',
		    parameters: { values : res_id_values,
							  action_id : id_action,
							  mode : mode_req,
							  req : 'first_request',
							  table : tablename,
							  coll_id : id_coll,
							  module : modulename
							  },
		        onSuccess: function(answer){
				eval("response = "+answer.responseText);
			//	console.log(answer.responseText);
				//alert(answer.responseText);
				var page_result = response.page_result;
				if(response.status == 0 ) // No confirm or form asked
				{
					//console.log('action_send_first_request OK');
					end_actions();
					close_action(id_action, page_result);

				}
				else if(response.status == 2) // Confirm asked to the user
				{
					//console.log('confirm');
					//alert('confirm');
					var modal_txt='<h2>'+response.confirm_content+'</h2>';
					modal_txt += '<p class="buttons">';
					modal_txt += '<input type="button" name="submit" id="submit" value="'+response.validate+'" class="button" onclick="action_send_form_confirm_result( \''+path_manage_script+'\', \''+mode_req+'\',\''+id_action+'\', \''+res_id_values+'\', \''+tablename+'\', \''+modulename+'\', \''+id_coll+'\');"/>';
					modal_txt += ' <input type="button" name="cancel" id="cancel" value="'+response.cancel+'" class="button" onclick="destroyModal(\'modal_'+id_action+'\');"/></p>';
					//console.log(modal_txt);
					window.top.createModal(modal_txt, 'modal_'+id_action, '150px', '300px');
				}
				else if(response.status == 3) // Form to fill by the user
				{
					//alert('test');
					window.top.createModal(response.form_content,'modal_'+id_action, response.height, response.width, response.mode_frm);
				}
				else // Param errors
				{
					if(console)
					{
						console.log('param error');
					}
					else
					{
						alert('param error');
					}
					//close_action(id_action,  page_result);
				}
		    },
		    onFailure: function(){
				//alert('erreur');
				}
		});
	}
}

/**
 * Gets an item (DOM object)) from its identifier
 *
 * @param elem_id String  Item identifier
 * @return DOM Object or false
 */
function get_elem( elem_id)
{
     if ($(elem_id))
     {
         return $(elem_id);
     }
     else
     {
		var tab = window.frames;
         for(var i=0; i < tab.length;i++)
         {
			if(tab[i].document)
			{
				return tab[i].$(elem_id);
			}
			else if(tab[i].contentDocument)
			{
				return tab[i].content$(elem_id);
			}
         }
     }
     return false;
}

/**
 * Sends the second ajax request to process a form
 *
 * @param path_manage_script String  Path to the php script called in the Ajax object
 * @param mode_req String Action mode : mass or page
 * @param id_action String  Action identifier
 * @param res_id_values String  Action do something on theses items listed in this string
 * @param tablename String  Table used for the action
 * @param modulename String  Action is this module
 * @param id_coll String  Collection identifier
 * @param values_new_form String  Values of the form to process
 */
function action_send_form_confirm_result(path_manage_script, mode_req, id_action, res_id_values, tablename, modulename, id_coll, values_new_form)
{
	//console.log('debut send_form');
	if(res_id_values != '' && (mode_req == 'mass' || mode_req == 'page')
			&& id_action != ''  && tablename != ''
			&& modulename!= '' &&  id_coll != '')
		{

			//console.log('avant obj : '+path_manage_script);
			new Ajax.Request(path_manage_script,
			{
				method:'post',
				parameters: { values : res_id_values,
							  action_id : id_action,
							  mode : mode_req,
							  req : 'second_request',
							  table : tablename,
							  coll_id : id_coll,
							  module : modulename,
							  form_values : values_new_form
							  },
				onSuccess: function(answer){
				//	console.log('answer '+answer.responseText);
				//	alert('answer '+answer.responseText);
					eval('response='+answer.responseText);
					if(response.status == 0 ) //Form or confirm processed ok
					{
						res_ids = response.result_id;
						//console.log(res_ids);
					//	alert(res_ids);
						end_actions();
						var page_result = response.page_result;
						close_action(id_action, page_result);
					}
					else //  Form Params errors
					{
						//console.log(response.error_txt);
						try{
							//$('frm_error').updateContent(response.error_txt); // update the error div in the modal form
							$('frm_error').innerHTML = response.error_txt;
							}
						catch(e){}
					}
				},
				onFailure: function(){
				//console.log('dans ton c** !!');
				}
			});
		}
}
/***********************************************************************/


/*************** Xml management functions : used with tiny_mce to load mapping_file *****************/

/**
 * Remove a node in a xml file
 *
 * @param node Node Object Node to remove
 */
function remove_tag(node){
	if(!node.data.replace(/\s/g,''))
		node.parentNode.removeChild(node);
}

/**
 * Clean an xml doc
 *
 * @param xml XML Object Xml string to clean
 */
function clean_xml_doc(xml)
{
	// TO DO : remove comment, do not work yet
	if(xml)
	{
		var nodes=xml.getElementsByTagName('*');
		for(var i=0;i<nodes.length;i++){
			a=nodes[i].previousSibling;
			if(a && (a.nodeType==3 || a.nodeName=='#comment'))
				remove_tag(a);
			b=nodes[i].nextSibling;
			if(b && (b.nodeType==3 || b.nodeName=='#comment'))
				remove_tag(b);
			c=nodes[i];
		}

	}
	return xml;
}

/**
 * Loads an XML File
 *
 * @param xmlfile String Path or URL of the xml file to load
 */
function load_xml_file(xmlfile)
{
	var xml;
	if( window.ActiveXObject && /Win/.test(navigator.userAgent) )
   	{
    	xml= new ActiveXObject("Microsoft.XMLDOM");
		xml.async = false;
		xml.load(xmlfile);
		return clean_xml_doc(xml);
   	}
    else if( document.implementation && document.implementation.createDocument )
    {
	 	xml = document.implementation.createDocument("", "", null);
		xml.async = false;
		xml.load(xmlfile);
		return clean_xml_doc(xml);
    }
    else
    {
	   return false;
    }
}
/***********************************************************************/

/**
 * Resize the current window
 *
 * @param x Integer  X size
 * @param y Integer  Y size
 */
function resize(x,y) {
parent.window.resizeTo(x,y);
}

/**
 * Sets the current window to fullscreen
 */
function fullscreen() {
parent.window.moveTo(0,0);
resize(screen.width-10,screen.height-30);
}

/**
 * Displays in a string all items of an array + its methods
 *
 */
function print_r(x, max, sep, l) {

	    l = l || 0;
	    max = max || 10;
	    sep = sep || ' ';

	    if (l > max) {
	        return "[WARNING: Too much recursion]\n";
	    }

	    var
	        i,
	        r = '',
	        t = typeof x,
	        tab = '';

	    if (x === null) {
	        r += "(null)\n";
	    } else if (t == 'object') {

	        l++;

	        for (i = 0; i < l; i++) {
	            tab += sep;
	        }

	        if (x && x.length) {
	            t = 'array';
	        }

	        r += '(' + t + ") :\n";

	        for (i in x) {
	            try {
	                r += tab + '[' + i + '] : ' + print_r(x[i], max, sep, (l + 1));
	            } catch(e) {
	                return "[ERROR: " + e + "]\n";
	            }
	        }

	    } else {

	        if (t == 'string') {
	            if (x == '') {
	                x = '(empty)';
	            }
	        }

	        r += '(' + t + ') ' + x + "\n";

	    }

	    return r;

	}

/**
 * Includes a javascript file
 *
 * @param file_url String  Url of the js file to include
 * @param in_html Bool  The Html is already loaded (true) or not (false)
 */
function include_js(file_url, in_html)
{
	if(in_html == true)
	{
		var head = $$("head")[0];
		//var head = document.getElementsByTagName("head")[0];
		var node = document.createElement("script");
		node.setAttribute('type','text/javascript');
		node.setAttribute('src',file_url);
		head.insert(node);

		//head.appendChild(node);
	}
	else
	{
		document.write('<script type="text/javascript" src="'+file_url+'"></script>');
	}
}

/**
 * Unlock a basket using Ajax
 *
 * @param path_script String Path to the Ajax script
 * @param id String Basket id to unlock
 * @param coll String Collection identifier of the basket
 **/
function unlock(path_script, id, coll)
{
	if(path_script && res_id && coll_id)
	{
		new Ajax.Request(path_script,
		{
			method:'post',
			parameters: {
							res_id : id,
							coll_id : coll
						  },
			onSuccess: function(answer){

				eval('response='+answer.responseText);
				if(response.status == 0 )
				{

						//console.log('Unlock OK');

				}
				else
				{
					if(console)
					{
						console.log('Pb unlock');
					}
					else
					{
						alert('Pb unlock');
					}
				}
			},
			onFailure: function(){
			}
		});
	}
}

/**
 * Show or hide the data related to a person in the contacts admin
 *
 * @param is_corporate Bool True the contact is corporate, Fasle otherwise
 **/
function show_admin_contacts( is_corporate, display)
{
	var display_value = display || 'inline';
	if(is_corporate == true)
	{
		$("title_p").style.display = "none";
		$("lastname_p").style.display = "none";
		$("firstname_p").style.display = "none";
		$("function_p").style.display = "none";
		$('lastname_mandatory').style.visibility = 'hidden';
		$('society_mandatory').style.visibility = 'visible';
	}
	else
	{
		$("title_p").style.display = display_value;
		$("lastname_p").style.display = display_value;
		$("firstname_p").style.display = display_value;
		$("function_p").style.display =display_value;
		$('lastname_mandatory').style.visibility = 'visible';
		$('society_mandatory').style.visibility = 'hidden';
	}
}

/**
 * Returns in an array all values selected from check boxes or radio buttons
 *
 * @param name_input String Item Name
 * @return Array Checked values
 **/
function get_checked_values(name_input)
{
	var arr = [];
	var items = document.getElementsByName(name_input);
	for(var i=0; i< items.length; i++)
	{
		if(items[i].checked == true)
		{
			arr.push(items[i].value);
		}
	}
	return arr;
}

/**
 * Clears a form (empties it)
 *
 * @param form_id String Form identifier
 **/
function clear_form(form_id)
{
	var frm = $(form_id);
	if(frm)
	{
		var items = frm.getElementsByTagName('INPUT');
		for(var i=0; i<items.length;i++)
		{
			if(items[i].type == "text")
			{
				items[i].value ='';
			}
		}
		items = frm.getElementsByTagName('TEXTAREA');
		for(var i=0; i<items.length;i++)
		{
			items[i].value ='';
		}
		items = frm.getElementsByTagName('SELECT');
		for(var i=0; i<items.length;i++)
		{
			if(items[i].multiple == "true")
			{
				// TO DO
			}
			else
			{
				items[i].options[0].selected ='selected';
			}
		}
	}
}

/*************** Apps Reports functions *****************/

/**
 * Function used to display the user access report
 *
 * @param url String Form Url of the php script which gets the results
 **/
function valid_userlogs(url)
{
	var user_div = $('user_id');
	var user_id_val = '';
	if(user_div)
	{
		user_id_val = user_div.value;
	}

	if( url )
	{
		new Ajax.Request(url,
		{
		    method:'post',
		    parameters: {
				user : user_id_val
						},
		        onSuccess: function(answer){
			//	alert(answer.responseText);
				var div_to_fill = $('result_userlogsstat');
				if(div_to_fill)
				{
					div_to_fill.innerHTML = answer.responseText;
				}
			}
		});
	}
}

/**
 * Function used to display the letterbox reports
 *
 * @param url String Form Url of the php script which gets the results
 **/
function valid_report_by_period(url)
{
	var type_period = '';
	var type_report = 'graph';
	var datestart = '';
	var dateend = '';
	var year = '';
	var month = '';
	var error = '';
	var report_id = '';

	var report_id_item = $('id_report');
	if(report_id_item)
	{
		report_id = report_id_item.value;
	}

	var report = $('report_array');
	if(report && report.checked)
	{
		type_report = 'array';
	}
	var period_custom = $('custom_period');
	var period_year = $('period_by_year');
	var period_month = $('period_by_month');
	if(period_custom && period_custom.checked)
	{
		type_period = 'custom_period';
		var datestart_item = $('datestart');
		if(datestart_item)
		{
			datestart = datestart_item.value;
		}
		var dateend_item = $('dateend');
		if(dateend_item)
		{
			dateend = dateend_item.value;
		}
	}
	else if(period_year && period_year.checked)
	{
		type_period = 'period_year';
		var years_list = $('the_year');
		if(years_list)
		{
			year =  years_list.options[years_list.selectedIndex].value;
		}
	}
	else if(period_month && period_month.checked)
	{
		type_period = 'period_month';
		var months_list = $('the_month');
		if(months_list)
		{
			month =  months_list.options[months_list.selectedIndex].value;
		}
	}
	else
	{
		error = 'empty_type_period';
	}

	if(type_period  != '' && url && error == '')
	{
		new Ajax.Request(url,
		{
		    method:'post',
		    parameters: {
				id_report : report_id,
				report_type : type_report,
				period_type : type_period,
				the_year : year,
				the_month : month,
				date_start : datestart,
				date_fin : dateend
						},
		        onSuccess: function(answer){
			//	alert(answer.responseText);
				var div_to_fill = $('result_period_report');
			//	console.log(div_to_fill);
				if(div_to_fill)
				{
					div_to_fill.innerHTML = answer.responseText;
				}
			}
		});
	}
}

/**
 * Launch the Ajax autocomplete object to activate autocompletion on a field
 *
 * @param path_script String Path to the Ajax script
 **/
function launch_autocompleter(path_script, id_text, id_div)
{
	var input = id_text ;
	var div  = id_div ;

	if( path_script)
	{
		// Ajax autocompleter object creation
	 		new Ajax.Autocompleter(input, div, path_script, {
		 method:'get',
		 paramName:'Input',
		 minChars: 2
		 });
	}
	else
	{
		if(console != null)
		{
			console.log('error parameters launch_autocompleter function');
		}
		else
		{
			alert('error parameters launch_autocompleter function');
		}
	}
}

/**
 * Gets the indexes for a given collection and fills a div with it
 *
 * @param url String Url to the Ajax script
 * @param id_coll String Collection identifier
 **/
function get_opt_index(url, id_coll)
{
	if(url && id_coll)
	{
		new Ajax.Request(url,
		{
			method:'post',
			parameters: {
				coll_id : id_coll
					},
					onSuccess: function(answer){
						var div_to_fill = $('opt_index');
					//	console.log(div_to_fill);
						if(div_to_fill)
						{
							div_to_fill.innerHTML = answer.responseText;
						}
					}
		});
	}
}

/**
 * Gets the indexes for a given document type (used in details page)
 *
 * @param doctype_id String Document type identifier
 * @param url String Url to the Ajax script
 * @param error_empty_type Message to displays if the type is empty
 **/
function change_doctype_details(doctype_id, url, error_empty_type)
{
	if(doctype_id != null && doctype_id != '' && doctype_id != NaN)
	{
		new Ajax.Request(url,
		{
		    method:'post',
		    parameters: { type_id : doctype_id
						},
		        onSuccess: function(answer){
				eval("response = "+answer.responseText);
			//	alert(answer.responseText);
				if(response.status == 0 )
				{
					var indexes = response.new_opt_indexes;
					var div_indexes = $('opt_indexes');
					if(div_indexes )
					{
						div_indexes.update(indexes);
					}

				}
				else
				{
					try{
					//	$('main_error').innerHTML = response.error_txt;
						}
					catch(e){}
				}
			}
		});
	}
	else
	{
		try{
			//$('main_error').innerHTML = error_empty_type;
			}
		catch(e){}
	}
}
-->
