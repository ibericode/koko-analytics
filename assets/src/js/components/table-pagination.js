import React from 'react'
'use strict'

const i18n = window.koko_analytics.i18n

export default class Component extends React.PureComponent {
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

  render (props) {
    const { offset, limit, total } = this.props

    return (
      <div className='pagination'>
        <span
          className={'prev ' + (offset === 0 ? 'disabled' : '')} title={i18n.Previous}
          onClick={this.handleClick('prev')}
        ><span className='dashicons dashicons-arrow-left' />
        </span>
        <span
          className={'next ' + (total < limit ? 'disabled' : '')} title={i18n.Next}
          onClick={this.handleClick('next')}
        ><span className='dashicons dashicons-arrow-right' />
        </span>
      </div>
    )
  }
}
