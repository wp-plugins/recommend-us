jQuery(document).ready(function ($) {    
    if (jQuery('.msbd-postbox-container').length > 0)
        jQuery('body').addClass('msbd-rcmnd-admin-page');
        
    jQuery(".msbd-postbox-container .handlediv, .msbd-postbox-container .hndle").on("click",function(e){
        e.preventDefault();
        jQuery(this).parent().toggleClass("closed");
    });   
});
