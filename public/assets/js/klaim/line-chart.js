'use strict';

function klaimLineChartOptions(months, series, colors, axisFormatter, tooltipFormatter) {
    const labelStyle = {fontSize:'10px',colors:'#81979d'};
    return {
        chart:{type:'line',height:285,toolbar:{show:false},animations:{enabled:false},fontFamily:'Poppins, sans-serif'},
        series,
        colors,
        dataLabels:{enabled:false},
        grid:{borderColor:'#edf2f3',strokeDashArray:4},
        legend:{position:'top',horizontalAlign:'left',fontSize:'10px'},
        noData:{text:'Belum ada data'},
        stroke:{width:3,curve:'straight'},
        markers:{size:3,hover:{sizeOffset:2}},
        xaxis:{categories:months,labels:{style:labelStyle},axisBorder:{show:false},axisTicks:{show:false}},
        yaxis:{min:0,forceNiceScale:true,labels:{formatter:axisFormatter,style:labelStyle}},
        tooltip:{shared:true,intersect:false,y:{formatter:tooltipFormatter}},
        responsive:[{breakpoint:576,options:{chart:{height:265},legend:{fontSize:'9px'}}}]
    };
}
