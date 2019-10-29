'use strict';

import Chart from './components/chart.js';
import Datepicker from './components/datepicker.js';
import m from 'mithril';

const now = new Date();

function App() {
	let startDate = new Date(now.getFullYear(), now.getMonth(), 1, 0, 0, 0);
	let endDate = new Date(now.getFullYear(), now.getMonth() + 1, 0, 23, 59, 59);

	function setDates(s, e) {
		if (s !== startDate) {
			startDate = s;
		}

		if (e !== endDate) {
			endDate = e;
		}

		m.redraw();
	}

    return {
        view() {
        	return (
				<main>
					<Datepicker startDate={startDate} endDate={endDate} onUpdate={setDates} />
					<Chart startDate={startDate} endDate={endDate} />
				</main>
			)
		}
    }
}

m.mount(document.getElementById('aaa-mount'), App);
