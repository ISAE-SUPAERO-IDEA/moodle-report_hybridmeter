var xdash_hits_chart = function(chart_data, canvas, bar_color, time='day') {
    var ctx = document.getElementById(canvas).getContext('2d');
    chart_data.sort((a, b) => {
        if (a.active == b.active) return 0;
        return a.active ? 1 : -1;
    })
    labels = [];
    data = [];
    for (i=0;i<chart_data.length;i++) {
        var date = new Date(chart_data[i].key)
        chart_data[i].x = date;
        chart_data[i].y = chart_data[i].doc_count;
        data.push(chart_data[i].doc_count);
        if(time == 'day'){
            labels.push(date);
        }else{
            labels.push(date);
        }
        
    }

    var scatterChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels,
            datasets: [{
                backgroundColor: bar_color,
                label: 'Dataset 1',
                data: data
            }]
        },
        options: {
            responsive: true, 
            maintainAspectRatio: false,
            legend: {
                display: false
            },
            scales: {
                yAxes: [{
                    type: 'linear',
                    gridLines: {
                        color: "rgba(0, 0, 0, 0)",
                    }
                }],
                xAxes: [{
                    type: 'time',
                    position: 'bottom',
                    time: {
                        unit: time,
                        isoWeekday: true
                    },
                    gridLines: {
                        color: "rgba(0, 0, 0, 0)",
                    }
                }]
            }
        }
    });

    return scatterChart;    
}


var xdash_activity_chart = function(chart_data, canvas) {
    var ctx = document.getElementById(canvas).getContext('2d');
    //#document.getElementById('activity_canvas').height = 50;
    /*
    document.getElementById('activity_canvas').onclick=function(evt) {
        var activePoints = scatterChart.getElementsAtEvent(evt);
        if (activePoints[0]) {
            data = chart_data[activePoints[0]._index];
            change_param("traces_range", data.key);
        }
        return;
    };
    */

    chart_data.sort((a, b) => {
        if (a.active == b.active) return 0;
        return a.active ? 1 : -1;
    })

    var pointRadius = []
    var pointBackgroundColor = []
    var pointBorderColor = []
    var pointHoverRadius = []
    var pointLabel = []
    var max_doc_count = chart_data.reduce((curval, data) => {
        return data.doc_count > curval ? data.doc_count : curval;
    },0);
    for (i=0;i<chart_data.length;i++) {
        var realdate = new Date(chart_data[i].key)
        var date = new Date(realdate.getFullYear(), realdate.getMonth(),realdate.getDate()); 
        var hours = realdate.getHours();
        chart_data[i].x = date;
        chart_data[i].y = hours;
        var size = 0 + (chart_data[i].doc_count * 10)/ max_doc_count;
        pointRadius.push(size);
        pointHoverRadius.push(size*1.5);
        pointBackgroundColor.push(chart_data[i].active ? "#ffb0c1" : "#eee");
        pointBorderColor.push(chart_data[i].active ? "#ffb0c1" : "#999");
        pointLabel.push(chart_data[i].doc_count)
    }
    for (i=0;i<chart_data.length;i++) {
    }

    var scatterChart = new Chart(ctx, {
        type: 'scatter',
              
        data: {
            datasets: [{
                //label: 'Scatter Dataset',
                showLine: false,
                pointRadius,
                label: pointLabel,
                pointHoverRadius,
                pointBackgroundColor,
                pointBorderColor,
                data: chart_data 
            }]
        },
        options: {
            responsive: true, 
            maintainAspectRatio: false,
            legend: {
                display: false
            },
            tooltips: {
                callbacks: {
                    label: function(tooltipitems, data) {
                        data = chart_data[tooltipitems.index];
                        date = chart_data[tooltipitems.index].x
                        hour = chart_data[tooltipitems.index].y
                        return `${date.getDate()}/${date.getMonth()}/${date.getFullYear()} entre ${hour}h et ${hour+3}h : ${data.doc_count} accès`;
                    }
                }
            },
            scales: {
                yAxes: [{
                    type: 'linear',

                    ticks: {
                        callback: function(value, index, values) {
                            return hour_to_range(value);
                        },
                        min: 0,
                        max: 23,
                        stepSize: 3,
                    },
                    gridLines: {
                        color: "rgba(0, 0, 0, 0)",
                    }
                }],
                xAxes: [{
                    type: 'time',
                    position: 'bottom',
                    time: {
                        unit: 'day'
                    },
                    gridLines: {
                        color: "rgba(0, 0, 0, 0)",
                    }
                }]
            }
        }
    });    
}

