jQuery(function(){

    var fselector = '';
    
    jQuery('.label_star').hover(function() {
        fselector = "#"+jQuery(this).closest('form').attr('id');
        renderStarRating(parseInt(jQuery(this).attr('id').charAt(11)), fselector);
    }, function() {
        renderStarRating(parseInt(jQuery(fselector+' #rRating').val()), fselector);
    });

    jQuery('.label_star').click(function() {
        jQuery(fselector+' #rRating').val(jQuery(this).attr('id').charAt(11));
    });

});

function renderStarRating(rating, fselector) {
    for (var i=1; i<=5; i++) {
        
        jQuery(fselector+' #label_star_'+i).removeClass('glyphicon-star');
        jQuery(fselector+' #label_star_'+i).removeClass('glyphicon-star-empty');
        
        if (i<=rating) {
            jQuery(fselector+' #label_star_'+i).addClass('glyphicon-star');
        } else {
            jQuery(fselector+' #label_star_'+i).addClass('glyphicon-star-empty');
        }
    }
}
