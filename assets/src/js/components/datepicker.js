'use strict';

import React from 'react';
import Pikaday from 'pikaday';
import 'pikaday/css/pikaday.css';
import '../../sass/datepicker.scss';
import { format } from 'date-fns'
const startOfWeek = window.koko_analytics.start_of_week;
const i18n = window.koko_analytics.i18n;

// TODO: Add arrow keys for quickly browsing to next period
export default class Datepicker extends React.Component {
	constructor(props) {
		super(props);

		this.state = {
			open: false,
			picking: false,
			startDate: new Date(props.startDate),
			endDate: new Date(props.endDate),
		};
		this.datepicker = null;

		this.toggle = this.toggle.bind(this);
		this.maybeClose = this.maybeClose.bind(this);
		this.setPeriod = this.setPeriod.bind(this);
		this.initPikaday = this.initPikaday.bind(this);
	}

    toggle() {
		this.setState({open: !this.state.open});
	}

	maybeClose(evt) {
    	if (!this.state.open) {
    		return;
		}

    	for(let i = evt.target; i !== null; i = i.parentNode) {
    		if (typeof(i.className) === "string" && (i.className.indexOf("date-picker-ui") > -1 || i.className.indexOf("date-label") > -1)) {
    			return;
			}
		}

    	this.toggle();
    }

    setPeriod(p) {
		return evt => {
			evt.preventDefault();

			const now = new Date();
    		let d, startDate, endDate;

    		switch(p) {
				case 'this_week':
					d = now.getDate() - now.getDay() + startOfWeek;
					if (now.getDay() < startOfWeek) {
						d = d - 7;
					}

					startDate = new Date(now.getFullYear(), now.getMonth(), d, 0, 0, 0);
					endDate = new Date(now.getFullYear(), startDate.getMonth(), startDate.getDate() + 6, 23, 59, 59);
					break;

				case 'last_week':
					d = now.getDate() - now.getDay() + startOfWeek - 7;
					if (now.getDay() < startOfWeek) {
						d = d - 7;
					}

					startDate = new Date(now.getFullYear(), now.getMonth(), d, 0, 0, 0);
					endDate = new Date(startDate.getFullYear(), startDate.getMonth(), startDate.getDate() + 6, 23, 59, 59);
					break;

				case 'this_month':
					startDate = new Date(now.getFullYear(), now.getMonth(), 1, 0, 0, 0);
					endDate = new Date(startDate.getFullYear(), startDate.getMonth() + 1, 0, 23, 59, 59);
					break;

				case 'last_month':
					startDate = new Date(now.getFullYear(), now.getMonth() - 1, 1, 0, 0, 0);
					endDate = new Date(startDate.getFullYear(), startDate.getMonth() + 1, 0, 23, 59, 59);
					break;

				case 'this_year':
					startDate = new Date(now.getFullYear(), 0, 1, 0, 0, 0);
					endDate = new Date(startDate.getFullYear(), 12, 0, 23, 59, 59);
					break;

				case 'last_year':
					startDate = new Date(now.getFullYear()-1, 0, 1, 0, 0, 0);
					endDate = new Date(startDate.getFullYear(), 12, 0, 23, 59, 59);
					break;
			}

			// update datepicker to match preset
			this.datepicker.setStartRange(startDate);
			this.datepicker.setEndRange(endDate);
			this.datepicker.gotoDate(endDate);

			// update app state
			//this.setState({startDate, endDate});
			this.props.onUpdate(startDate, endDate);
		}
	}

	initPikaday(element) {
		document.body.addEventListener('click', this.maybeClose);
		let c = this;

		this.datepicker = new Pikaday({
			field: document.getElementById('start-date-input'),
			bound: false,
			firstDay: startOfWeek,
			onSelect: function(date) {
				if (!c.state.picking || c.state.startDate === null || date < c.state.startDate) {
					c.setState({startDate: date, endDate: null});
					this.setStartRange(date);
					this.setEndRange(null);
				} else {
					c.setState({endDate: date});
					this.setEndRange(date);
				}

				c.setState({picking: !c.state.picking});
				this.draw();

				if (c.state.startDate && c.state.endDate) {
					c.props.onUpdate(c.state.startDate, c.state.endDate);
				}
			},
			container: element,
		});
	}

	render() {
		let {open, picking} = this.state;
		let {startDate, endDate} = this.props;
		return (
			<div className="date-nav">
				<div onClick={this.toggle} className="date-label"><span className="dashicons dashicons-calendar-alt"></span>
					<span>{format(startDate, 'MMM d, yyyy')}</span> &mdash;
					<span>{format(endDate, "MMM d, yyyy")}</span></div>
				<div className="date-picker-ui" style={{display: open ? '' : 'none'}}>
					<div className="date-presets">
						<strong>{i18n['Date range']}</strong>
						<a href="" onClick={this.setPeriod('this_week')}>{i18n['This week']}</a>
						<a href="" onClick={this.setPeriod('last_week')}>{i18n['Last week']}</a>
						<a href="" onClick={this.setPeriod('this_month')}>{i18n['This month']}</a>
						<a href="" onClick={this.setPeriod('last_month')}>{i18n['Last month']}</a>
						<a href="" onClick={this.setPeriod('this_year')}>{i18n['This year']}</a>
						<a href="" onClick={this.setPeriod('last_year')}>{i18n['Last year']}</a>
					</div>
					<div id="date-picker" className="date-picker" ref={this.initPikaday}></div>
				</div>
				<input type="hidden" id="start-date-input"/>
			</div>
		);
	}
}
