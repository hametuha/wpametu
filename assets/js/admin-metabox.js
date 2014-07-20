/**
 * Description
 */

/*global google: true*/

(function ($) {
    'use strict';

    $(document).ready(function(){

        // Google map
        $('.geo-row').each(function(index, row){
            var input = $(row).find('input[type=hidden]'),
                mapCanvas = $(row).find('.wpametu-map'),
                geoCoder = $(row).find('.gmap-geocoder'),
                map, center, zoom, point, marker, geocoder;
            if( input.length && mapCanvas.length ){
                zoom = parseInt(input.attr('data-zoom'), 10);
                point = input.val().split(',');
                if( point.length !== 2 ){
                    point = [input.attr('data-lat'), input.attr('data-lng')];
                }
                center = new google.maps.LatLng(point[0], point[1]);
                map = new google.maps.Map(mapCanvas.get(0), {
                    center: center,
                    zoom: zoom,
                    mapTypeId: google.maps.MapTypeId.ROADMAP
                });
                // Show map marker
                marker = new google.maps.Marker({
                    draggable: true,
                    map: map,
                    position: center
                });
                google.maps.event.addListener(marker, 'position_changed', function(e){
                    if( !mapCanvas.hasClass('original') ){
                        // Sync input value
                        input.val(this.position.lat() + ',' + this.position.lng());
                    }else{
                        // Trigger event
                        mapCanvas.trigger('move.gmap', [marker, map, input]);
                    }
                });
                // Check GeoCoder
                geocoder = new google.maps.Geocoder();
                $(row).on('click', '.gmap-geocoder-btn', function(e){
                    e.preventDefault();
                    var address = $(this).prev('input').val(),
                        msg = $(this).attr('data-failure');
                    if( address.length ){
                        geocoder.geocode( {
                            'address': address
                        }, function( results, status ) {
                            if (status == google.maps.GeocoderStatus.OK) {
                                map.setCenter(results[0].geometry.location);
                                marker.setPosition(results[0].geometry.location);
                            } else {
                                alert(msg);
                            }
                        });
                    }
                });
            }
        });

    });

})(jQuery);
