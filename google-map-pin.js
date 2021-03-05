// <script src="https://maps.googleapis.com/maps/api/js?key=YOURAPIKEY&callback=initMap" defer></script>

var units = {!! json_encode($units) !!};
var activeUnit = {!! user()->unit ? user()->unit->id : '{}' !!};
var geocoder;
var map;
var markers = [];
var iterator = 0;
var allowedBounds = null;

toastr.options = {
    closeButton: true,
    extendedTimeOut: 0,
    timeOut: 0
};

let zoom = localStorage.getItem('map_zoom');

if (zoom && (Number.isInteger(zoom) || !isNaN(zoom))) {
    zoom = parseFloat(zoom);
} else {
    zoom = 3;
}

let center = localStorage.getItem('map_center');

if (!center) {
    center = {lat: 38.998337, lng: -101.276247};
} else {
    center = JSON.parse(center);
    if (!center.lat || !center.lng || isNaN(center.lat) || isNaN(center.lng)) {
        center = {lat: 38.998337, lng: -101.276247};
        zoom = 3;
    }
}

function initMap() {
    geocoder = new google.maps.Geocoder();
    map = new google.maps.Map(document.getElementById('google-map'), {
        zoom: zoom,
        minZoom: 3,
        center: center
    });
    window.google.maps.event.addListener(map, 'zoom_changed', function (event) {
        localStorage.setItem('map_zoom', map.getZoom());
    });
    window.google.maps.event.addListenerOnce(map,'idle',function() {
        allowedBounds = map.getBounds();
    });
    window.google.maps.event.addListener(map,'drag',function() {
        checkBounds();
    });
    window.google.maps.event.addListener(map,'tilesloaded',function() {
        checkBounds();
    });

    function checkBounds() {
        if(! allowedBounds.contains(map.getCenter()))
        {
            var C = map.getCenter();
            var X = C.lng();
            var Y = C.lat();
            var AmaxX = allowedBounds.getNorthEast().lng();
            var AmaxY = allowedBounds.getNorthEast().lat() - 20;
            var AminX = allowedBounds.getSouthWest().lng();
            var AminY = allowedBounds.getSouthWest().lat();
            console.log(AmaxX, AmaxY);
            if (X < AminX) {X = AminX;}
            if (X > AmaxX) {X = AmaxX;}
            if (Y < AminY) {Y = AminY;}
            if (Y > AmaxY) {Y = AmaxY;}
            map.panTo(new google.maps.LatLng(center.lat, center.lng));
        }
    }

    units.forEach((u) => {
        codeAddress(u);
    });
}

function createPin(color) {
    return {
        path: 'M 0,0 C -2,-20 -10,-22 -10,-30 A 10,10 0 1,1 10,-30 C 10,-22 2,-20 0,0 z',
        fillColor: color,
        fillOpacity: 1,
        strokeColor: '#000',
        strokeWeight: 1,
        scale: 1,
        labelOrigin: new google.maps.Point(0, -30)
    };
}

function updateUnit(id) {
    localStorage.setItem('map_zoom', map.getZoom());
    localStorage.setItem('map_center', JSON.stringify(center, null, 2));
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': "<?php echo csrf_token() ?>"
        }
    });
    $.ajax({
        type: 'GET',
        url: `updateUnit/${id}`,
        success: function (data) {
            location.reload();
        }, error: function (data) {
            console.log(data);
        }
    });
}

function codeAddress(u) {
    let latLng = JSON.parse(u.latlng);
    let icon = u.getMarkerIcon;
    let marker = new google.maps.Marker({
        position: latLng,
        map,
        icon,
        title: u.getFullName,
        label: u.isSelected ? {
            text: ' ',
            className: 'pulse'
        } : '',
    });
    marker.setValues({id: u.id});
    markers.push(marker);
    window.google.maps.event.addListener(marker, 'click', function (event) {
        center = event.latLng.toJSON();
        updateUnit(u.id);
    });
}

function setMapMarkerLocation(event, elem, type, url) {
    event.preventDefault();
    if (needConfirmToGo === true) {
        localStorage.removeItem('map_center');
        let urlToRedirect = $(elem).attr('href');
        if (type === 'unit') {
            $.ajax({
                type: "GET",
                url: url,
                success: function (response) {
                    let geoCoder = new google.maps.Geocoder();
                    geoCoder.geocode({'address': response.location}, function (results, status) {
                        if (results && results[0]) {
                            let latLng = {
                                lat: results[0].geometry.location.lat(),
                                lng: results[0].geometry.location.lng()
                            };
                            localStorage.setItem('map_center', JSON.stringify(latLng, null, 2));
                        } else {
                            alert('Geocode was not successful for the following reason: ' + status);
                        }
                    });
                    window.location.href = urlToRedirect;
                }
            });
        }
        if (type === 'organization') {
            localStorage.removeItem('map_zoom');
            window.location.href = urlToRedirect;
        }
    }
}