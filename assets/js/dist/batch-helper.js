!function(e){"use strict";var t;t={$form:e("#batch-form"),$btn:e("input[type=submit]","#batch-form"),$pre:e(".console","#batch-result"),reset:function(){this.$btn.attr("disabled",!1),this.$form.removeClass("loading"),e("#page_num").val("1")},execute:function(){t.$form.addClass("loading"),t.$btn.attr("disabled",!0),t.$pre.empty(),t.console("Start Processing..."),t.ajax()},ajax:function(){this.$form.ajaxSubmit({success:function(a){if(a.success)if(t.console(a.message),a.next){var r=e("#page_num");r.val(parseInt(r.val(),10)+1),t.ajax()}else t.console(WpametuBatch.done,"success"),t.reset();else t.addError(a.message),t.reset()},error:function(e,a,r){t.addError(r),t.reset()}})},console:function(t,a){var r=e("<p></p>");r.text(t),a&&r.addClass(a),this.$pre.append(r)},addError:function(e){this.console("[Error] "+e,"error")},setPage:function(t){e("#page_num").val(t)}},t.$form.submit(function(a){a.preventDefault(),e(this).find("input[name=batch_class]:checked").length?confirm(WpametuBatch.confirm)&&t.execute():t.addError(WpametuBatch.alert)})}(jQuery);
//# sourceMappingURL=map/batch-helper.js.map