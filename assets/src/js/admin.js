'use strict';

import '././sass/admin.scss';

import m from 'mithril';
import Chart from './components/chart.js';
import Datepicker from './components/datepicker.js';
import Totals from './components/totals.js';
import TopPosts from './components/top-posts.js';
import TopReferrers from './components/top-referrers.js';
import Settings from './components/settings.js';
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

					<div>
						<div className={"grid"}>
							<div style={"grid-column: span 4;"}>
								<Datepicker startDate={startDate} endDate={endDate} onUpdate={setDates} />
							</div>
							<div style="grid-column: span 2;">
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
}

m.route(document.getElementById('koko-analytics-mount'), "/", {
	"/": App,
	"/settings": Settings
});
//m.mount(document.getElementById('koko-analytics-mount'), App);
