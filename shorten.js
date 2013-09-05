$(document).ready(function(){

    /* Search */
    $('#search').keyup(function() {
        $searchString = $(this).val();
        $('section').each(function() {
            if($(this).attr('id').toLowerCase().indexOf($searchString.toLowerCase()) >= 0)
                $(this).show(500);
            else
                $(this).hide(500);
        });
    });

    /* Copy to Clipboard using Jquery ZClip plugin; see http://www.steamdev.com/zclip/ */
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
