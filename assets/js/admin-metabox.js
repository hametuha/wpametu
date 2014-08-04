/**
 * Description
 */

/*global google: true*/

(function ($) {
    'use strict';

    $(document).ready(function(){

        // Char counter
        $('.char-counter').each(function(index, p){
            var input = $(p).prev('input, textarea');
            input.keyup(function(){
                var len = $(this).val().length,
                    max = parseInt($(this).attr('data-max-length')),
                    min = parseInt($(this).attr('data-min-length')),
                    flg = true;
                $(p).find('strong').text(len);
                if( min && len < min ){
                    flg = false;
                }
                if( max && len > max ){
                    flg = false;
                }
                if( flg ){
                    $(p).removeClass('ng').addClass('ok');
                }else{
                    $(p).removeClass('ok').addClass('ng');
                }
            });
        });

        // Google map
        $('.geo-row').each(function(index, row){
            var input = $(row).find('input[type=hidden]'),
                mapCanvas = $(row).find('.wpametu-map'),
                geoCoder = $(row).find('.gmap-geocoder'),
                map, center, zoom, point, marker, geocoder, target, sync;
            if( input.length && mapCanvas.length ){
                zoom = parseInt(input.attr('data-zoom'), 10);
                point = input.val().split(',');
                // Check this map's role
                target = $('#' + input.attr('data-target'));
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
                    draggable: !input.attr('data-no-drag'),
                    map: map,
                    position: center
                });
                // Check GeoCoder
                geocoder = new google.maps.Geocoder();
                if( !target.length ){
                    // This is normal map
                    google.maps.event.addListener(marker, 'position_changed', function(e){
                        if( !mapCanvas.hasClass('original') ){
                            // Sync input value
                            input.val(this.position.lat() + ',' + this.position.lng());
                        }else{
                            // Trigger event
                            mapCanvas.trigger('move.gmap', [marker, map, input]);
                        }
                    });
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
                                    WPametu.alert(msg);
                                }
                            });
                        }
                    });
                }else{
                    // This is watcher
                    sync = function(){
                        var address = target.val(),
                            icon = $('<i class="dashicons dashicons-update wpametu-spinner"></i>');
                        target.after(icon);
                        if( address.length ){
                            geocoder.geocode( {
                                'address': address
                            }, function( results, status ) {
                                if (status == google.maps.GeocoderStatus.OK) {
                                    map.setCenter(results[0].geometry.location);
                                    marker.setPosition(results[0].geometry.location);
                                    mapCanvas.addClass('ok');
                                } else {
                                    mapCanvas.removeClass('ok');
                                }
                                icon.remove();
                            });
                        }
                    };
                    sync();
                    // setTimeout
                    var timer = [];
                    target.focus(function(){
                        timer.push(setInterval(sync, 3000));
                    });
                    target.blur(function(){
                        sync();
                        $.each(timer, function(index, id){
                            clearInterval(id);
                        });
                        timer = [];
                    });
                }
            }
        });

    });

})(jQuery);
