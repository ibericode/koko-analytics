'use strict';

import m from 'mithril';
import Pikaday from 'pikaday';
import 'pikaday/css/pikaday.css';
import './datepicker.css';
import { format } from 'date-fns'
const now = new Date();

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
						<a href="">This month</a>
						<a href="">Last month</a>
						<a href="">This year</a>
						<a href="">Last year</a>
						<a href="">All time</a>
					</div>
					<div id="date-picker" className="date-picker"> </div>
				</div>
				<input type="hidden" id="start-date-input" />
			</div>
        )
    }
}

export default Component;
