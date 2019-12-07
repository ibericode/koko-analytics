'use strict';

import React from 'react';
import { format } from 'date-fns'
import api from '../util/api.js';
import '../../sass/chart.scss';

export default class Component extends React.PureComponent {

	constructor(props) {
		super(props);

		this.state = {
			dataset: {},
		};

		this.base = React.createRef();
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
		this.setState({dataset});

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
			});

			this.setState({dataset});
		});
	}

	componentDidMount() {
		console.log(this.base.current);
	}

	componentDidUpdate(prevProps, prevState, snapshot) {
		if (this.props.startDate.getTime() === prevProps.startDate.getTime() && this.props.endDate.getTime() === prevProps.endDate.getTime()) {
			return;
		}

		this.updateChart();
	}

	render() {
		const computedHeight = Math.max(240, Math.min(window.innerHeight / 3, window.innerWidth / 2, 360));
		return (
			<div className="box">
				<div className={"chart-container"} style={{ height: this.props.height || computedHeight}} ref={this.base}>
				</div>
			</div>
		);
	}
}
