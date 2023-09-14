import React, {Component} from 'react'
import { __ } from '@wordpress/i18n'

export default class Pagination extends Component {
  handleClick (direction) {
    const { offset, limit, total } = this.props

    return () => {
      if ((direction === 'prev' && offset === 0) || (direction === 'next' && total < limit)) {
        return
      }

      const mod = direction === 'prev' ? -1 : 1
      const newOffset = Math.max(0, offset + limit * mod)

      this.props.onUpdate(newOffset)
    }
  }

  render () {
    const { offset, limit, total } = this.props

    return (
      <div className='pagination'>
        <span
          className={'prev ' + (offset === 0 ? 'disabled' : '')} title={__('Previous', 'koko-analytics')}
          onClick={this.handleClick('prev')}
        ><span className='dashicons dashicons-arrow-left' />
        </span>
        <span
          className={'next ' + (total < limit ? 'disabled' : '')} title={__('Next', 'koko-analytics')}
          onClick={this.handleClick('next')}
        ><span className='dashicons dashicons-arrow-right' />
        </span>
      </div>
    )
  }
}
