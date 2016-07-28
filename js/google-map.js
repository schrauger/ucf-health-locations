function setup_google_map() {
    var zoomlevel = 10; // keep zoom at 12 on the map (higher number is closer zoom)

    var currentInfoWindow = new google.maps.InfoWindow();
    var infoWindows = {};
    var markers = {};
    var mapOptions = {
        center: new google.maps.LatLng(28.48197, -81.25351),
        zoom: 15,
        mapTypeId: google.maps.MapTypeId.ROADMAP
    };
    var map = new google.maps.Map(document.getElementById("map"),
        mapOptions);
    map.set('styles', [
        {
            "featureType": "administrative",
            "stylers": [ {"visibility": "off"} ]
        }, {
            "featureType": "landscape",
            "stylers": [ {"visibility": "off"} ]
        }, {
            "featureType": "poi",
            "stylers": [ {"visibility": "off"} ]
        }, {
            "featureType": "poi.medical",
            "stylers": [ {"visibility": "on"} ]
        }, {
            "featureType": "poi.school",
            "stylers": [ {"visibility": "on"} ]
        }, {
            "featureType": "transit",
            "stylers": [ {"visibility": "off"} ]
        }, {
            "featureType": "water",
            "stylers": [ {"visibility": "on"} ]
        }, {}
    ]);

    //Create points object
    var points = jQuery('input[name="ucf_health_locations"]').data('locations'); // this input has a JSON object with all the info - see ucf_health_locations->insert_location_content()
    var locations = {}; // will contain just the latitude_X_longitude objects

    //Create College of Medicine object
    /*
    points[ 'com' ] = {};
    points[ 'com' ][ 'title' ] = "UCF College of Medicine";
    points[ 'com' ][ 'content' ] = "UCF College of Medicine <br /> <br /> 6850 Lake Nona Boulevard <br /> Orlando, FL 32827 <br /> (407) 266-1000";
    points[ 'com' ][ 'lat' ] = 28.367368;
    points[ 'com' ][ 'lon' ] = -81.280358;
    */
    var map_icon_width = 18;
    var map_icon_height = 28;
    var map_sprite_padding = 2; // number of pixels between each sprite icon
    var map_sprite_top = 0; // vertical location of the icon within the sprite (what changes in the loop)
    var map_icon = {
        url: '/wp-content/plugins/ucf-health-locations/icons/map_icons.png', // sprite file
        size: new google.maps.Size(map_icon_width, map_icon_height), // lenghts and height
        origin: '', // the top left of the sprite icon - set within the loop
        anchor: new google.maps.Point(map_icon_width / 2, map_icon_height) // the pointy part of the icon ((x/2,y) is the middle bottom)
    }
    var map_icon_count = 0; // our foreach loop has string keys, so we must manually count each iteration to calculate sprite location
    $.each(points, function (key, point) {
		// we have some hidden locations with no lat-long. don't show those on the map.
		if (point['latitude'] && point['longitude']){
		    /*
		    Calculate sprite icon location
		     */
		    map_sprite_top = ((map_icon_height + map_sprite_padding) * map_icon_count);
		    map_icon.origin = new google.maps.Point(0, map_sprite_top);
		    console.log(map_sprite_top);

		    /*
		    Add a marker on the map for this location
		     */
		    locations[ key ] = {};
		    locations[ key ] = new google.maps.LatLng(point[ 'latitude' ], point[ 'longitude' ]);
		    markers[ key ] = {};
		    markers[ key ] = new google.maps.Marker({
		        position: locations[ key ],
		        map: map,
		        title: point[ 'name' ],
		        icon: map_icon
		    });

		    /*
		    Add a description box when the marker is selected
		     */
		    var infoWindowHTML = info_window_html(point);
		    infoWindows[ key ] = {}
		    infoWindows[ key ] = new google.maps.InfoWindow({
		        content: infoWindowHTML
		    });

		    /**
		     * if user clicks on a map point, show the map dialog info box,
		     * and also highlight the extended details outside of the map.
		     */
		    google.maps.event.addListener(markers[ key ], 'click', function () {
		        if (currentInfoWindow) {
		            currentInfoWindow.close();
		        }
		        show_details(key);
		        currentInfoWindow = infoWindows[ key ];
		        currentInfoWindow.open(map, markers[ key ]);
		    });


		}
	    map_icon_count += 1;
    });

    /**
     * if user clicks on a location outside the map, show the extended details
     * and highlight the map point and show the map dialog info box.
     */
    $('div.locations ul li.locations').each(function () {
        google.maps.event.addDomListener(this, 'click', function () {
			if (currentInfoWindow) {
                currentInfoWindow.close();
            }            
			var office_location = $(this).data('location');
            show_details(office_location);
            map.panTo(locations[ office_location ]);
            map.setZoom(zoomlevel);
            
            currentInfoWindow = infoWindows[ office_location ];
            currentInfoWindow.open(map, markers[ office_location ]);
        });
    });

    // map: an instance of GMap3
    // latlng: an array of instances of GLatLng
    var latlngbounds = new google.maps.LatLngBounds();
    for (var point in locations) {
        latlngbounds.extend(locations[ point ]);
    }

    map.setCenter(latlngbounds.getCenter());
    map.fitBounds(latlngbounds);
    var listener = google.maps.event.addListener(map, "idle", function () {
        //if (map.getZoom() > 16) {
        map.setZoom(zoomlevel);
        //}
        google.maps.event.removeListener(listener);
    });
}

