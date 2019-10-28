'use strict';

import Chart from './components/chart.js';
import Datepicker from './components/datepicker.js';

import m from 'mithril';

function App() {
	let	startDate = null;
	let	endDate = null;

	function setDates(s, e) {
		startDate = s;
		endDate = e;
		m.redraw();
	}

    return {
        view() {
        	console.log(startDate, endDate);
        	return (
				<main>
					<Datepicker onUpdate={setDates} />
					<Chart startDate={startDate} endDate={startDate} />
				</main>
			)
		}
    }
}

m.mount(document.getElementById('aaa-mount'), App);
