'use strict';

import '../sass/admin.scss';

import React, { Component } from 'react'
import ReactDOM from 'react-dom';

import Chart from './components/chart.js';
import Datepicker from './components/datepicker.js';
import Totals from './components/totals.js';
import TopPosts from './components/top-posts.js';
import TopReferrers from './components/top-referrers.js';
import Settings from './components/settings.js';
const now = new Date();

class App extends React.Component {

	constructor(props) {
		super(props);

		this.state = {
			startDate: new Date(now.getFullYear(), now.getMonth(), 1, 0, 0, 0),
			endDate: new Date(now.getFullYear(), now.getMonth() + 1, 0, 23, 59, 59)
		};

		this.setDates = this.setDates.bind(this);
	}

	setDates(startDate, endDate) {
		if (startDate.getTime() ===  this.state.startDate.getTime() && endDate.getTime() === this.state.endDate.getTime()) {
			return;
		}

		this.setState({startDate, endDate});
		// window.location.hash = `!/?start_date=${s.getFullYear()}-${s.getMonth()+1}-${s.getDate()}&end_date=${e.getFullYear()}-${e.getMonth()+1}-${e.getDate()}`;
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
						<div style={{ gridColumn: 'span 2'}}>
							<ul className="nav subsubsub">
								<li><a href={"#!/"} className="current">Stats</a> | </li>
								<li><a href={"#!/settings"}>Settings</a></li>
							</ul>
						</div>
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

ReactDOM.render(<App />, document.getElementById('koko-analytics-mount'));
