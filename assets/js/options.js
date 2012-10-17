/**
 * Created with JetBrains PhpStorm.
 * User: Only For Me
 * Date: 10/8/12
 * Time: 11:14 PM
 * To change this template use File | Settings | File Templates.
 */


(function($){

    $(document).ready(function(){
        $('.custom-filter-post-types').change(function(){
            var chk_box=$(this);
            var tr='#tr-post-type-'+chk_box[0].value;
            if(chk_box.prop("checked")){
                $(tr).show();
                $(tr+' input').removeAttr('disabled');
            }else{
                $(tr).hide();
                $(tr+' input').attr('disabled','disabled');
            }
        }).change();

        $(".post-filter-options").filter('[value="taxonomy"]').change(function(){
            var chk_box = $(this);
            var tr = '#' + chk_box.closest('tr').attr('id') + '-taxonomy';
            if(chk_box.prop("checked")){
                $(tr).show();
                $(tr+' input').removeAttr('disabled');
            }else{
                $(tr).hide();
                $(tr+' input').attr('disabled','disabled');
            }
        }).change();

        $(".filter-style-selector-1").change(function(){
                $("#" + $(this).attr('rel')).hide();
        });
        $(".filter-style-selector-2").change(function(){
            $("#" + $(this).attr('rel')).show();
        });
    });
})(jQuery);