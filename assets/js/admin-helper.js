/**
 * Description
 */

/* global wpametuAdminHelper:true */

(function ($) {
    'use strict';

    //
    // Global funcitons
    //
    window.WPametu = {
        alert: function(msg){
            var dialog = $('<div id="wpametu-alert"></div>');
            dialog.html('<p>' + msg + '</p>');
            dialog.dialog({
                title: wpametuAdminHelper.error,
                resizable: false,
                modal: true,
                buttons: [
                    {
                        text: wpametuAdminHelper.close,
                        click: function() {
                            $(this).dialog( "close" );
                            $(this).remove();
                        }
                    }
                ]
            });
        }
    };

    //
    // Tooltips
    //
    $(document).ready(function(){
        $(document).tooltip({
            items: 'a[data-tooltip-title]',
            content: function(){
                return $(this).attr('data-tooltip-title');
            },
            track: true
        });
    });


})(jQuery);
