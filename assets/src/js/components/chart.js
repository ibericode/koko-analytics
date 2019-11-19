'use strict';

import m from 'mithril';
import Chart from 'chart.js';
import 'chartjs-adapter-date-fns';
import '../../sass/chart.scss';
import { format } from 'date-fns'
import api from '../util/api.js';
import en from 'date-fns/locale/en-US';

Chart.defaults.global.defaultFontColor = '#666';
Chart.defaults.global.defaultFontFamily = '-apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Oxygen-Sans, Ubuntu, Cantarell, "Helvetica Neue", sans-serif';

function Component(vnode) {
	let startDate = new Date(vnode.attrs.startDate);
	let endDate = new Date(vnode.attrs.endDate);
	let pageviews = {};
	let visitors = {};
	let chart;

	const chartOptions = {
		type: 'bar',
		data: {
			labels: [],
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
						borderDash: [2, 4],
					},
					ticks: {
						beginAtZero: true,
						precision: 0,
						min: 0,
					}
				}],
				xAxes: [{
					stacked: true,
					type: 'time',
					time: {
						tooltipFormat: 'MMM d, yyyy',
						minUnit: 'day',
					},
					ticks: {
						source: 'labels',
						autoSkip: true,
						maxTicksLimit: 12,
						maxRotation: 0,
						minRotation: 0,
					},
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
		pageviews = {};
		visitors = {};

		// fill chart with 0's
		let labels = [];
		let i = 0;
		for(let d = new Date(startDate); d <= endDate; d.setDate(d.getDate() + 1)) {
			let key = format(d, 'yyyy-MM-dd');
			labels[i] = new Date(d);

			pageviews[key] = { x: labels[i], y: 0 };
			visitors[key] = { x: labels[i], y: 0 };
			i++;
		}

		chartOptions.data.labels = labels;
		chartOptions.data.datasets[0].data = [];
		chartOptions.data.datasets[1].data =  [];
		chart.update();

		// fetch stats
		api.request(`/stats`, {
			params: {
				start_date: format(startDate, 'yyyy-MM-dd'),
				end_date: format(endDate, 'yyyy-MM-dd')
			}
		}).then(data => {
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
			const computedHeight = Math.max(240, Math.min(window.innerHeight / 3, window.innerWidth / 2, 360));
			return (
				<div className="box">
				<div className={"chart-container"}>
				<canvas id="koko-analytics-chart" height={vnode.attrs.height || computedHeight}></canvas>
				</div>
				</div>
		)
		}
	}
}

export default Component;
