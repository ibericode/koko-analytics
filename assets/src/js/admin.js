import '../sass/admin.scss'
import Router from 'preact-router'
import Dashboard from './components/dashboard'
import Settings from './components/settings'
import { createHashHistory } from 'history'
const history = createHashHistory()

import * as preact from 'preact'
import api from './util/api.js'
const h = preact.h;
import Pagination from './components/table-pagination'
window.koko_analytics.api = api;
window.koko_analytics.components = {
  preact,
  Pagination,
};
let blockComponents = [];
let settingComponents = [];

window.koko_analytics.registerBlockComponent = function(c) {
  blockComponents.push(c)
}
window.koko_analytics.registerSettingsComponent = function(c) {
  settingComponents.push(c)
}

function Page () {
  return (
    <Router history={history}>
      <Dashboard path={'/'} history={history} />
      <Settings path={'/settings'} />
    </Router>
  )
}

preact.render(<Page />, document.getElementById('koko-analytics-mount'))
