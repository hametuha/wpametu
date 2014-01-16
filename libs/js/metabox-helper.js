/**
 * Metabox helper
 *
 * @author Takahashi Fumiki
 */


jQuery(document).ready(function($){

    /**
     * Get table row
     *
     */
    var getRow = function(target){
            return $(target).parents('tr');
        },
        showError = function(msg, target){
            getRow(target).effect('highlight', {}, 1000).
                find('p.validator').text(msg).css('display', 'block').delay(5000).fadeOut(1000, function(){
                    $(this).text('');
                });

        };



    $('.wpametu-metabox-table input[type=text]').each(function(index, elt){
        $(elt).blur(function(){
            if( !$(this).hasClass('datetime-picker') && $(this).hasClass('required') && !$(this).val() ){
                showError(MetaboxHelper.required, this);
            }
        });
    });

    $('input.datetime-picker').datetimepicker(jQueryDatePickerString.map({
        dateFormat: 'yy-mm-dd',
        timeFormat: 'HH:mm:00',
        changeYear: true,
        onClose: function(){

        }
    }));
});
