import Dashboard from './components/dashboard'
import { createHashHistory } from 'history'
const history = createHashHistory()
import './globals.js'
import React from 'react'
import {createRoot} from 'react-dom'
import "../css/dashboard.css"

document.addEventListener('DOMContentLoaded', () => {
  createRoot(document.getElementById('koko-analytics-mount')).render(<Dashboard history={history} />)
})
