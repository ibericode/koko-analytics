'use strict'

import '../sass/admin.scss'
import React from 'react'
import ReactDOM from 'react-dom'
import { HashRouter as Router, Switch, withRouter, Route } from 'react-router-dom'
import Dashboard from './components/dashboard'
import Settings from './components/settings'

const Page = () => (
  <Router>
    <Switch>
      <Route path='/settings' exact>
        <Settings />
      </Route>
      <Route path='/' exact>
        {withRouter(Dashboard)}
      </Route>
    </Switch>
  </Router>
)

ReactDOM.render(<Page />, document.getElementById('koko-analytics-mount'))
