var $win
    ,$container
    ,$slideshow
    ,$cronoDia
    ,$cronoDescricao
    ,currDia
    ,currDescricao
    ,sizeBaseWin = 750
    ,sizeBaseContainer = 550
    ,doResize = true
    ;

jQuery.fn.ready(function(){

    // jQuery as $
    (function($){

        $("body").css("display", "none");

        $("body").fadeIn(500);

        $(".menu li a").click(function(event){
            event.preventDefault();
            linkLocation = this.href;
            $("body").fadeOut(300, redirectPage);      
        });
         
        function redirectPage() {
            window.location = linkLocation;
        }

        // PlaceHolder
        $('input, textarea').placeholder();

        // Custom Form
        $('select.Styled').customized();

        // RF Slider
        $slideshow = $('#slideshow');
        if($slideshow.length > 0) {
            $slideshow.slideshownav({
                transition: 'push(#{direction})',
                mode: 'horizontal',
                navSelector: '> ul > li > a',
                duration: 400,
                autoPlay: false
            });
        }

        var $rfSlider = $('div.rfSlider');
        var slideshow = $slideshow.data('slideshownav');

        $rfSlider.find('a.prev').on('click',function(ev){
            ev.preventDefault();
            slideshow.show('previous',{ transition:'push(right), swing' });
        });

        $rfSlider.find('a.next').on('click',function(ev){
            ev.preventDefault();
            slideshow.show('next',{ transition:'push(left), swing' });
        });

        // Scroll
        $win = $(window);
        /*
        $container = $('#container');

        $win.load(function(){
            $container.mCustomScrollbar({
                scrollButtons: {
                    enable: false
                }
                // ,autoHideScrollbar: true
                ,theme: "dark"
            })
            .find('.mCSB_scrollTools').addClass('invisible');

            $('#up').on('click',function(ev){
                ev.preventDefault();
                scrolla("up");
            })
            $('#down').on('click',function(ev){
                ev.preventDefault();
                scrolla("down");
            })
        });
        */

        // Resize
        $win.on('resize',function(){
            resizeBox();
        })
        .trigger('resize');

        // Show Plus
        $('a.showPlus').click(function(ev){
            ev.preventDefault();
            $(this).remove();
            $('#plus').removeClass('hidden');
            $('.navegacao').removeClass('hidden');
            $container.mCustomScrollbar("update");
            //$('.container').addClass('boxWhite')
            //$rfSlider.fadeOut(200);
            //$('.home.bt-inscreva-se').fadeOut(200); 
        });

        // Cronograma
        $cronoDia = $('#showDia');
        $cronoDescricao = $('#showDescricao');
        currDia = $cronoDia.text();
        currDescricao = $cronoDescricao.text();

        var tds = $('.box-cronograma table.tbl td').not('.disable');
        tds.on('click',function(){
            var bg1 = $(this).css('backgroundColor');
            if (bg1 != 'transparent' && bg1 != 'rgba(0, 0, 0, 0)') {
                $('#crono-principal').css('backgroundColor', bg1);
            }
            currDia = $(this).data('dia');
            currDescricao = $(this).data('descricao');
            $cronoDia.html(currDia);
            $cronoDescricao.text(currDescricao);
        });

        tds.on('mouseenter',function(){
            var bg1 = $(this).css('backgroundColor');
            if (bg1 != 'transparent' && bg1 != 'rgba(0, 0, 0, 0)') {
                $('#crono-principal').css('backgroundColor', bg1);
            }
            $cronoDia.html($(this).data('dia'));
            $cronoDescricao.text($(this).data('descricao'));
        });
        tds.on('mouseleave',function(){
            $cronoDia.text(currDia);
            $cronoDescricao.text(currDescricao);
        });
/*
        $("#frmFale").validate({
            errorElement: "em",
            errorContainer: $("#warning")
        });*/

        $tableDatas = $('.crono-date').not('.disable')
        var id = 0
        $('.crono-nav').on('click', function(){
            if( $(this).attr('id') === 'crono-prev' ){
                id--
                if( id < 0 ){
                    id = $tableDatas.size()-1
                }
            }else{
                id++
                if( id  == $tableDatas.size() ){
                    id = 0
                }
            }
            $nova = $tableDatas.eq(id)
            $('#crono-principal').css( { 'background' : $nova.css('background') } )
            $('#showDia').html( $nova.data('dia') )
            $('#showDescricao').text( $nova.data('descricao') )
            
        })

    })(jQuery);

});

function scrolla(which)
{
    if($container.find(".mCSB_scrollTools").css('display') == 'block')
    {
        var activeElemPos=Math.abs($container.find(".mCSB_container").position().top)
            ,pixelsToScroll=60;

        if(which==="up")
        {
            if(pixelsToScroll>activeElemPos)
                $container.mCustomScrollbar("scrollTo","top");
            else
                $container.mCustomScrollbar("scrollTo",(activeElemPos-pixelsToScroll),{scrollInertia:400,scrollEasing:Power2.easeOut});
        }
        else if(which==="down")
            $container.mCustomScrollbar("scrollTo",(activeElemPos+pixelsToScroll),{scrollInertia:400,scrollEasing:Power2.easeOut});
    }
}

function resizeBox()
{
    // if(doResize)
    // {
    //     if ($win.height() <= sizeBaseWin )
    //         $container.css({ "height": calculo() });
    //     else
    //         $container.css({ "height": sizeBaseContainer - 100 });
    // }
    // $container
    // .mCustomScrollbar("update");
}

function calculo()
{
    var h = ((sizeBaseContainer * $win.height()) / sizeBaseWin) - 70;
    h = (h <= 300) ? 300 : h;
    return parseInt(h);
}