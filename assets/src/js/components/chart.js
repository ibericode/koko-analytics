'use strict';

import m from 'mithril';
import Chart from 'chart.js';
import 'chartjs-adapter-date-fns';
import '../../sass/chart.scss';
import { format } from 'date-fns'
import api from '../util/api.js';
import en from 'date-fns/locale/en-US';

function Component(vnode) {
    let startDate = new Date(vnode.attrs.startDate);
    let endDate = new Date(vnode.attrs.endDate);
    let pageviews = {};
    let visitors = {};
    let dates = [];
    let chart;


    const timeFormat = 'YYYY-MM-DD';
    Chart.defaults.global.defaultFontColor = '#666';
    Chart.defaults.global.defaultFontFamily = '-apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Oxygen-Sans, Ubuntu, Cantarell, "Helvetica Neue", sans-serif';
    const chartOptions = {
        type: 'bar',
        data: {
            labels: dates,
            datasets: [
				{
					label: 'Visitors',
					backgroundColor: '#d70206',
					data: [],
				},
                {
                    label: 'Pageviews',
					backgroundColor: '#f05b4f',
                    data: [],
                },
            ],
        },
        options: {
            legend: { display: false },
            tooltips: {
                backgroundColor: "#FFF",
                bodyFontColor: "#444",
                titleFontColor: "#23282d",
                borderColor: "#BBB",
                borderWidth: 1,
            },
            maintainAspectRatio: false,
            scales: {
                yAxes: [{
                    stacked: true,
                    gridLines: {
                        color: "#EEE",
                    },
					ticks: {
						beginAtZero: true,
						precision: 0
					}
                }],
                xAxes: [{
                    stacked: true,
                    type: 'time',
                    time: {
                        parser: timeFormat,
                        tooltipFormat: 'MMM d, yyyy',
                        minUnit: 'day',
                    },
                    distribution: 'series',
                    adapters: {
                        date: {
                            locale: en
                        }
                    },
                    gridLines: {
                        display: false,
                    },
                }],
            }
        }
    };

    function updateChart() {
        // empty previous data
        dates = [];
        pageviews = {};
        visitors = {};

        // fill chart with 0's
        let i = 0;
        for(let d = new Date(startDate); d <= endDate; d.setDate(d.getDate() + 1)) {
            let key = format(d, 'yyyy-MM-dd');
            dates[i] = new Date(d);
            pageviews[key] = { x: dates[i], y: 0 };
            visitors[key] = { x: dates[i], y: 0 };
            i++;
        }

        chartOptions.data.labels = dates;
        chartOptions.data.datasets[0].data = [];
        chartOptions.data.datasets[1].data =  [];
        chart.update();

        // fetch stats
        api.request(`/stats`, {
            params: {
                start_date: format(startDate, 'yyyy-MM-dd'),
                end_date: format(endDate, 'yyyy-MM-dd')
            }
        })
            .then(data => {
                data.forEach(d => {
                    if (typeof(pageviews[d.date]) === "undefined") {
                        console.error("Unexpected date in response data", d.date);
                        return;
                    }

                    pageviews[d.date].y = parseInt(d.pageviews);
                    visitors[d.date].y = parseInt(d.visitors);
                });

				chartOptions.data.datasets[0].data = Object.values(visitors);
				chartOptions.data.datasets[1].data = Object.values(pageviews);
                chart.update();
            });
    }

    return {
        oncreate: (vnode) => {
           const ctx = document.getElementById('koko-analytics-chart').getContext('2d');
           chart = new Chart(ctx, chartOptions);
           updateChart();
        },
        onupdate: (vnode) => {
            if (vnode.attrs.startDate.getTime() === startDate.getTime() && vnode.attrs.endDate.getTime() === endDate.getTime()) {
                return;
            }

            startDate = new Date(vnode.attrs.startDate);
            endDate = new Date(vnode.attrs.endDate);
            updateChart();
        },
        view: (vnode) => {
            return (
                <div className="chart-container">
                    <canvas id="koko-analytics-chart" height={vnode.attrs.height || window.innerHeight / 3}></canvas>
                </div>
            )
        }
    }
}

export default Component;
