'use strict';

import '../sass/admin.scss';

import m from 'mithril';
import Chart from './components/chart.js';
import Datepicker from './components/datepicker.js';
import Totals from './components/totals.js';
import TopPosts from './components/top-posts.js';
import TopReferrers from './components/top-referrers.js';
import Settings from './components/settings.js';

function App(vnode) {
	let now = new Date();
	let startDate = vnode.attrs.startDate || new Date(now.getFullYear(), now.getMonth(), 1, 0, 0, 0);
	let endDate = vnode.attrs.endDate || new Date(now.getFullYear(), now.getMonth() + 1, 0, 23, 59, 59);

	function setDates(s, e) {
		let dirty = false;

		if (s.getTime() !== startDate.getTime()) {
			startDate = new Date(s);
			dirty = true;
		}

		if (e.getTime() !== endDate.getTime()) {
			endDate = new Date(e);
			dirty = true;
		}

		if (dirty) {
			m.redraw();
		}
	}

    return {
        view(vnode) {
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
