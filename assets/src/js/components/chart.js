'use strict';

import React from 'react';
import { format } from 'date-fns'
import api from '../util/api.js';
import '../../sass/chart.scss';

function step(v, ticks) {
	let step = (v - (v % ticks)) / ticks;
	let round = 1000000;
	while(v < round * 3) {
		round /= 10;
	}
	return Math.ceil(step / round) * round;
}


export default class Component extends React.PureComponent {

	constructor(props) {
		super(props);

		this.state = {
			dataset: [],
			yMax: 0
		};

		this.base = React.createRef();
		this.tooltip = React.createRef();
		this.showTooltip = this.showTooltip.bind(this);
		this.hideTooltip = this.hideTooltip.bind(this);
	}

	updateChart() {
		// empty previous data
		let dataset = {};

		// fill chart with 0's
		for(let d = new Date(this.props.startDate); d <= this.props.endDate; d.setDate(d.getDate() + 1)) {
			let key = format(d, 'yyyy-MM-dd');
			dataset[key] = {
				date: new Date(d),
				pageviews: 0,
				visitors: 0,
			};
		}
		this.setState({
			dataset: Object.values(dataset)
		});

		let yMax = 0;

		// fetch stats
		api.request(`/stats`, {
			body: {
				start_date: format(this.props.startDate, 'yyyy-MM-dd'),
				end_date: format(this.props.endDate, 'yyyy-MM-dd')
			}
		}).then(data => {
			data.forEach(d => {
				if (typeof(dataset[d.date]) === "undefined") {
					console.error("Unexpected date in response data", d.date);
					return;
				}

				dataset[d.date].pageviews = parseInt(d.pageviews);
				dataset[d.date].visitors = parseInt(d.visitors);

				if (d.pageviews > yMax) {
					yMax = d.pageviews;
				}
			});

			this.setState({
				dataset: Object.values(dataset),
				yMax
			});
		});
	}

	componentDidMount() {
		this.updateChart();

		this.tooltip = document.createElement('div');
		this.tooltip.style.display = 'none';
		document.body.appendChild(this.tooltip);
	}

	componentDidUpdate(prevProps, prevState, snapshot) {
		if (this.props.startDate.getTime() === prevProps.startDate.getTime() && this.props.endDate.getTime() === prevProps.endDate.getTime()) {
			return;
		}

		this.updateChart();
	}

	showTooltip(evt) {
		const el = this.tooltip;

		if (this.tooltip.current !== evt.currentTarget) {
			this.tooltip.current = evt.currentTarget;
			el.style.display = 'block';
			el.style.position = 'absolute';
			el.innerText = 'Tooltip';
		}
		el.style.left = evt.pageX + "px";
		el.style.top = evt.pageY + "px";

		console.log("Mouse move", evt);

	}

	hideTooltip(evt) {
		console.log("On mouse leave");
		this.tooltip.current = null;
		this.tooltip.style.display = 'none';
	}

	render() {
		const {dataset, yMax} = this.state;
		const width = this.base.current ? this.base.current.clientWidth : window.innerWidth;
		const height = this.props.height || Math.max(240, Math.min(window.innerHeight / 3, window.innerWidth / 2, 360));

		const innerWidth = width - 24;
		const innerHeight = height - 24;
		const barWidth = innerWidth / dataset.length;

		const x = (value) => value / dataset.length * innerWidth;
		const y = (value) => innerHeight - ( value / yMax * innerHeight) || 0;
		const yStep = step(yMax, 3) || 1;
		return (
			<div className="box">
				<div className={"chart-container"}>
					<svg className="chart" ref={this.base} width={"100%"} height={height}>
						<g className={"axes"}>
							<g className={"axes-y"} textAnchor={"end"}>
								{[0, 1, 2].map(v => {
									return (
										<g transform={`translate(24, ${y(v * yStep)})`} key={v}>
											<line stroke={"#DDD"} x1={6} x2={width}  />
											<text fill="#999" x="0" dy="0.33em">{yStep * v}</text>
										</g>
									)
								})}
							</g>
							<g className={"axes-x"} transform={`translate(36, ${innerHeight})`} textAnchor="middle">
								{dataset.map((d, i) => {
									return (
										<g transform={`translate(${x(i) + 0.5*barWidth}, 0)`} key={i}>
											<line stroke="#DDD"  y2="6" />
											{i === 0 && <text fill="#999" y={9} dy={"1em"}>{format(d.date, 'MMM d, yyyy')}</text>}
										</g>
									);
								})}
							</g>
						</g>
						<g className={"bars"} transform={`translate(36, 0)`}>
							{dataset.map( (d, i) => {
								// do not draw unnecessary elements
								if (d.pageviews === 0) {
									return;
								}

								let pageviewHeight = d.pageviews / yMax * innerHeight || 0;
								let visitorHeight = d.visitors / yMax * innerHeight || 0;

								return (<g key={d.date}
										transform={`translate(${x(i) + 0.05 * barWidth}, 0)`}
										onMouseEnter={this.showTooltip}
										onMouseLeave={this.hideTooltip}>
										<rect
											className={"pageviews"}
											height={pageviewHeight - visitorHeight}
											width={barWidth * 0.9}
											y={y(d.pageviews).toFixed(3)}
										/>
										<rect
											className={"visitors"}
											height={visitorHeight}
											width={barWidth * 0.9}
											y={y(d.visitors.toFixed(3))}
										/>
								</g>)
							})}
						</g>
					</svg>
				</div>
			</div>
		);
	}
}
