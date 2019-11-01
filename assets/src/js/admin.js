'use strict';

import './admin.css';

import m from 'mithril';
import Chart from './components/chart.js';
import Datepicker from './components/datepicker.js';
import Totals from './components/totals.js';
import TopPosts from './components/top-posts.js';
import TopReferrers from './components/top-referrers.js';
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
					<Totals startDate={startDate} endDate={endDate} />
					<Chart startDate={startDate} endDate={endDate} />
					<div className={"row"}>
						<TopPosts startDate={startDate} endDate={endDate} />
						<TopReferrers startDate={startDate} endDate={endDate} />
					</div>
				</main>
			)
		}
    }
}

m.mount(document.getElementById('koko-analytics-mount'), App);
