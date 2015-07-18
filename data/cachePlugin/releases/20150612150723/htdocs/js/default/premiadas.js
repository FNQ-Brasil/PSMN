var cyclesModule = (function() {
    functionName = function($param) {
        return $param;
    }

    return {
        init: function() {
            // Retira a margin-left da primeira foto de cada linha
            $( ".page-ciclos ul.photos li:nth-child(7n+1)").css("margin-left", "0");

            // troca de vencedora para fotos (CICLO)
            $(".ver-fotos").click(function(event){
                var $year_content = $(event.currentTarget).parents('.content:first');
                $year_content.find(".wrapper-list").hide("slide", { direction: "left" }, 500);
                $year_content.find(".wrapper-photos").show("slide", { direction: "right" }, 500);
                return false;
            });

            // troca de fotos para vencedora (CICLO)
            $(".ver-vencedora").click(function(event){
                var $year_content = $(event.currentTarget).parents('.content:first');
                $year_content.find(".wrapper-photos").hide("slide", { direction: "right" }, 500);
                $year_content.find(".wrapper-list").show("slide", { direction: "left" }, 500);
                return false;
            });

            var fancybox_common_options = {
                wrapCSS    : 'fancybox-custom',
                closeClick : true,

                openEffect : 'elastic',
                openSpeed  : 150,

                closeEffect : 'elastic',
                closeSpeed  : 150,

                prevEffect : 'elastic',
                nextEffect : 'elastic',

                arrows    : true,
                nextClick : true,

                helpers : {
                    title : {
                        type : 'inside'
                    },
                    overlay : {
                        css : {
                            'background' : 'rgba(0,0,0,0.85)'
                        }
                    }
                }
            };

            var previous_cycles_gallery_options = jQuery.extend(true, {}, fancybox_common_options);

            previous_cycles_gallery_options.afterLoad = function() {
                this.title = 'Imagem: ' + (this.index + 1) + ' de ' + this.group.length + (this.title ? ' - ' + this.title : '');
            };

            $("[data-previous-cycles-gallery]").fancybox(previous_cycles_gallery_options);

            $('.wrapper-list [data-report-id]').each(function(index,report_link){
                var $report_link = $(report_link);
                var report_id = $report_link.data('report-id');
                var content = $('.wrapper-reports ul.reports li[data-report-id='+report_id+']').html();
                var report_fancybox_options = jQuery.extend(true, {}, fancybox_common_options);
                report_fancybox_options.content = content;
                report_fancybox_options.maxWidth = 800;
                $report_link.fancybox(report_fancybox_options);
            });

            //Hover imagens
            $('ul.photos li a').hover(
                function() {
                    $(this).prepend('<span class="hover-ciclo"></span>');
                },function() {
                    $(this).children('span.hover-ciclo').remove();
                }
            );


            // Ao clicar no ano da tab, mostra a div do ano selecionado.
            $(".tab-years .year").click(function(event){
                var $current_li = $('.tab-years li.current');
                var current_year = $current_li.find('a').data('year');
                var $selected_li = $(event.currentTarget).parent();
                var selected_year = $selected_li.find('a').data('year');

                $current_li.removeClass('current');
                $selected_li.addClass('current');

                $('#year'+current_year).hide();
                $('#year'+selected_year).show();
            });

            return this;
        }
    };

}());

$(function() {
    try {
        cyclesModule.init();
    } catch (e) {
        if (APPLICATION_ENV != 'development') {
            console.log(e);
            //document.write ("Outer catch caught <br/>");
            Sescoop.error(e.message);
            return;
        }
        throw e;
    }
});