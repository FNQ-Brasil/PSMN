var reportModule = (function () {
    
    var $frm; //, $divError, $divErrorMsg; 9,8188-9951
    
    init = function(){


        if ($('#radarChart').length) {
            var DATA_RADAR_GERAL = $('#reportCompare').val() == '1'? [
                38.52,27.92,38.33,39.69,34.21,37.2,41.93,13.28
            ] : [0, 0, 0, 0, 0 , 0 , 0 , 0];
            var that = this;
            var radarChartData = {
                labels : ["Liderança","Estratégias","Clientes","Sociedade",
                    "Informações","Pessoas","Processos","Resultados"],
                datasets : [
                    {
                        fillColor : "rgba(220,220,220,0.70)",
                        strokeColor : "rgba(210,210,210,1)",
                        pointColor : "rgba(205,205,205,1)",
                        pointStrokeColor : "#fff",
                        data : DATA_RADAR_GERAL
                    },
                    {
                        fillColor : "rgba(152,186,200,0.45)",
                        strokeColor : "rgba(152,186,200,0.95)",
                        pointColor : "rgba(152,186,200,0.95)",
                        pointStrokeColor : "#fff",
                        data : DATA_RADAR
                    }
                ]
            };

            var globalGraphSettings = {
                animation : Modernizr.canvas,
                scaleLineWidth : 1,
                scaleShowLabels : true,
                scaleFontSize : 11,
                scaleFontColor : "#999",
                scaleSteps : 5,
                scaleStepWidth : 20,
                scaleStartValue : 0,
                scaleOverride  : true

            };
            function showRadarChart(){
                var ctx = document.getElementById("radarChartCanvas").getContext("2d");
                new Chart(ctx).Radar(radarChartData,globalGraphSettings);
            }
            var graphInitDelay = 250;
            $("#radarChart").on("inview",function(){
                var $this = $(this);
                $this.removeClass("hidden").off("inview");
                setTimeout(showRadarChart,graphInitDelay);			
            });
        }
        
        if ($('#areaChartCategoria').length) {
            var inscritasData = [
				{
                    label: 'Agro. ' + INSCRITASPORCEN[0] + '%',
					value: INSCRITAS[0],
					color:"#F7464A",
                    labelColor : '#fff'
				},
				{
                    label: 'Educação ' + INSCRITASPORCEN[1] + '%',
					value : INSCRITAS[1],
					color : "#46BFBD",
                    labelColor : '#fff'
				},
				{
                    label: 'Comércio ' + INSCRITASPORCEN[2] + '%',
					value : INSCRITAS[2],
					color : "#FDB45C",
                    labelColor : '#fff'
				},
				{
                    label: 'Indústria ' + INSCRITASPORCEN[3] + '%',
					value : INSCRITAS[3],
					color : "#949FB1",
                    labelColor : '#fff'
				},
				{
                    label: 'Serviços ' + INSCRITASPORCEN[4] + '%',
					value : INSCRITAS[4],
					color : "#4D5360",
                    labelColor : '#fff'
				},
                {
                    label: 'Saúde ' + INSCRITASPORCEN[5] + '%',
					value : INSCRITAS[5],
					color : "#4D52FF",
                    labelColor : '#fff'
				},
				{
                    label: 'T.I. ' + INSCRITASPORCEN[6] + '%',
					value : INSCRITAS[6],
					color : "#3D43cc",
                    labelColor : '#fff'
				},
				{
                    label: 'Turismo ' + INSCRITASPORCEN[7] + '%',
					value : INSCRITAS[7],
					color : "#ff5390",
                    labelColor : '#fff'
				}
		];
        
        var candidatasData = [
            {
                label: 'Agro. ' + CANDIDATASPORCEN[0] + '%',
                value: CANDIDATAS[0],
                color:"#F7464A",
                labelColor : '#fff'
            },
            {
                label: 'Educação ' + CANDIDATASPORCEN[1] + '%',
                value : CANDIDATAS[1],
                color : "#46BFBD",
                labelColor : '#fff'
            },
            {
                label: 'Comércio ' + CANDIDATASPORCEN[2] + '%',
                value : CANDIDATAS[2],
                color : "#FDB45C",
                labelColor : '#fff'
            },
            {
                label: 'Indústria ' + CANDIDATASPORCEN[3] + '%',
                value : CANDIDATAS[3],
                color : "#949FB1",
                labelColor : '#fff'
            },
            {
                label: 'Serviços ' + CANDIDATASPORCEN[4] + '%',
                value : CANDIDATAS[4],
                color : "#4D5360",
                labelColor : '#fff'
            },
            {
                label: 'Saúde ' + CANDIDATASPORCEN[5] + '%',
                value : CANDIDATAS[5],
                color : "#4D52FF",
                labelColor : '#fff'
            },
            {
                label: 'T.I. ' + CANDIDATASPORCEN[6] + '%',
                value : CANDIDATAS[6],
                color : "#3D43cc",
                labelColor : '#fff'
            },
            {
                label: 'Turismo ' + CANDIDATASPORCEN[7] + '%',
                value : CANDIDATAS[7],
                color : "#ff5390",
                labelColor : '#fff'
            }

    ];
        var graphInitDelay = 300;
        var globalGraphSettings = {
             animation : Modernizr.canvas,
             segmentShowStroke: true,
             segmentStrokeWidth: 2,
             labelFontColor: '#000',
             labelFontSize: "14"
         };

         function showChartCategoria(){
             var ctx = document.getElementById("chartInscritas").getContext("2d");
             new Chart(ctx).Pie(inscritasData, globalGraphSettings);
             var ctx2 = document.getElementById("chartCandidatas").getContext("2d");
             new Chart(ctx2).Pie(candidatasData, globalGraphSettings);
         };
        $("#areaChartCategoria").on("inview",function(){
            var $this = $(this);
            $this.removeClass("hidden").off("inview");
            setTimeout(showChartCategoria, graphInitDelay);			
        });
    }


        if ($('#areaChartInscricoes').length) {

            var colors = [
                '#39e233', '#b4afde', '#96db65', '#f505a5', '#88cf8c', '#c2b5e0',
                '#1a1a75', '#495904', '#2250c4', '#2d44b8', '#f5366d', '#39f588',
                '#d1c07e', '#d98c5e', '#90a3b3', '#db2a10', '#258eb1', '#b86cb4',
                '#3a24cb', '#5407c0', '#385a9d', '#9d1a25', '#369e63', '#488aa0',
                '#a72a27', '#6f9f35', '#789ce0', '#f8c243', '#67f704'
            ];

            var graphInitDelay = 300;
            var globalGraphSettings = {
                 animation : Modernizr.canvas,
                 segmentShowStroke: false,
                 segmentStrokeWidth: 1,
                 labelFontColor: '#000',
                 labelFontSize: "10"
             };

            var inscricoes = $.parseJSON(INSCRICOES_JSON);
            var inscricoesPorcent = $.parseJSON(INSCRICOES_PORCENT_JSON);
            var ufs = $.parseJSON(UFS_JSON);
            var inscricoesData = [];
            var key; 
            for (key in inscricoes) {
                inscricoesData.push({
                    label: ufs[key] + ' '  + inscricoesPorcent[key] + '%',
                    value: parseFloat(inscricoes[key]), color:colors[key], labelColor : '#fff'
                });
            }

            var inscricoesGeralData = [
                {
                    label: 'Própria Empresa ' + TOTALEMPRESASPORCENT + '%',
                    value: TOTALEMPRESAS,
                    color:"#F7464A",
                    labelColor : '#fff'
                },
                {
                    label: 'Digitador ' + TOTALDIGITADORESPORCENT + '%',
                    value : TOTALDIGITADORES,
                    color : "#46BFBD",
                    labelColor : '#fff'
                }
            ];

            function showChartInscricoes(){
                var ctx = document.getElementById("chartInscricoes").getContext("2d");
                new Chart(ctx).Pie(inscricoesData, globalGraphSettings);
                var ctx2 = document.getElementById("chartInscricoes2").getContext("2d");
                new Chart(ctx2).Pie(inscricoesGeralData, globalGraphSettings);
            };
            $("#areaChartInscricoes").on("inview", function(){
                var $this = $(this);
                $this.removeClass("hidden").off("inview");
                setTimeout(showChartInscricoes, graphInitDelay);			
            });
        }
    }
      
    return {
        init: init
    };
}());

$(function() {
    reportModule.init();
});