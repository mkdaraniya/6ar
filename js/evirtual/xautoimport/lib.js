function addFieldMapping()
{
	var entityType = $('profile_entity_type').value;
	Element.insert($('map_container_'+entityType), {bottom: $('map_template_'+entityType).innerHTML});
}
function removeFieldMapping(button)
{
	Element.remove(button.parentNode);
}

function addSFieldMapping()
{
	var entityType = $('profile_entity_type').value;
	Element.insert($('s_map_container_'+entityType), {bottom: $('s_map_template_'+entityType).innerHTML});
}
function removeSFieldMapping(button)
{
	Element.remove(button.parentNode);
}

function setMapFileField(select)
{
	select.parentNode.getElementsByTagName('input')[0].value = select.value;
}

function addCategoryFieldMapping()
{
	var entityType = $('profile_category_type').value;
	Element.insert($('map_container_'+entityType), {bottom: $('map_template_'+entityType).innerHTML});
}
function removeCategoryFieldMapping(button)
{
	Element.remove(button.parentNode);
}

function setSessionStore(storeid,baseurl)
{
	var allSelectedStoreId=getSelectValues(storeid);
	var neww=allSelectedStoreId.toString();
	var gg=neww.split(",").join("-");
	new Ajax.Request(baseurl, {
  	method: 'get',
  	parameters: {storeid:gg}
	});
	ProfileTabsFormMapping=document.getElementById("profile_tabs_form_category");
	ProfileTabsFormMappingClassName=ProfileTabsFormMapping.className;
	document.getElementById("profile_tabs_form_category").className=ProfileTabsFormMappingClassName+" notloaded";
}

function getSelectValues(select1) {
  var result = [];
  var options = select1.options;
  var opt;

  for (var i=0, iLen=options.length; i<iLen; i++) {
    opt = options[i];

    if (opt.selected) {
      result.push(opt.value || opt.text);
    }
  }
  return result;
}

/* COOKIES OBJECT */
var Cookies = {
        // Initialize by splitting the array of Cookies
	init: function () {
		var allCookies = document.cookie.split('; ');
		for (var i=0;i<allCookies.length;i++) {
			var cookiePair = allCookies[i].split('=');
			this[cookiePair[0]] = cookiePair[1];
		}
	},
        // Create Function: Pass name of cookie, value, and days to expire
	create: function (name,value,days) {
		if (days) {
			var date = new Date();
			date.setTime(date.getTime()+(days*24*60*60*1000));
			var expires = "; expires="+date.toGMTString();
		}
		else var expires = "";
		document.cookie = name+"="+value+expires+"; path=/";
		this[name] = value;
	},
        // Erase cookie by name
	erase: function (name) {
		this.create(name,'',-1);
		this[name] = undefined;
	}
};
Cookies.init();