'use strict';

import m from 'mithril';
import Pikaday from 'pikaday';
import 'pikaday/css/pikaday.css';
import './datepicker.css';
import { format } from 'date-fns'
const now = new Date();

const startOfWeek = window.koko_analytics.start_of_week;
const i18n = window.koko_analytics.i18n;

// TODO: Add arrow keys for quickly browsing to next period
function Component(vnode) {
    let startDate = new Date(vnode.attrs.startDate);
    let endDate = new Date(vnode.attrs.endDate);
    let picking = false;
    let open = false;
    let datepicker;

    function toggle() {
    	open = !open;
		m.redraw();
	}

	function maybeClose(evt) {
    	if (!open) {
    		return;
		}

    	for(let i = evt.target; i !== null; i = i.parentNode) {
    		if (typeof(i.className) === "string" && (i.className.indexOf("date-picker-ui") > -1 || i.className.indexOf("date-label") > -1)) {
    			return;
			}
		}

    	toggle();
    }

    function setPeriod(p) {
    	return function(evt){
    		let d;
    		evt.preventDefault();

    		switch(p) {
				case 'this_week':
					d = now.getDate() - now.getDay() + options.startOfWeek;
					if (now.getDay() < options.startOfWeek) {
						d = d - 7;
					}

					startDate = new Date(now.getFullYear(), now.getMonth(), d, 0, 0, 0);
					endDate = new Date(now.getFullYear(), startDate.getMonth(), startDate.getDate() + 6, 23, 59, 59);
					break;

				case 'last_week':
					d = now.getDate() - now.getDay() + options.startOfWeek - 7;
					if (now.getDay() < options.startOfWeek) {
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
			datepicker.setStartRange(startDate);
			datepicker.setEndRange(endDate);
			datepicker.gotoDate(endDate);

			// update app state
			vnode.attrs.onUpdate(startDate, endDate);
			m.redraw();
		}
	}

    return {
        oncreate: (vnode) => {
        	document.body.addEventListener('click', maybeClose);

            datepicker = new Pikaday({
				field: document.getElementById('start-date-input'),
				bound: false,
				firstDay: startOfWeek,
                onSelect: function(date) {
					if (!picking || startDate === null || date < startDate) {
						startDate = date;
						endDate = null;
						this.setStartRange(startDate);
						this.setEndRange(null);
					} else {
						endDate = date;
						this.setEndRange(endDate);
					}

					picking = !picking;
					this.draw();

					if (startDate && endDate) {
						vnode.attrs.onUpdate(startDate, endDate);
					}
                },
				container: document.getElementById('date-picker'),
            });
        },
        view: () => (
            <div className="date-nav">
				<div onclick={toggle} className="date-label"><span>{format(startDate, 'MMM d, yyyy')}</span> &mdash; <span>{format(endDate, "MMM d, yyyy")}</span></div>
				<div className="date-picker-ui" style={{display: open ? '' : 'none'}}>
					<div className="date-presets">
						<strong>{i18n['Date range']}</strong>
						<a href="" onclick={setPeriod('this_week')}>{i18n['This week']}</a>
						<a href="" onclick={setPeriod('last_week')}>{i18n['Last week']}</a>
						<a href="" onclick={setPeriod('this_month')}>{i18n['This month']}</a>
						<a href="" onclick={setPeriod('last_month')}>{i18n['Last month']}</a>
						<a href="" onclick={setPeriod('this_year')}>{i18n['This year']}</a>
						<a href="" onclick={setPeriod('last_year')}>{i18n['Last year']}</a>
					</div>
					<div id="date-picker" className="date-picker"> </div>
				</div>
				<input type="hidden" id="start-date-input" />
			</div>
        )
    }
}

export default Component;
