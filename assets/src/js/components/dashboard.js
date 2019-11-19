'use strict';

import React from 'react'
import Chart from './chart.js';
import Datepicker from './datepicker.js';
import Totals from './totals.js';
import TopPosts from './top-posts.js';
import TopReferrers from './top-referrers.js';
import {NavLink} from "react-router-dom";
const i18n = window.koko_analytics.i18n;

const now = new Date();


export default class Dashboard extends React.Component {

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
								<li><NavLink to={"/"} exact activeClassName={"current"}>{i18n['Stats']}</NavLink></li>
								<li><NavLink to={"/settings"} activeClassName={"current"}>{i18n['Settings']}</NavLink></li>
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

