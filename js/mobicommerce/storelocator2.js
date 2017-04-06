var i=0;
function getInfoStore(url, dateformat)
{
    //jQuery('reload_store').show();  
    if(jQuery('#shipping_date_div').length)
        jQuery('#shipping_date_div').hide();    
    jQuery('#date-please-wait').css('display','block');
    jQuery('#shipping_date').css('display','none');
    jQuery('#shipping_date_trig').css('display','none');
    var parameters = {
        store_id: jQuery('#store_id').val()
    };

    var request = new Ajax.Request(url,
            {
                method: 'post',
                parameters: parameters,
                onSuccess: function(transport) {
                    if (transport.responseText) {

                        var response = JSON.parse(transport.responseText);
						if(jQuery('shipping_date'))
                        Calendar.setup({
                            inputField: "shipping_date",
                            ifFormat: dateformat,
                            showsTime: false,
                            electric: false,
                            button: "shipping_date_trig",
                            singleClick: true,
                            disableFunc: function(date) {
                                var today = new Date();
                                var check = false;                                                                 
                                //check special day
                                if (response.specialdate != null)
                                    if(response.specialdate.indexOf(parseFloat(date.print("%Y%m%d")))!==-1){                                       
                                            return false;
                                        }                                    
                                //check holiday
                                
                                if (response.holidaydate != null)
                                    if(response.holidaydate.indexOf(parseFloat(date.print("%Y%m%d")))!==-1){                             
                                        return 'holiday';
                                        }
                                    
                                //check closed day
                                if (date.getFullYear() < today.getFullYear()) {
                                    return true;
                                } else if (date.getMonth() < today.getMonth() && date.getFullYear() <= today.getFullYear()) {
                                    return true;
                                } else if (date.getDate() < today.getDate() && date.getMonth() <= today.getMonth() && date.getFullYear() <= today.getFullYear()) {
                                    return true;
                                }
                                if (today.getDate() == date.getDate()) {
                                    return false;
                                }
                                for (i = 0; i < parseFloat(response.closed.length); i++) {
                                    if (response.closed[i] == date.getDay()) {
                                        return true;
                                    }
                                }
                                
                            },onUpdate:function(){
                                var storelocator = new Storelocator(changedate_url);
                                storelocator.changeDate(jQuery('changedate_url').value, jQuery('changedate_format').value);
                            }
                            
                        });
                    }
                    if(jQuery('store_id').value){
                        if(jQuery('time-box'))
                            jQuery('time-box').hide();
                        if (jQuery('date-box'))
                            jQuery('date-box').show();
                        if(jQuery('select-store'))
                            jQuery('select-store').show();
                        jQuery('date-please-wait').style.display = 'none';
                        jQuery('shipping_date').style.display = 'block';
                        jQuery('shipping_date_trig').style.display = 'block';                                          if(jQuery('shipping_date_div'))
                            jQuery('shipping_date_div').show();
                         
                    }
                    
                }
            }
    );
}

var StorelocatorMap = Class.create();
StorelocatorMap.prototype = {
    initialize: function(latitude, longtitude, zoom_value) {

        this.stockholm = new google.maps.LatLng(latitude, longtitude);
        this.zoom_value = zoom_value;
        this.marker = null;
        this.map = null;
        google.maps.event.addDomListener(window, 'load', this.initGoogleMap.bind(this));


    },
    initGoogleMap: function() {
        var mapOptions = {
            zoom: this.zoom_value,
            mapTypeId: google.maps.MapTypeId.ROADMAP,
            center: this.stockholm
        };

        this.map = new google.maps.Map(jQuery('googleMap'),
                mapOptions);
		 var color =  jQuery('pin_color').value;
         if(!color){color = 'f75448'}
        var pinImage = 'http://chart.apis.google.com/chart?chst=d_map_pin_letter&chld=%E2%80%A2|'+color;
        this.marker = new google.maps.Marker({
            map: this.map,
            draggable: true,
            position: this.stockholm,
			icon: pinImage
        });
        google.maps.event.addListener(this.marker, 'dragend', function(event) {
            jQuery('#store_latitude_value').val(event.latLng.lat());
            jQuery('#store_longtitude_value').val(event.latLng.lng());
            jQuery('#store_latitude').val(event.latLng.lat());
            jQuery('#store_longtitude').val(event.latLng.lng());
            jQuery('#store_latitude').css('background','rgb(250, 230, 180)');
            jQuery('#store_longtitude').setStyle('background','rgb(250, 230, 180)');
        }.bind(this));
        google.maps.event.addListener(this.map, 'zoom_changed', function() {
            var zoomLevel = this.map.getZoom();
            jQuery('#zoom_level_value').val(zoomLevel);
            jQuery('#zoom_level').val(zoomLevel);
            jQuery('#zoom_level').css('background','rgb(250, 230, 180)');
        }.bind(this));
    },
    reSizeMap: function() {
        // map = this.map;

    },
    reLoadMap: function() {
        //        var  service = new google.maps.places.PlacesService(map);      
        //        service.textSearch(request, this.processSearchResults.bind(this));   
    }
}


