jQuery(document).ready(function( $ ) {
        jQuery("#ordin_submit").click(function(event) {
            event.preventDefault();
            fullname = jQuery("#ordin_name").val();
            profile = jQuery("#ordin_profile").val();

            var data = {
                action: 'ordin_find',
                fullname: fullname,
                profile: profile
            }

            jQuery.post( myajax.url, data, function(response) {
                jQuery(".ordin_output").html(response);
            });
        });
});