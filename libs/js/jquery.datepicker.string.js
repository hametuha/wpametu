/**
 * jQuery datepicker string mapper
 *
 * @author Takahashi Fumiki
 * @package WPametu
 */


/**
 * Datepicker string
 *
 * @lends {Window}
 * @type {Object}
 */
var jQueryDatePickerString = window.jQueryDatePickerString || {};

/**
 * Returns jQuery Datepicker ready object
 *
 * @param {Object} obj
 * @return {Object}
 */
jQueryDatePickerString.map = function(obj){
    var newObj = {};
    for(var prop in obj){
        newObj[prop] = obj[prop];
    }
    for(prop in this){
        if( 'function' !== typeof this[prop] && !newObj[prop] ){
            newObj[prop] = this[prop];
        }
    }
    return newObj;
};