var _currentPickupDate = null;

var Storelocator = Class.create();
Storelocator.prototype = {
    initialize: function(changeStoreUrl) {

        this.changeStoreUrl = changeStoreUrl;

    },
    setUrl: function(url)
    {
        this.changeStoreUrl = url;
    },
    changeStore: function() {
        
        if (jQuery('shipping_date'))
            jQuery('shipping_date').value = '';

        var storeId;

        storeId = jQuery('store_id').value;

        var url = this.changeStoreUrl;

        url = url + 'store_id/' + storeId;

        var request = new Ajax.Request(url, {method: 'get', onFailure: ""});

        if (jQuery('storelocator-box') && jQuery('storelocator-box') != null)
            jQuery('storelocator-box').style.display = 'block';        
        if (jQuery('date-box') && !jQuery('date-box').value)
            jQuery('time-box').hide();        

        //end all store mode
        if (jQuery('curr-store') != null)
        {
            var curr_store_id = jQuery('curr-store').value;

            if (jQuery('store-info-' + curr_store_id) != null)
            {
                jQuery('store-info-' + curr_store_id).style.display = 'none';
            }

            if (jQuery('store-info-' + storeId) != null)
            {
                jQuery('store-info-' + storeId).style.display = 'block';
                jQuery('curr-store').value = storeId;
            }
        }
        
        //end all store mode
    },

    selectStoreShipping: function(is_storelocator)
    {
        var url = this.changeStoreUrl;

        if (is_storelocator == true)
            url += 'is_storelocator/1';
        else
            url += 'is_storelocator/2';

        var request = new Ajax.Request(url, {method: 'get', onFailure: ""});
    },
    changeTime: function(url)
    {
        var shipping_date = jQuery('shipping_date').value;
        var shipping_time = jQuery('shipping_time').value;
        
        if (shipping_date == '')
        {
            alert('Please select shipping date');
            jQuery('shipping_time').selectedIndex = 0;
            return;
        }

        url += 'shipping_date/' + shipping_date + '/shipping_time/' + shipping_time;

        var request = new Ajax.Request(url, {method: 'get', onFailure: ""});
    },
    changeDate: function(url, dateformat)
    {

        jQuery('time-box').show();
        var shipping_date = jQuery('shipping_date').value;
        var store_id = jQuery('store_id').value;
        
        switch (dateformat)
        {
            case '%Y-%m-%d':
                var yyy = shipping_date.substr(0, 4);
                var mm = shipping_date.substr(5, 2);
                var dd = shipping_date.substr(8, 2);
                valid_shipping_date = mm + '-' + dd + '-' + yyy;
                break;
            case '%d-%m-%Y':
                var mm = shipping_date.substr(3, 2);
                var dd = shipping_date.substr(0, 2);
                var yyy = shipping_date.substr(6, 4);
                valid_shipping_date = mm + '-' + dd + '-' + yyy;
                break;
            case '%Y-%d-%m':
                var mm = shipping_date.substr(8, 2);
                var dd = shipping_date.substr(5, 2);
                var yyy = shipping_date.substr(0, 4);
                valid_shipping_date = mm + '-' + dd + '-' + yyy;
                break;
            default:
                valid_shipping_date = shipping_date;
                break;
        }

        if (store_id == '' && shipping_date != '')
        {
            alert('Please select store');
            jQuery('time-box').hide();
            jQuery('shipping_date').value = '';
            return;
        }

        if (store_id == '')
            return;

        if (!isDate(valid_shipping_date))
        {
            alert('Please enter a valid date');
            jQuery('shipping_date').value = '';
            jQuery('time-box').hide();
            return;
        }
        if (jQuery('shipping_time_div'))
            jQuery('shipping_time_div').show();
        jQuery('date-notation').innerHTML = '';

        url += 'shipping_date/' + shipping_date + '/store_id/' + store_id;      
        
        
        jQuery('time-please-wait').style.display = 'block';
        jQuery('shipping_time').style.display = 'none';

        var request = new Ajax.Updater('shipping_time', url, {
            method: 'get',
            onComplete: function() {
                after_changedate();
            },
            onFailure: function() {
                jQuery('time-box').hide();
            }
        });

    }
}

