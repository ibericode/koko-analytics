'use strict';

import m from 'mithril';
import {format} from "date-fns";
import '../../sass/totals.scss';
import numbers from '../util/numbers.js';
import api from '../util/api.js';
const i18n = window.koko_analytics.i18n;
const now = new Date();

function Component(vnode) {
	let startDate = vnode.attrs.startDate;
	let endDate = vnode.attrs.endDate;
	let visitors = 0;
	let pageviews = 0;
	let visitorsChange = 0;
	let pageviewsChange = 0;
	let visitorsPrevious = 0;
	let pageviewsPrevious = 0;
	let visitorsDiff = 0;
	let pageviewsDiff = 0;
	let previousParams = null;


	function fetch() {
		const params =  {
			start_date: format(startDate, 'yyyy-MM-dd'),
			end_date: format(endDate, 'yyyy-MM-dd')
		};

		if (JSON.stringify(params) === JSON.stringify(previousParams)) {
			return;
		}

		let s = startDate;

		// if end date is in future, use today instead so we get a fair comparison
		let e = endDate <= now ? endDate : now;
		previousParams = params;
		let diff = (e.getTime() - s.getTime()) - 1;
		let previousStartDate = new Date(s.getTime() - diff);
		let previousEndDate = new Date(e.getTime() - diff);


		// fetch stats for this period
		api.request(`/stats`, {params})
			.then(data => {
				visitors = 0;
				pageviews = 0;

				data.forEach(r => {
					visitors += parseInt(r.visitors);
					pageviews += parseInt(r.pageviews);
				});

				// fetch stats from period period to compare against
				api.request(`/stats`, {
					params: {
						start_date: format(previousStartDate, 'yyyy-MM-dd'),
						end_date: format(previousEndDate, 'yyyy-MM-dd')
					}
				}).then(data => {
					visitorsPrevious = 0;
					pageviewsPrevious = 0;
					visitorsChange = 0;
					pageviewsChange = 0;
					visitorsDiff = 0;
					pageviewsDiff = 0;

					data.forEach(r => {
						visitorsPrevious += parseInt(r.visitors);
						pageviewsPrevious += parseInt(r.pageviews);
					});

					if (visitorsPrevious > 0) {
						visitorsDiff = visitors - visitorsPrevious;
						visitorsChange = Math.round((visitors / visitorsPrevious - 1) * 100);
					}

					if (pageviewsPrevious > 0) {
						pageviewsDiff = pageviews - pageviewsPrevious;
						pageviewsChange = Math.round((pageviews / pageviewsPrevious - 1) * 100);
					}
				});
		});


	}

	fetch();

	return {
		onupdate(vnode) {
			if (vnode.attrs.startDate.getTime() !== startDate.getTime() || vnode.attrs.endDate.getTime() !== endDate.getTime()) {
				startDate = vnode.attrs.startDate;
				endDate = vnode.attrs.endDate;
				fetch();
			}
		},
		view() {
			return (
				<div className="totals-container">
					<div className="totals-box">
						<div className="totals-label">{i18n['Total visitors']}</div>
						<div className="totals-amount">{numbers.formatPretty(visitors)} <span className={visitorsChange > 0 ? "up" : visitorsChange === 0 ? "neutral" : "down"}>{numbers.formatPercentage(visitorsChange)}</span></div>
						<div className="totals-compare">
							<span>{numbers.formatPretty(Math.abs(visitorsDiff))} {visitorsDiff > 0 ? "more" : "less"} than previous period</span>
						</div>
					</div>
					<div className="totals-box">
						<div className="totals-label">{i18n['Total pageviews']}</div>
						<div className="totals-amount">{numbers.formatPretty(pageviews)} <span className={pageviewsChange > 0 ? "up" : pageviewsChange === 0 ? "neutral" : "down"}>{numbers.formatPercentage(pageviewsChange)}</span></div>
						<div className="totals-compare">
							<span>{numbers.formatPretty(Math.abs(pageviewsDiff))} {pageviewsDiff > 0 ? "more" : "less"} than previous period</span>
						</div>
					</div>
				</div>
			)
		}
	}
}

export default Component;
