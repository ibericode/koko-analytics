'use strict';

import m from 'mithril';
import Pikaday from 'pikaday';
import 'pikaday/css/pikaday.css';
import './datepicker.css';
const now = new Date();

function Component(vnode) {
    let startDate, endDate, picking = false;

    return {
        oncreate: (vnode) => {
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
                <input type="hidden" id="start-date-input" />
				<div className="row">
					<div className="date-presets">
						<a href="">This month</a>
						<a href="">Last month</a>
						<a href="">This year</a>
						<a href="">Last year</a>
						<a href="">All time</a>
					</div>
					<div id="date-picker" style="display: flex;"> </div>
				</div>
            </div>
        )
    }
}

export default Component;
