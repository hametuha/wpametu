/**
 * Description
 */

(function ($) {
    'use strict';


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

})(jQuery);
