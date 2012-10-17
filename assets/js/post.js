/**
 * Created with JetBrains PhpStorm.
 * User: Only For Me
 * Date: 10/8/12
 * Time: 11:14 PM
 * To change this template use File | Settings | File Templates.
 */
function parseJSON(response){
    try{
        if(response){
            return eval("(" + response + ")");
        }else{
            return {};
        }
    }catch(e){
        return response;
    }
}

(function($){
    var settings = {};
    var filter_div;

    function getSettings(){
        var settings_span = $("#ex-filter-setting-input");
        filter_div = settings_span.closest('div');
        settings = parseJSON(settings_span.html());

        settings_span.remove();
    }

    function handleDefaultInputs(){
        var month = filter_div.find('select[name="m"]');
        if(isFilterActive('month')){
            month.show();
        }else{
            month.remove();
        }

        var cat = filter_div.find('select[name="cat"]');
        if(isFilterActive('category')){
            cat.show();
        }else{
            cat.remove();
        }
    }

    function isFilterActive(filter){
        return settings.config.indexOf(filter) > -1
    }

    function handleDateInput(){
        if(!isFilterActive('date_range')){
            return;
        }
        $( ".date-picker-input" ).datepicker({dateFormat : 'yy-mm-dd'});
    }

    $(document).ready(function(){
        getSettings();
        handleDefaultInputs();
        handleDateInput();
        if(settings.config.length > 0){
            $('.ex-filter, #post-query-submit').show()
        }
    });
})(jQuery);


