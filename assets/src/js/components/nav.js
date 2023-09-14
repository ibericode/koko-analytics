import { __ } from '@wordpress/i18n'
import React from "react"
const {showSettings} = window.koko_analytics

export default function Nav ({ history }) {
  // do not show navigation if user can not access settings anyway
  if (!showSettings) {
    return null
  }

  return (
    <div className='two nav'>
      <ul className='subsubsub'>
        <li><a href='#/' className={history.location.pathname === '/' ? 'current': ''}>{__('Stats', 'koko-analytics')}</a> | </li>
        <li><a href='#/settings' className={history.location.pathname === '/settings' ? 'current': ''}>{__('Settings', 'koko-analytics')}</a></li>
      </ul>
    </div>
  )
}
