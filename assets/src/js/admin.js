'use strict';

import Chart from './components/chart.js';
import Datepicker from './components/datepicker.js';

import m from 'mithril';

function App() {
    return {
        view: () => (
            <main>
                <h1>Hello world</h1>
                <Datepicker />
                <Chart />
            </main>
        )
    }
}

m.render(document.getElementById('aaa-mount'), <App />);