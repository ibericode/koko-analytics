import '../sass/admin.scss'
import Dashboard from './components/dashboard'
import Settings from './components/settings'
import { createHashHistory } from 'history'
const history = createHashHistory()
import './globals.js'
import React, {useEffect, useState} from 'react'
import {createRoot} from 'react-dom'

function Page() {
  const [path, setPath] = useState(history.location.pathname)
  useEffect(() => {
    return history.listen(({location}) => setPath(location.pathname))
  },[])

  return (
    path === '/' ? <Dashboard history={history} /> : <Settings history={history} />
  )
}

document.addEventListener('DOMContentLoaded', () => {
  createRoot(document.getElementById('koko-analytics-mount')).render(<Page />)
})
