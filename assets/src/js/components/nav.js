'use strict'

import { h } from 'preact'
import { Link } from 'preact-router/match'

const i18n = window.koko_analytics.i18n

export default function Nav () {
  // do not show navigation if user can not access settings anyway
  if (window.koko_analytics.showSettings === false) {
    return null
  }

  return (
    <div className='two nav'>
      <ul className='subsubsub'>
        <li><Link href='/' exact activeClassName='current'>{i18n.Stats}</Link> | </li>
        <li><Link href='/settings' activeClassName='current'>{i18n.Settings}</Link></li>
      </ul>
    </div>
  )
}
