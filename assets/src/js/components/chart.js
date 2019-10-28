'use strict';

import m from 'mithril';
import Chartist from 'chartist';
import 'chartist-plugin-tooltips-updated';
import './chart.css';
import { format } from 'date-fns'

const now = new Date();
let startDate = new Date(now.getFullYear(), now.getMonth(), 1, 0, 0, 0);
let endDate = new Date(now.getFullYear(), now.getMonth() + 1, 0, 23, 59, 59);

// fill up period with 0's
let pageviews = {};
let visitors = {};
let labels = [];
for(let i = startDate; i < endDate; i.setDate(i.getDate() + 1)) {
    let key = format(i, 'yyy-MM-dd');
    labels.push(format(i, 'MMM d'));
    pageviews[key] = 0;
    visitors[key] = 0;
}
const chartData = {
    labels,
    series: [],
};
const chartOptions = {
    fullWidth: true,
    axisY: {
        onlyInteger: true,
        low: 0,
    },
    stackBars: true,
    stackMode: 'overlap',
    axisX: {

    },
    plugins: [
        Chartist.plugins.tooltip()
    ]
};


function Component(vnode) {
	// TODO: Get startDate & endDate from vnode.attrs

    return {
        oncreate: (vnode) => {
            const chart = new Chartist.Bar('.ct-chart', chartData, chartOptions);

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
                chartData.series = [
                    {
                        name: 'Pageviews',
                        data: Object.values(pageviews)
                    },
                    {
                        name: "Visitors",
                        data: Object.values(visitors)
                    }
                ];

                chart.update(chartData);
            });
        },

        view: () => (
            <div className = "ct-chart ct-double-octave"> </div>
        )
    }
}

export default Component;
