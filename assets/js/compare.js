$(document).ready(function () {
    let ctx = $('.chart-compare canvas');
    if (ctx.length) {
        const data = ctx.data('stats');

        let dataFields = ['cases', 'deaths', 'newCases', 'newDeaths', 'deathPercent'];

        let labels = [];

        let datasets = {};

        $.each(data, function (date, locations) {
            labels.push(date);
            $.each(locations, function (location, record) {
                $.each(record, function (field, value) {
                    if($.inArray(field, dataFields) > -1){
                        let key = field + ' - ' + location;
                        if(datasets[key]===undefined){
                            datasets[key] = [];
                        }
                        if(field.match(/percent/i)){
                            datasets[key].push(Math.round(100 * value)/100);
                        } else {
                            datasets[key].push(value);
                        }
                    }
                });
            });
        });

        let chartDataSets = [];

        $.each(datasets, function (label, data) {
            chartDataSets.push({
                label: label,
                fill: false,
                borderColor: '#' + Math.floor(Math.random() * 16777215).toString(16),
                data: data.reverse(),
                hidden: !label.match(/percent/i)
            });
        });

        new Chart(ctx, {
            type: 'line',
            data: {
                labels: labels.reverse(),
                datasets: chartDataSets
            },
            options: {
                maintainAspectRatio: false,
                responsive: true,
                tooltips: {
                    mode: 'index',
                    intersect: false,
                },
                hover: {
                    mode: 'nearest',
                    intersect: true
                },
                scales: {
                    yAxes: [{
                        ticks: {
                            beginAtZero: true
                        }
                    }]
                }
            }
        });
    }
});