import Dashboard from './components/dashboard'
import './globals.js'
import React from 'react'
// eslint-disable-next-line react/no-deprecated
import {createRoot, render} from 'react-dom'
import "../css/dashboard.css"

document.addEventListener('DOMContentLoaded', () => {
  if (typeof createRoot === 'function') {
    createRoot(document.getElementById('koko-analytics-mount')).render(<Dashboard />)
  } else {
    render(<Dashboard />, document.getElementById('koko-analytics-mount'))
  }
})
