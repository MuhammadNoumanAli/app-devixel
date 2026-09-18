if (window.location.pathname === '/home') {
    runAsyncFunctions();
}

async function runAsyncFunctions() {
    const userRole = document.querySelector('meta[name="user-role"]').content;

    await loadCarrierChart(7);
    await loadDispatcherChart(7);
    if (userRole === 'Admin') {
        await loadDispatcherRevenueChart('monthWise');
    } else {
        await loadDispatcherRevenueChart('thisMonth');
    }
    await truckTypePieCharts('last_thirty_days');
}

async function loadCarrierChart($params) {
    try {
        let response = await fetch('/carriers/chart-type?chart='+$params);
        if (response.ok) {
            let data = await response.json();
            $('#reportsSalesAgentChart').html('');
            drawChart(JSON.stringify(data.data), JSON.stringify(data.categories), 'reportsSalesAgentChart');
        } else {
            console.error(response.status);
        }
    } catch (error) {
        console.error(error);
    }
}


async function loadDispatcherChart($params) {
    try {
        let response = await fetch('/dispatchers/chart-type?chart='+$params);
        if (response.ok) {
            let data = await response.json();
            $('#reportsDispatchersChart').html('');
            drawChart(JSON.stringify(data.data), JSON.stringify(data.categories), 'reportsDispatchersChart');
        } else {
            console.error(response.status);
        }
    } catch (error) {
        console.error(error);
    }
}

async function loadDispatcherRevenueChart($params) {
    try {
        let response = await fetch('/dispatchers/revenue/chart-type?chart='+$params);
        if (response.ok) {
            let data = await response.json();
            $('#reportsDispatchersRevenueChart').html('');
            drawChart(JSON.stringify(data.data), JSON.stringify(data.categories), 'reportsDispatchersRevenueChart');
        } else {
            console.error(response.status);
        }
    } catch (error) {
        console.error(error);
    }
}


function drawChart(data, categories, id){
    var options = {
        series: JSON.parse(data),
        chart: {
            height: 350,
            type: 'line',
            toolbar: {
                show: false
            },
            redrawOnParentResize: true,
            redrawOnWindowResize: true,
        },
        markers: {
            size: 4
        },
        // colors: ['#4154f1', '#2eca6a', '#ff771d'],
        fill: {
            type: "gradient",
            gradient: {
                shadeIntensity: 1,
                opacityFrom: 0.3,
                opacityTo: 0.4,
                stops: [0, 90, 100]
            }
        },
        dataLabels: {
            enabled: false
        },
        stroke: {
            curve: 'smooth',
            width: 2
        },
        xaxis: {
            type: 'datetime',
            categories: JSON.parse(categories)
        },
        yaxis: {
            forceNiceScale: true,
            labels: {
                formatter: Math.floor,
            },
        },
        tooltip: {
            x: {
                formatter: function(value) {
                    return new Date(value).toLocaleString('en-US', { month: 'long', year: 'numeric' });
                }
            },
            // x: {
            //     format: 'dd, MMM yyyy'
            // },
        },
        noData: {
            text: 'Loading...'
        },
        redrawOnParentResize: true,
    }
    var chart = new ApexCharts(
        document.querySelector("#"+id),
        options
    );
    chart.render();
}


async function truckTypePieCharts(chartType) {
    // AJAX request to fetch data based on chart type
    $.ajax({
        url: 'truck-type/pie-chart', // Replace with your controller route
        method: 'GET',
        data: {
            chart: chartType
        },
        success: function (response) {
            // Initialize empty arrays for chart data
            var chartData = [];
            var legendData = [];

            // Populate chart data arrays from the response
            response.forEach(function (item) {
                chartData.push({ value: item.value, name: item.name });
                legendData.push(item.name);
            });

            // Initialize echarts instance and set options
            var myChart = echarts.init(document.querySelector("#truckTypePieChart"));
            var options = {
                tooltip: {
                    trigger: 'item'
                },
                legend: {
                    top: '5%',
                    left: 'center',
                    data: legendData // Set legend data
                },
                series: [{
                    name: 'Truck Name',
                    type: 'pie',
                    radius: ['40%', '70%'],
                    avoidLabelOverlap: false,
                    label: {
                        show: false,
                        position: 'center'
                    },
                    emphasis: {
                        label: {
                            show: true,
                            fontSize: '18',
                            fontWeight: 'bold'
                        }
                    },
                    labelLine: {
                        show: false
                    },
                    data: chartData // Set chart data
                }]
            };

            // Set chart options
            myChart.setOption(options);
        },
        error: function (xhr, status, error) {
            console.error(error);
        }
    });
}
