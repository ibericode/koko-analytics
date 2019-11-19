'use strict';

import '../sass/admin.scss';
import ReactDOM from 'react-dom';
import {HashRouter as Router, Route} from 'react-router-dom';
import Dashboard from "./components/dashboard";
import Settings from './components/settings';

const Page = () => (
	<Router>
		<Route path="/settings" exact>
			<Settings />
		</Route>
		<Route path="/" exact>
			<Dashboard />
		</Route>
	</Router>
);

ReactDOM.render(<Page />, document.getElementById('koko-analytics-mount'));
