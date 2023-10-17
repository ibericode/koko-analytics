import React, { Fragment } from 'react'
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
  // render nothing if we have less items than the limit & are on 1st page
  if (total < limit && offset === 0) {
    return null;
  }

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

  let fill = Array.from(Array(limit - total).keys());
  return (
    <Fragment>
    {fill.map((n) => (
        <div key={n} className={'ka-topx--row ka-fade'}>&nbsp;</div>
      ))}
    <div className='ka-pagination'>
        <span className={'ka-pagination--prev ' + (offset === 0 ? 'disabled' : '')} onClick={prev}><span className='dashicons dashicons-arrow-left' /> {__('Previous', 'koko-analytics')}</span>
      <span className={'ka-pagination--next ' + (total < limit ? 'disabled' : '')} onClick={next}
      >{__('Next', 'koko-analytics')} <span className='dashicons dashicons-arrow-right'/></span>
    </div>
    </Fragment>
  )
}
