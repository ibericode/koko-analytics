import React, {useState} from 'react'
import {request} from '../util/api'
import { __ } from '@wordpress/i18n'

export default function ButtonReset() {
  const [saving, setSaving] = useState(false)
  const [buttonText, setButtonText] = useState(__('Reset Statistics', 'koko-analytics'))

  function click(evt) {
    if (window.confirm(__('Are you sure you want to reset all of your statistics? This can not be undone.', 'koko-analytics')) !== true) {
      return
    }

    evt.preventDefault()
    setSaving(true)
    setButtonText(__('Resetting - please wait', 'koko-analytics'))

    request('/reset', {
      method: 'POST',
      body: {}
    }).then(() => {
      setButtonText(__('Done!', 'koko-analytics') + ' ' + __('Page will reload in a few seconds.', 'koko-analytics'))

      // reload page after 4 seconds
      setTimeout(() => {
        location.reload()
      }, 4000)
    }).finally(() => {
      setTimeout(() => {
        setSaving(false)
        setButtonText(__('Reset Statistics', 'koko-analytics'))
      }, 4000)
    })
  }

  return (
    <button
      type='button' className='button button-primary' onClick={click}
      disabled={saving}
    >{buttonText}
    </button>
  )
}
