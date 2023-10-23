import Dashboard from './components/dashboard'
import './globals.js'
import React from 'react'
// eslint-disable-next-line react/no-deprecated
import {createRoot, render} from 'react-dom'
import "../css/dashboard.css"
import Datepicker from './datepicker.js';

document.addEventListener('DOMContentLoaded', () => {



  const el = document.getElementById('koko-analytics-mount')
  if (typeof createRoot === 'function') {
    createRoot(el).render(<Dashboard />)
  } else {
    render(<Dashboard />, el)
  }
})
