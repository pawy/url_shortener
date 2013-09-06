$(document).ready(function(){

    // Search
    $('#search').keyup(function() {
        $searchString = $(this).val();
        $('section').each(function() {
            if($(this).attr('id').toLowerCase().indexOf($searchString.toLowerCase()) >= 0)
                $(this).show(500);
            else
                $(this).hide(500);
        });
        setTimeout(function(){
            setZclip();
        }, 1000 );
    });

    // Catch toggle events from Bootstrap to reposition the zclip flash
    $('.statistics').on('shown.bs.collapse', function () {
        setTimeout(function(){
            setZclip();
        }, 500 );
    });
    $('.statistics').on('hidden.bs.collapse', function () {
        setTimeout(function(){
            setZclip();
        }, 500 );
    });

    // Copy to Clipboard using Jquery ZClip plugin; see http://www.steamdev.com/zclip/
    $('input.shorten').zclip({
        setHandCursor: false,
        path:'http://davidwalsh.name/demo/ZeroClipboard.swf',
        copy:function(){return $(this).val();},
        afterCopy:function(){
            $this = $(this);
            $before = $(this).val();
            $(this).val('Copied to Clipboard');
            setTimeout(function() {
                $this.val($before);
            },1000);
            $(this).animate({backgroundColor:'red'},500).animate({backgroundColor:'white'},500);
        }
    });
});

// Reposition the zClip's Flash to catch the click
function setZclip()
{
    $('input.shorten').zclip('remove');
    $('input.shorten').zclip({
        setHandCursor: false,
        path:'http://davidwalsh.name/demo/ZeroClipboard.swf',
        copy:function(){return $(this).val();},
        //the triggered function fires allthoug we removed the zClip, therefore we do not need to set it again
        afterCopy:function(){}
    });
}