function after_changedate()
{
    jQuery('time-box').show();
    checkHoliday();
    jQuery('shipping_time').style.display = 'block';
    jQuery('time-please-wait').style.display = 'none';
}

var StoreLocation = Class.create();
StoreLocation.prototype = {
    initialize: function(url) {
        this.url = url;
    },
    changecountry: function(url)
    {
        var regionId = jQuery('store_country_id').value;

        url += 'country_id/' + regionId;

        new Ajax.Updater('store_region_id', url, {method: 'get', onFailure: ""});
    },
    changeregion: function(url)
    {
        var regionId = jQuery('store_region_id').value;

        url += 'region_id/' + regionId;

        new Ajax.Updater('store_city_id', url, {method: 'get', onFailure: ""});
    },
    changecity: function(url)
    {
        var cityId = jQuery('store_city_id').value;

        url += 'city_id/' + cityId;

        new Ajax.Updater('suburb_id', url, {method: 'get', onFailure: ""});
    },
    changesuburb: function(url)
    {
        var countryId = jQuery('store_country_id').value;
        var regionId = jQuery('store_region_id').value;
        var cityId = jQuery('store_city_id').value;
        var suburbId = jQuery('suburb_id').value;

        url += 'country_id/' + countryId;
        url += '/region_id/' + regionId;
        url += '/city_id/' + cityId;
        url += '/suburb_id/' + suburbId;

        new Ajax.Updater('store_id', url, {method: 'get', onComplete: function(transport) {
                loadedStore();
            }, onFailure: ""});

        jQuery('storelocator-box').style.display = 'block';
        jQuery('store_id').style.display = 'none';
        jQuery('store-please-wait').style.display = 'block';
    },
    changesuburbPagestore: function(url)
    {
        var countryId = jQuery('store_country_id').value;
        var regionId = jQuery('store_region_id').value;
        var cityId = jQuery('store_city_id').value;
        var suburbId = jQuery('suburb_id').value;

        url += 'country_id/' + countryId;
        url += '/region_id/' + regionId;
        url += '/city_id/' + cityId;
        url += '/suburb_id/' + suburbId;

        new Ajax.Updater('page-store', url, {method: 'get', onFailure: ""});
    }

}

function loadedStore()
{
    jQuery('store_id').style.display = 'block';
    jQuery('store-please-wait').style.display = 'none';
}


/*
 function checkStore()
 {
 if(jQuery('store_id').options.length == 1)
 {
 jQuery('store-notation').innerHTML = jQuery('store_not_found_nonce').value;
 } else if(jQuery('store_id').options.length == 0){
 
 alert('Very early shipping time !');
 
 jQuery('store_id').innerHTML = '<option value="">Select store</option>';
 
 }		
 }
 */
function checkHoliday()
{
    if (jQuery('shipping_time').options.length == 1)
    {
        //jQuery('date-notation').innerHTML = jQuery('holiday_nonce').value;
        switch (jQuery('shipping_time').options[0].value) {
            case 'invalid_date':
                alert('Invalid date!');
                jQuery('shipping_date').value = '';
                jQuery('time-box').hide();
                break;
            case 'early_date_nonce':
                alert(jQuery('early_date_nonce').value);
                jQuery('shipping_date').value = '';
                jQuery('time-box').hide();
                break;
            case 'holiday_nonce':
                var comment = jQuery('shipping_time').options[0].id;
                alert(comment.replace(/_/gi, ' '));
                jQuery('shipping_date').value = '';
                jQuery('time-box').hide();
                break;
            case 'store_closed':
                alert('Store will be closed on this day');
                jQuery('shipping_date').value = '';
                jQuery('time-box').hide();
                break;
        }
    }

}

