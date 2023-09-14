import React from 'react'
import { __ } from '@wordpress/i18n'

/**
 * @param {number} offset
 * @param {number} limit
 * @param {number} total
 * @param {function} onUpdate
 * @returns {JSX.Element}
 * @constructor
 */
export default function Pagination({offset, limit, total, onUpdate}) {
  function prev() {
    if (offset === 0) {
      return
    }

    return onUpdate(Math.max(0, offset - limit))
  }

  function next() {
    if (total < limit) {
      return
    }

    return onUpdate(offset + limit)
  }

  return (
    <div className='pagination'>
        <span
          className={'prev ' + (offset === 0 ? 'disabled' : '')} title={__('Previous', 'koko-analytics')}
          onClick={prev}
        ><span className='dashicons dashicons-arrow-left' />
        </span>
      <span
        className={'next ' + (total < limit ? 'disabled' : '')} title={__('Next', 'koko-analytics')}
        onClick={next}
      ><span className='dashicons dashicons-arrow-right' />
        </span>
    </div>
  )
}
