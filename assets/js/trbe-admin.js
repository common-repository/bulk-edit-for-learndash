const trbeAjaxUrl = trbe_js_object.ajaxurl;
const trbeWpnonce = trbe_js_object._wpnonce;
const trbeBulkEditAction = 'trbe_ld_bulk_edit';
const trbeCourseOptionsAction = 'trbe_ld_course_options';
jQuery(document).ready(function ($) {

    //on submit form, prevent default
    $('form#trbe-bulk-edit-form').submit(function (e) {
        e.preventDefault();
        let price = $('#trbe_courses_price').val();
        let courses_selected = $('#trbe_courses_select').val();
        let update_woocommerce = $('#trbe_update_woocommerce').length && $('#trbe_update_woocommerce').is(":checked");
        update_woocommerce = update_woocommerce ? 1 : 0;
        // console.log(update_woocommerce); return false;
        let select_options, courses_array;
        //if -1 is in courses_selected, then all courses are selected

        if (courses_selected.indexOf('-1') >= 0) {
            select_options = $('#trbe_courses_select option');
            courses_array = [];
            $.each(select_options, function (index, op) {
                value = $(op).val();
                if (value !== '-1') {
                    courses_array.push(value);    
                }
            });
            courses_selected = courses_array; 
        }        

        $.ajax({
            url: trbeAjaxUrl,
            type: 'post',
            dataType: 'json',
            data: {
                'action': trbeBulkEditAction,
                '_wpnonce': trbeWpnonce,
                'price': price,
                'courses_selected': courses_selected,
                'update_woocommerce': update_woocommerce
            },
            success: function (response) {
                if (response.status !== 'success') {
                    alert(response.message);
                } else {
                    alert(response.message);
                } //end if/else success
            } //end success callback
        }); //end ajax call
    }); //end submit form

    //on change select
    $('#trbe_category_select').change(function () {
        $('#trbe_courses_select').empty();
        //post request to populate courses select
        $.ajax({
            url: trbeAjaxUrl,
            type: 'post',
            // dataType: 'json',
            data: {
                'action': trbeCourseOptionsAction,
                '_wpnonce': trbeWpnonce,
                'category_id': $(this).val()
            },
            success: function (response) {
                // console.log(response);
                if(response){
                    //if 'Error' string exists in response
                    if(response.indexOf('Error') >= 0) { 
                        console.log(response);
                        return false;
                    }
                    $('#trbe_courses_select').html(response);
                } //end if/else success
            } //end success callback    
        }); //end ajax call
    }); //end change select


}); //end jquery