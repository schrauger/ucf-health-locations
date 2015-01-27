function setup_google_map() {
    var currentInfoWindow = new google.maps.InfoWindow();
    var infoWindows = {};
    var markers = {};
    var mapOptions = {
        center: new google.maps.LatLng(28.48197, -81.25351),
        zoom: 11,
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

    $.each(points, function (key, point) {
        locations[ key ] = {};
        locations[ key ] = new google.maps.LatLng(point[ 'latitude' ], point[ 'longitude' ]);
        markers[ key ] = {};
        markers[ key ] = new google.maps.Marker({
            position: locations[ key ],
            map: map,
            title: point[ 'name' ]
        });

        var infoWindowHTML = info_window_html(point);
        infoWindows[ key ] = {}
        infoWindows[ key ] = new google.maps.InfoWindow({
            content: infoWindowHTML
        });

        google.maps.event.addListener(markers[ key ], 'click', function () {
            if (currentInfoWindow) {
                currentInfoWindow.close();
            }
            currentInfoWindow = infoWindows[ key ];
            currentInfoWindow.open(map, markers[ key ]);
            // @TODO add a class to the 'key' element so that css can highlight it or do other things
        });


    });

    $('.map-point').each(function () {
        google.maps.event.addDomListener(this, 'click', function () {
            map.panTo(locations[ this.attributes[ "name" ].value ]);
            map.setZoom(15);
            if (currentInfoWindow) {
                currentInfoWindow.close();
            }
            currentInfoWindow = infoWindows[ this.attributes[ "name" ].value ];
            currentInfoWindow.open(map, markers[ this.attributes[ "name" ].value ]);
        });
    });
}

/**
 * builds an HTML string with divs for each element. will be displayed on the map when the user clicks a point
 * @param location_object
 */
function info_window_html(location_object) {
    var return_string = '';
    return_string += '<div class="info_window">';
    return_string += info_window_html_div_if_exists(location_object, 'name');
    return_string += nl2br(info_window_html_div_if_exists(location_object, 'description'));
    return_string += nl2br(info_window_html_div_if_exists(location_object, 'address'));
    return_string += nl2br(info_window_html_div_if_exists(location_object, 'hours_of_operation'));
    return_string += nl2br(info_window_html_div_if_exists(location_object, 'phone_number'));
    return_string += info_window_html_div_if_exists(location_object, 'url');
    return_string += '</div>';
    return return_string;
}

/**
 * checks the object key to see if a value is set. if so, it returns a div with the proper class and value. if not, returns an empty string.
 * @param location_object Array such as 'lake_nona' with key->value objects in the array.
 * @param location_object_property_key Ex address, description, name, etc
 * @returns {string}
 */
function info_window_html_div_if_exists(location_object, location_object_property_key ){
    if (location_object[location_object_property_key]) {
        return '<div class="' + location_object_property_key + '">' + location_object[location_object_property_key] + '</div>'
    } else {
        return '';
    }
}

function nl2br (str, is_xhtml) {
    var breakTag = (is_xhtml || typeof is_xhtml === 'undefined') ? '<br />' : '<br>';
    return (str + '').replace(/([^>\r\n]?)(\r\n|\n\r|\r|\n)/g, '$1'+ breakTag +'$2');
}

function setup_location_extended_details(){

}

jQuery(document).ready(function () {
    setup_google_map();
});