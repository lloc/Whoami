var paircount = 0;
jQuery( function($) {
    $( '.socialicons img' ).each( function() {
        var $newthis = $( this );
        $newthis.addClass( 'pair_' + ++paircount );
        var $cloned = $newthis.clone().attr( 'id', '' );
        $cloned.insertAfter($newthis).addClass( 'color' ).hide();
        $newthis.desaturate( { onswitch: function() { } } );
        $cloned.closest( '.switched_images' ).bind( 'mouseenter mouseleave', desevent );
    });
});
var desevent = function( event ) {
    var classString = new String( $( this ).attr( 'class' ) );
    var pair        = classString.match(/pair_\d+/);
    if ( event.type == 'mouseenter')
        $( '.des.color', this ).fadeIn( 500 );
    if ( event.type == 'mouseleave')
        $( '.des.color', this ).fadeOut( 500 );
}