/**
 * builds an HTML string with divs for each element. will be displayed on the map when the user clicks a point
 * @param location_object
 */

function info_window_html(location_object) {
    var return_string = '';
    return_string += '<div class="info_window">';
    return_string += '<div class="name"><strong><a style="font-size: 16px;" href="' + location_object[ 'directions_url' ] + '">' + location_object.name + '</a></strong></div>';
    return_string += '<p>' + nl2br(info_window_html_div_if_exists(location_object, 'description')) + '<a href="#info">More Info</a></p>';
    return_string += '<strong>Directions:<div class="directions_url" target="_blank"><a href="' + location_object.directions_url + '">Google Maps</a> | <a href="' + location_object.directions_apple_url + '">Apple iOS Maps</a> | <a href="' + location_object.written_directions_pdf + '" target="_blank">PDF File</a></div>';
    //  return_string += nl2br(info_window_html_div_if_exists(location_object, 'address'));
    //  return_string += nl2br(info_window_html_div_if_exists(location_object, 'hours_of_operation'));
    //  return_string += nl2br(info_window_html_div_if_exists(location_object, 'phone_number'));
    //  return_string += info_window_html_div_if_exists(location_object, 'url');
    return_string += '</div>';
    return return_string;
}

/**
 * checks the object key to see if a value is set. if so, it returns a div with the proper class and value. if not, returns an empty string.
 * @param location_object Array such as 'lake_nona' with key->value objects in the array.
 * @param location_object_property_key Ex address, description, name, etc
 * @returns {string}
 */
function info_window_html_div_if_exists(location_object, location_object_property_key) {
    if (location_object[ location_object_property_key ]) {
        return '<div class="' + location_object_property_key + '">' + location_object[ location_object_property_key ] + '</div>'
    } else {
        return '';
    }
}


function nl2br(str, is_xhtml) {
    var breakTag = (is_xhtml || typeof is_xhtml === 'undefined') ? '<br />' : '<br>';
    return (str + '').replace(/([^>\r\n]?)(\r\n|\n\r|\r|\n)/g, '$1' + breakTag + '$2');
}

function show_details_click(clicked_div) {
    show_details($(clicked_div).data('location'));

}

function show_details(office_location) {
    hide_location_details(); // hide other location info
    // highlight clicked item
    $('div.locations ul li.locations[data-location="' + office_location + '"]').addClass('selected');

    // show location extended details
    $('div.locations div.info[data-location="' + office_location + '"]').removeClass('hidden').addClass('selected');

}

function hide_location_details() {
    // hide the location info first.
    $('div.locations ul li.locations').removeClass('selected')
    $('div.locations div.info').removeClass('selected').addClass('hidden')
}
function setup_location_details() {
    hide_location_details();
    // auto-select the first item
    //$('div.locations ul li.locations').first().trigger('click');
    show_details_click($('div.locations ul li.locations').first()); // select first in list, but don't initially select the point on google map


}


jQuery(document).ready(function () {
    setup_location_details();
    setup_google_map();
});
