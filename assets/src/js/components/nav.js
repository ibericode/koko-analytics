import { __ } from '@wordpress/i18n'
import React from "react"

export default function Nav (props) {
  // do not show navigation if user can not access settings anyway
  if (!window.koko_analytics.showSettings) {
    return null
  }

  return (
    <div className='two nav'>
      <ul className='subsubsub'>
        <li><a href='#/' className={props.history.location.pathname === '/' ? 'current': ''}>{__('Stats', 'koko-analytics')}</a> | </li>
        <li><a href='#/settings' className={props.history.location.pathname === '/settings' ? 'current': ''}>{__('Settings', 'koko-analytics')}</a></li>
      </ul>
    </div>
  )
}
