import { h } from 'preact'
import { Link } from 'preact-router/match'
import { __ } from '@wordpress/i18n'

export default function Nav () {
  // do not show navigation if user can not access settings anyway
  if (!window.koko_analytics.showSettings) {
    return null
  }

  return (
    <div className='two nav'>
      <ul className='subsubsub'>
        <li><Link href='/' exact activeClassName='current'>{__('Stats', 'koko-analytics')}</Link> | </li>
        <li><Link href='/settings' activeClassName='current'>{__('Settings', 'koko-analytics')}</Link></li>
      </ul>
    </div>
  )
}
