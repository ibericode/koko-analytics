'use strict';

import m from 'mithril';
import {format} from "date-fns";
import './totals.css';
import numbers from '../util/numbers.js';

function Component(vnode) {
    let startDate;
    let endDate;
    let visitors = 0;
    let pageviews = 0;
    let visitorsChange = 0.00;
    let pageviewsChange = 0.00;

    function fetch(vnode) {
        if (typeof(startDate) === "object" && vnode.attrs.startDate.getTime() === startDate.getTime() && vnode.attrs.endDate.getTime() === endDate.getTime()) {
            return;
        }

        startDate = new Date(vnode.attrs.startDate);
        endDate = new Date(vnode.attrs.endDate);

        let diff = (endDate.getTime() - startDate.getTime());
        let previousStartDate = new Date(startDate.getTime() - diff);
        let previousEndDate = new Date(endDate.getTime() - diff);


        // fetch stats for this period
        m.request(`${aaa.root}aaa-stats/v1/stats?start_date=${format(startDate, 'yyyy-MM-dd')}&end_date=${format(endDate, 'yyyy-MM-dd')}&count=1`)
            .then(data => {
               visitors = 0;
               pageviews = 0;

               data.forEach(r => {
                   visitors += parseInt(r.visitors);
                   pageviews += parseInt(r.pageviews);
               });

                // fetch stats from period period to compare against
                m.request(`${aaa.root}aaa-stats/v1/stats?start_date=${format(previousStartDate, 'yyyy-MM-dd')}&end_date=${format(previousEndDate, 'yyyy-MM-dd')}&count=1`)
                    .then(data => {
                       let previousVisitors = 0;
                       let previousPageviews = 0;
                        visitorsChange = 0;
                        pageviewsChange = 0;

                        data.forEach(r => {
                            previousVisitors += parseInt(r.visitors);
                            previousPageviews += parseInt(r.pageviews);
                        });

                        if (previousVisitors > 0) {
                            visitorsChange = Math.round((visitors / previousVisitors - 1) * 100);
                        }

                        if (previousPageviews > 0) {
                            pageviewsChange = Math.round((pageviews / previousPageviews - 1) * 100);
                        }
                    });



            });


    }

    fetch(vnode);

    return {
        onupdate(vnode) {
            fetch(vnode);
        },

        view() {
            return (
                <div className="totals-container">
                    <div className="totals-box">
                        <div className="totals-label">Total visitors</div>
                        <div className="totals-amount">{numbers.formatPretty(visitors)}</div>
                        <div className={"totals-change " + (visitorsChange > 0 ? "up" : visitorsChange === 0 ? "neutral" : "down")}> {numbers.formatPercentage(visitorsChange)}</div>
                    </div>
                    <div className="totals-box">
                        <div className="totals-label">Total pageviews</div>
                        <div className="totals-amount">{numbers.formatPretty(pageviews)}</div>
                        <div className={"totals-change " + (pageviewsChange > 0 ? "up" : pageviewsChange === 0 ? "neutral" : "down")}> {numbers.formatPercentage(pageviewsChange)}</div>
                    </div>
                </div>
            )
        }
    }
}

export default Component;