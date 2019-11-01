'use strict';

import m from 'mithril';
import Pikaday from 'pikaday';
import 'pikaday/css/pikaday.css';
import './datepicker.css';
import { format } from 'date-fns'
const now = new Date();


// TODO: Add arrow keys for quickly browsing to next period
function Component(vnode) {
    let startDate = new Date(vnode.attrs.startDate);
    let endDate = new Date(vnode.attrs.endDate);
    let picking = false;
    let open = false;

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
    		evt.preventDefault();

    		switch(p) {
				case 'this_month':
					startDate = new Date(now.getFullYear(), now.getMonth(), 1, 0, 0, 0);
					endDate = new Date(now.getFullYear(), now.getMonth() + 1, 0, 23, 59, 59);

					break;
				case 'last_month':
					startDate = new Date(now.getFullYear(), now.getMonth() - 1, 1, 0, 0, 0);
					endDate = new Date(now.getFullYear(), now.getMonth(), 0, 23, 59, 59);
					break;

				case 'this_year':
					startDate = new Date(now.getFullYear(), 0, 1, 0, 0, 0);
					endDate = new Date(now.getFullYear(), 12, 0, 23, 59, 59);
					break;

				case 'last_year':
					startDate = new Date(now.getFullYear()-1, 0, 1, 0, 0, 0);
					endDate = new Date(now.getFullYear()-1, 12, 0, 23, 59, 59);
					break;
			}

			vnode.attrs.onUpdate(startDate, endDate);
			m.redraw();
		}
	}

    return {
        oncreate: (vnode) => {
        	document.body.addEventListener('click', maybeClose);

            new Pikaday({
				field: document.getElementById('start-date-input'),
				bound: false,
                maxDate: new Date(now),
                onSelect: function(date) {
					if (!picking || startDate === null || date < startDate) {
						startDate = this.getDate();
						endDate = null;
						this.setStartRange(startDate);
						this.setEndRange(null);
					} else {
						endDate = this.getDate();
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
				<div onclick={toggle} className="date-label">Showing <span>{format(startDate, 'MMM d, yyyy')}</span> &mdash; <span>{format(endDate, "MMM d, yyyy")}</span></div>
				<div className="date-picker-ui" style={{display: open ? '' : 'none'}}>
					<div className="date-presets">
						<strong>Date range</strong>
						<a href="" onclick={setPeriod('this_month')}>This month</a>
						<a href="" onclick={setPeriod('last_month')}>Last month</a>
						<a href="" onclick={setPeriod('this_year')}>This year</a>
						<a href="" onclick={setPeriod('last_year')}>Last year</a>
					</div>
					<div id="date-picker" className="date-picker"> </div>
				</div>
				<input type="hidden" id="start-date-input" />
			</div>
        )
    }
}

export default Component;
