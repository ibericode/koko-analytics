'use strict';

import React from 'react'
import Chart from './chart.js';
import Datepicker from './datepicker.js';
import Totals from './totals.js';
import TopPosts from './top-posts.js';
import TopReferrers from './top-referrers.js';
import Nav from './nav.js';

const now = new Date();
const formatDate = (d) => `${d.getFullYear()}-${d.getMonth()+1}-${d.getDate()}`;
function parseUrlParams(str) {
	let params = {},
		match,
		matches =  str.split("&");

	for(let i=0; i<matches.length; i++) {
		match = matches[i].split('=');
		params[match[0]] = decodeURIComponent(match[1]);
	}

	return params;
}

export default class Dashboard extends React.Component {

	constructor(props) {
		super(props);

		this.state = {
			startDate: new Date(now.getFullYear(), now.getMonth(), 1, 0, 0, 0),
			endDate: new Date(now.getFullYear(), now.getMonth() + 1, 0, 23, 59, 59)
		};

		this.setDates = this.setDates.bind(this);
	}

	componentDidMount() {
		this.unlisten = this.props.history.listen((location, action) => {
			if (action === 'POP') {
				this.setDatesFromLocation(location.search.substring(1))
			}
		});

		let searchPos = window.location.hash.indexOf('?');
		let queryStr = window.location.hash.substring(searchPos + 1);
		this.setDatesFromLocation(queryStr);
	}

	componentWillUnmount() {
		this.unlisten();
	}

	setDatesFromLocation(queryStr) {
		if (!queryStr) {
			return;
		}

		const params = parseUrlParams(queryStr);
		if (!params.start_date || !params.end_date) {
			return;
		}

		this.setState({
			startDate: new Date(params.start_date),
			endDate: new Date(params.end_date + " 23:59:59"),
		});
	}

	setDates(startDate, endDate) {
		if (startDate.getTime() ===  this.state.startDate.getTime() && endDate.getTime() === this.state.endDate.getTime()) {
			return;
		}

		this.setState({startDate, endDate});
		this.props.history.push(`/?start_date=${formatDate(startDate)}&end_date=${formatDate(endDate)}`);
	}

	render() {
		let {startDate, endDate} = this.state;
		return (
			<main>
				<div>
					<div className={"grid"}>
						<div style={{ gridColumn: 'span 4'}}>
							<Datepicker startDate={startDate} endDate={endDate} onUpdate={this.setDates} />
						</div>
						<Nav />
					</div>
					<Totals startDate={startDate} endDate={endDate} />
					<Chart startDate={startDate} endDate={endDate} />
					<div className={"grid"}>
						<TopPosts startDate={startDate} endDate={endDate} />
						<TopReferrers startDate={startDate} endDate={endDate} />
					</div>
				</div>
			</main>
		)
	}
}

