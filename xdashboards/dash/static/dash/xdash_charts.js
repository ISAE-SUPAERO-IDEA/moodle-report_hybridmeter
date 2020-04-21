/*TABLEAU DES COULEURS*/
colors = [
    "#ef9a9a", "#9fa8da", "#ffcc80", "#a5d6a7", "#81d4fa", "#e57373", "#7986cb", "#ffb74d", "#81c784", "#4fc3f7",
    "#ef5350", "#5c6bc0", "#ffa726", "#66bb6a", "#29b6f6", "#f44336", "#3f51b5", "#ff9800", "#4caf50", "#03a9f4",
    "#e53935", "#3949ab", "#fb8c00", "#43a047", "#039be5", "#d32f2f", "#303f9f", "#f57c00", "#388e3c", "#0288d1",
    "#c62828", "#283593", "#ef6c00", "#2e7d32", "#0277bd", "#b71c1c", "#1a237e", "#e65100", "#1b5e20", "#01579b",
    "#ff8a80", "#8c9eff", "#ffd180", "#b9f6ca", "#80d8ff", "#ff5252", "#536dfe", "#ffab40", "#69f0ae", "#40c4ff"
];

var xdash_hits_chart = function(chart_data, canvas, time='day') {

    var ctx = document.getElementById(canvas).getContext('2d');
    
    chart_data.sort((a, b) => {
        if (a.active == b.active) return 0;
        return a.active ? 1 : -1;
    })


    labels = [];
    data = [];
    backcolors = [];
    lastKey = {'position': -1, 'key': ""};

    for (i=0;i<chart_data.length;i++) {
        
        activity_children = chart_data[i].activity_children;

        for (j=0;j<activity_children.length;j++) {
            var date = new Date(activity_children[j].key);
            
            if(activity_children[j].key == lastKey.key){
                data[lastKey.position] += activity_children[j].doc_count;
            }else{
                data.push(activity_children[j].doc_count);
                labels.push(date);
                backcolors.push(colors[i%50]);
                lastKey.key = activity_children[j].key;
                lastKey.position +=1;
            }

            
        }
        
    }

    var barChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [{
                backgroundColor: backcolors,
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
                        unit: 'week',
                        isoWeekday: true
                    },
                    gridLines: {
                        color: "rgba(0, 0, 0, 0)",
                    }
                }]
            }

        }
    });

    return barChart;    
}


var xdash_activity_chart = function(chart_data, canvas, time='day', weeks_data=null) {
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

    if(weeks_data != null){
        chart_data = weeks_by_hours_data(chart_data, weeks_data);
    }

    chart_data.sort((a, b) => {
        if (a.active == b.active) return 0;
        return a.active ? 1 : -1;
    })

    

    var pointRadius = []
    var pointBackgroundColor = []
    var pointBorderColor = []
    var pointHoverRadius = []
    var pointLabel = []
    var max_doc_count = 0;
    //Taille max
    for (i=0;i<chart_data.length;i++) {
        var tmp = chart_data[i].activity_children.reduce((curval, data) => {
            return data.doc_count > curval ? data.doc_count : curval;
        },0);
        if(tmp > max_doc_count){
            max_doc_count = tmp;
        }
    }

    data_points = [];
        
    for (i=0;i<chart_data.length;i++) {

        activity_children = chart_data[i].activity_children;

        for(j=0;j<activity_children.length;j++){

            var realdate = new Date(activity_children[j].key)
            var date = new Date(realdate.getFullYear(), realdate.getMonth(),realdate.getDate()); 
            var hours = realdate.getHours();
            //activity_children[j].x = date;
            //activity_children[j].y = hours;
            data_points.push( {x: date, y: hours, doc_count: activity_children[j].doc_count} );
            var size = Math.sqrt( ( (activity_children[j].doc_count * 100)/ max_doc_count ) /Math.PI);
            pointRadius.push(size);
            pointHoverRadius.push(size*1.5);
            if(time == "week"){
                pointBackgroundColor.push(activity_children[j].active ? chart_data[i].color : "#eee");
                pointBorderColor.push(activity_children[j].active ? chart_data[i].color : "#999");
            }else{
                pointBackgroundColor.push(activity_children[j].active ? colors[i%50] : "#eee");
                pointBorderColor.push(activity_children[j].active ? colors[i%50] : "#999");
            }
            pointLabel.push(activity_children[j].doc_count);

        }
            
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
                data: data_points 
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
                        data = data_points[tooltipitems.index];
                        date = data_points[tooltipitems.index].x
                        hour = data_points[tooltipitems.index].y
                        if(time == 'day'){
                            return `${date.getDate()}/${date.getMonth()+1}/${date.getFullYear()} entre ${hour}h et ${hour+3}h : ${data.doc_count} accès`;
                        }else{
                            return `Semaine du : ${date.getDate()}/${date.getMonth()+1}/${date.getFullYear()} entre ${hour}h et ${hour+3}h : ${data.doc_count} accès`;
                        }
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
                        unit: 'week',
                        isoWeekday: true
                    },
                    gridLines: {
                        color: "rgba(0, 0, 0, 0)",
                    }
                }]
            }
        }
    });    
}

function weeks_by_hours_data(months_data, weeks_data){

    var chart_data = [];

    var weeks_count = 0;
        
    //Pour chaque mois
    for(i=1;i<=months_data.length;i++){

        //Tant que le début de la semaine appartient au mois en cours
        while( (weeks_count<weeks_data.length) &&
            ( (i<=months_data.length-1 && months_data[i].key > weeks_data[weeks_count].key) || (i==months_data.length) )
        ){

            var data = new Map();
            var activity_children = [];

            date = new Date(weeks_data[weeks_count].key);
            days = weeks_data[weeks_count].activity_children;
            
            //Pour chaque aggrégation de 3h de la semaine
            for(j=0;j<days.length;j++){
                var date_tmp = new Date(days[j].key);
                hour = date_tmp.getHours();
                if(data.get(hour) == undefined){
                    data.set(hour, {key: date.setHours(hour), active: days[j].active, doc_count: days[j].doc_count});
                }else{
                    data.get(hour).doc_count += days[j].doc_count;
                }
            }

            var add_children = function(value){
                activity_children.push(value);
            }

            data.forEach(add_children);

            //On ajoute les données dans chart_data
            chart_data.push({key: weeks_data[weeks_count].key, active: weeks_data[weeks_count].active, activity_children: activity_children, color: colors[i-1%50]})

            weeks_count++;

        }

    }


    return chart_data;

}