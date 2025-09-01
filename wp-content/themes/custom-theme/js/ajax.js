jQuery(document).ready(function ($) {

    var page_number = 2;
    jQuery(document).on('click', "#my-ajax-button", function (e) {
        e.preventDefault();

        var posts_per_page = $(this).attr("posts_per_page");
        var myButton = $("#my-ajax-button")


        $.ajax({
            url: ajax_object.ajax_url,
            type: 'POST',
            data: {
                action: 'my_custom_ajax_action',
                posts_per_page: posts_per_page,
                page_number: page_number
            },
            beforeSend: function () {
                myButton.html('Loading...');
            },
            success: function (response) {
                if (response.success) {
                    myButton.html('View More!');
                    $('.main_content').append(response.data.html);
                    page_number++;
                    console.log(response.data.more);
                    console.log(page_number);
                    if (!response.data.more) {
                        myButton.hide();
                    }
                }
            },
            error: function () {
                $('#main_content').append('Something went wrong!');
            }
        });


    });
});