function changeDate(url, dateformat)
{
    if (jQuery('shipping_date').value == _currentPickupDate)
        return;

    _currentPickupDate = jQuery('shipping_date').value;

    var storelocator = new Storelocator(url);
    storelocator.changeDate(url, dateformat);
}

// check date valid 
var dtCh = "-";
var minYear = 1900;
var maxYear = 2100;

function isInteger(s) {
    var i;
    for (i = 0; i < s.length; i++) {
        // Check that current character is number.
        var c = s.charAt(i);
        if (((c < "0") || (c > "9")))
            return false;
    }
    // All characters are numbers.
    return true;
}

function stripCharsInBag(s, bag) {
    var i;
    var returnString = "";
    // Search through string's characters one by one.
    // If character is not in bag, append to returnString.
    for (i = 0; i < s.length; i++) {
        var c = s.charAt(i);
        if (bag.indexOf(c) == -1)
            returnString += c;
    }
    return returnString;
}

function daysInFebruary(year) {
    // February has 29 days in any year evenly divisible by four,
    // EXCEPT for centurial years which are not also divisible by 400.
    return (((year % 4 == 0) && ((!(year % 100 == 0)) || (year % 400 == 0))) ? 29 : 28);
}
function DaysArray(n) {
    for (var i = 1; i <= n; i++) {
        this[i] = 31
        if (i == 4 || i == 6 || i == 9 || i == 11) {
            this[i] = 30
        }
        if (i == 2) {
            this[i] = 29
        }
    }
    return this
}

function isDate(dtStr) {
    var daysInMonth = DaysArray(12)
    var pos1 = dtStr.indexOf(dtCh)
    var pos2 = dtStr.indexOf(dtCh, pos1 + 1)
    var strMonth = dtStr.substring(0, pos1)
    var strDay = dtStr.substring(pos1 + 1, pos2)
    var strYear = dtStr.substring(pos2 + 1)
    strYr = strYear
    if (strDay.charAt(0) == "0" && strDay.length > 1)
        strDay = strDay.substring(1)
    if (strMonth.charAt(0) == "0" && strMonth.length > 1)
        strMonth = strMonth.substring(1)
    for (var i = 1; i <= 3; i++) {
        if (strYr.charAt(0) == "0" && strYr.length > 1)
            strYr = strYr.substring(1)
    }
    month = parseInt(strMonth)
    day = parseInt(strDay)
    year = parseInt(strYr)
    if (pos1 == -1 || pos2 == -1) {
        //	alert("The date format should be : mm/dd/yyyy")
        return false
    }
    if (strMonth.length < 1 || month < 1 || month > 12) {
        //	alert("Please enter a valid month")
        return false
    }
    if (strDay.length < 1 || day < 1 || day > 31 || (month == 2 && day > daysInFebruary(year)) || day > daysInMonth[month]) {
        //	alert("Please enter a valid day")
        return false
    }
    if (strYear.length != 4 || year == 0 || year < minYear || year > maxYear) {
        //	alert("Please enter a valid 4 digit year between "+minYear+" and "+maxYear)
        return false
    }
    if (dtStr.indexOf(dtCh, pos2 + 1) != -1 || isInteger(stripCharsInBag(dtStr, dtCh)) == false) {
        //	alert("Please enter a valid date")
        return false
    }
    return true
}

