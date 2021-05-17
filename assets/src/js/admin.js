import '../sass/admin.scss'
import { render, h } from 'preact'
import Router from 'preact-router'
import Dashboard from './components/dashboard'
import Settings from './components/settings'
import { createHashHistory } from 'history'
const history = createHashHistory()

function Page () {
  return (
    <Router history={history}>
      <Dashboard path={'/'} history={history} />
      <Settings path={'/settings'} />
    </Router>
  )
}

render(<Page />, document.getElementById('koko-analytics-mount'))
