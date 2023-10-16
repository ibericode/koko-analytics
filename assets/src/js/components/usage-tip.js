import { __ } from '@wordpress/i18n'
import React from 'react'

let tips = [
  __('Tip: use the arrow keys on your keyboard to cycle through date ranges.', 'koko-analytics'),
  __('Tip: you can set a default date range in the plugin settings.', 'koko-analytics'),
  __('Tip: did you know there is a widget, shortcode and template function to <a href="%1s">show a list of the most viewed posts</a> on your site?', 'koko-analytics').replace('%1s', 'https://www.kokoanalytics.com/kb/showing-most-viewed-posts-on-your-wordpress-site/'),
  __('Tip: Use <a href="%1s">Koko Analytics Pro</a> to set-up custom event tracking.', 'koko-analytics').replace('%1s', 'https://www.kokoanalytics.com/pricing/'),
]

export function UsageTip() {
  const idx = Math.floor(Math.random() * tips.length)
  const randomTip = tips[idx];
  return (
    <div className={'ka-margin-s'}>
      <p className={'description ka-right'} dangerouslySetInnerHTML={{__html: randomTip}}></p>
    </div>
  )
}
