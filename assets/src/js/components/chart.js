'use strict';

import m from 'mithril';
import Chartist from 'chartist';
import 'chartist-plugin-tooltips-updated';
import './chart.css';
import { format } from 'date-fns'


function Component(vnode) {
    let startDate = new Date(vnode.attrs.startDate);
    let endDate = new Date(vnode.attrs.endDate);
    let pageviews = {};
    let visitors = {};
    let labels = [];
    let chart;
    let chartData = {
        labels: [],
        series: [],
    };
    let strokeWidth = 100 / labels.length;
    const chartOptions = {
        fullWidth: true,
        axisY: {
            onlyInteger: true,
            low: 0,
        },
        stackBars: true,
        stackMode: 'overlap',
        seriesBarDistance: 5,
        axisX: {
        },
        plugins: [
            Chartist.plugins.tooltip()
        ]
    };

    function updateChart() {
        // empty previous data
        labels = [];
        pageviews = {};
        visitors = {};

        // calculate number of ticks (estimate)
        let ticks = Math.floor((endDate.getTime() - startDate.getTime()) / 86400 / 1000);

        // fill data with 0's and set labels
        for(let i = new Date(startDate); i < endDate; i.setDate(i.getDate() + 1)) {
            let key = format(i, 'yyyy-MM-dd');

            if (ticks <= 31 || i.getDate() === 1) {
                labels.push(format(i, 'MMM d'));
            } else {
                labels.push('');
            }

            pageviews[key] = 0;
            visitors[key] = 0;
        }

        strokeWidth = 100 / labels.length;

        // fetch stats
        m.request(aaa.root + 'aaa-stats/v1/stats', {
            headers: {
                "X-WP-Nonce": aaa.nonce
            },
        }).then(data => {
            data.forEach(d => {
                if (typeof(pageviews[d.date]) === "undefined") {
                    return;
                }

                pageviews[d.date] = parseInt(d.pageviews);
                visitors[d.date] = parseInt(d.visitors);
            });

            chartData = {
                labels,
                series: [
                    {
                        name: 'Pageviews',
                        data: Object.values(pageviews)
                    },
                    {
                        name: "Visitors",
                        data: Object.values(visitors)
                    }
                ]
            };

            chart.update(chartData);
        });
    }

    return {
        oncreate: (vnode) => {
            chart = new Chartist.Bar('.ct-chart', chartData, chartOptions);
            chart.on('draw', function(data) {
                if(data.type === 'bar') {
                    data.element.attr({
                        style: `stroke-width: ${strokeWidth}%`,
                    });
                }
            });

            updateChart();
        },
        onupdate: (vnode) => {
            if (vnode.attrs.startDate.getTime()  === startDate.getTime() && vnode.attrs.endDate.getTime() === endDate.getTime()) {
                return;
            }

            startDate = new Date(vnode.attrs.startDate);
            endDate = new Date(vnode.attrs.endDate);
            updateChart();
        },
        view: (vnode) => {
            return (
                <div className="chart-container">
                    <div className="ct-chart ct-double-octave"> </div>
                </div>
            )
        }
    }
}

export default Component;