var StorelocatorFrontEnd = Class.create();
StorelocatorFrontEnd.prototype = {
    initialize: function(latitude, longtitude, zoom_value, idMap) {
        this.myLatlng = new google.maps.LatLng(latitude, longtitude);
        this.markerArray = [];
        this.markearryIdStore = [];
        this.myOptions = {
            zoom: zoom_value,
            center: this.myLatlng,
            mapTypeId: google.maps.MapTypeId.ROADMAP
        };
        this.map = new google.maps.Map(document.getElementById(idMap), this.myOptions);
        this.bounds = new google.maps.LatLngBounds();
        this.rendererOptions = {
            map: this.map
        };
        this.directionsDisplay = new google.maps.DirectionsRenderer(this.rendererOptions);
        this.directionsService = new google.maps.DirectionsService();
    },
    extendPoint: function(marker) {
        this.bounds.extend(marker);
    },
    placeMarker: function(point, store_info, storeId, image, zoomLevel, infoWindow, x, storeObject) {
        var marker;
        if (image) {
            marker = new google.maps.Marker({
                position: point,
                map: this.map,
                icon: image,
                store_id: storeId
            });
        }
        else {
            marker = new google.maps.Marker({
                position: point,
                map: this.map,
                store_id: storeId
            });
        }
        storeObject.marker = marker;
        this.markerArray.push(marker);
        google.maps.event.addListener(marker, 'click', function(event) {
            infoWindow.setContent(store_info);
            infoWindow.setPosition(event.latLng);
            infoWindow.open(this.map, marker);
            if (zoomLevel != 0) {
                this.map.setZoom(zoomLevel);
            }
            this.setActiveForm(storeId, x);
        }.bind(this));

        //this.getPoupForm(store_info, point, marker, zoomLevel, storeId, infoWindow)
    },
    getPoupForm: function(store_info, point, zoomLevel, storeId, infoWindow) {
        jQuery('id' + storeId).observe('click', function(event) {
            infoWindow.setContent(store_info);
            infoWindow.setPosition(point);
            //marker_point = new google.maps.LatLng(setLat, setLon);
            this.map.setCenter(point);
            infoWindow.open(this.map);
            if (zoomLevel != 0) {
                this.map.setZoom(zoomLevel);
            }
        }.bind(this));
    },
    setBoundFill: function() {
        this.map.fitBounds(this.bounds);
        this.map.setCenter(this.bounds.getCenter());
    },
    setActiveForm: function(id, x) {
        jQueryjQuery('.active').invoke('removeClassName', 'active');
        jQuery('id' + id).addClassName('active');
        if (x) {
            for (i = 0; i <= this.markearryIdStore.length - 1; i++) {
                try {
                    if (this.markearryIdStore[i] == id) {
                        jQuery('magestore-storelocator-getdirection-' + id).show();
                    } else {
                        jQuery('magestore-storelocator-getdirection-' + this.markearryIdStore[i]).hide();
                    }
                } catch (err) {

                }
            }
            this.directionsDisplay.setPanel(document.getElementById('magestore-storelocator-directionsPanel-' + id));
            this.calcRoute(id);
        }
    },
    createMarker: function(marker_point) {
        //marker_point = new google.maps.LatLng(latitude, longitude);
        image_icon = new google.maps.MarkerImage("http://chart.apis.google.com/chart?chst=d_map_pin_letter&chld=%E2%80%A2|0099FF");
        //shadow = new google.maps.MarkerImage("http://chart.apis.google.com/chart?chst=d_map_pin_shadow");
        marker = new google.maps.Marker({
            position: marker_point,
            map: this.map,
            icon: image_icon
                    // shadow: shadow
        });
        this.markerArray.push(marker);
        this.extendPoint(marker_point);
    },
    createRadius: function(radius) {
        var myCircle = new google.maps.Circle({
            center: this.markerArray[this.markerArray.length - 1].getPosition(),
            map: this.map,
            radius: radius,
            strokeColor: "#FF0000",
            strokeOpacity: 0.8,
            strokeWeight: 2,
            fillColor: "#B9D3EE",
            fillOpacity: 0.35
        });
        var myBounds = myCircle.getBounds();

        //filters markers
        for (var i = this.markerArray.length; i--; ) {
            if (!myBounds.contains(this.markerArray[i].getPosition()))
            {
                this.markerArray[i].setMap(null);
                jQuery("id" + this.markerArray[i].store_id).hide();
            }
        }
        this.map.setCenter(this.markerArray[this.markerArray.length - 1].getPosition());
        this.map.setZoom(this.map.getZoom() + 1);
    },
    calcRoute: function(idstore) {
        destination_lat = jQuery('storelocator-lat-' + idstore).value;
        destination_lng = jQuery('storelocator-lng-' + idstore).value;
        finalMarker = this.markerArray.length - 1;
//        for(var i=0; i< finalMarker; i++){
        var request = {
            origin: this.markerArray[finalMarker].getPosition(),
            destination: new google.maps.LatLng(destination_lat, destination_lng),
            waypoints: [],
            travelMode: google.maps.DirectionsTravelMode.DRIVING
        };
        this.directionsService.route(request, function(response, status) {
            if (status === google.maps.DirectionsStatus.OK) {
                this.directionsDisplay.setDirections(response);
            }
        }.bind(this));
//        }        
    },
    computeTotalDistance: function(result) {
        var total = 0;
        var myroute = result.routes[0];
        for (var i = 0; i < myroute.legs.length; i++) {
            total += myroute.legs[i].distance.value;
        }
    }
}
