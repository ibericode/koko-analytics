'use strict'

import { h, Component } from 'preact'
import PropTypes from 'prop-types'
import Pikaday from 'pikaday'
import 'pikaday/css/pikaday.css'
import '../../sass/datepicker.scss'
import format from 'date-fns/format'
import addDays from 'date-fns/addDays'
import { isLastDayOfMonth } from '../util/dates.js'
const startOfWeek = window.koko_analytics.start_of_week
const i18n = window.koko_analytics.i18n

export default class Datepicker extends Component {
  constructor (props) {
    super(props)

    this.state = {
      open: false,
      picking: false,
      startDate: new Date(props.startDate.getTime()),
      endDate: new Date(props.endDate.getTime())
    }
    this.datepicker = null
    this.datepickerContainer = null
    this.toggle = this.toggle.bind(this)
    this.maybeClose = this.maybeClose.bind(this)
    this.setPeriod = this.setPeriod.bind(this)
    this.onKeydown = this.onKeydown.bind(this)
  }

  componentDidMount () {
    document.body.addEventListener('click', this.maybeClose)
    document.body.addEventListener('keydown', this.onKeydown)

    const datepicker = this.datepicker = new Pikaday({
      field: document.getElementById('start-date-input'),
      bound: false,
      firstDay: startOfWeek,
      numberOfMonths: window.innerWidth > 680 ? 2 : 1,
      enableSelectionDaysInNextAndPreviousMonths: true,
      showDaysInNextAndPreviousMonths: true,
      keyboardInput: false,
      onSelect: (date) => {
        let newState = {
          picking: !this.state.picking
        }

        if (!this.state.picking || this.state.startDate === null || date < this.state.startDate) {
          date.setHours(0, 0, 0)
          newState = { ...newState, startDate: date, endDate: null }
          datepicker.setStartRange(date)
          datepicker.setEndRange(null)
        } else {
          date.setHours(23, 59, 59)
          newState = { ...newState, endDate: date }
          datepicker.setEndRange(date)
          this.props.onUpdate(this.state.startDate, date)
        }

        this.setState(newState)
        datepicker.draw()
      },
      container: this.datepickerContainer
    })

    this.datepicker.setStartRange(this.state.startDate)
    this.datepicker.setEndRange(this.state.endDate)
    this.datepicker.gotoDate(this.state.endDate)
  }

  componentWillUnmount () {
    this.datepicker.destroy()
    document.body.removeEventListener('click', this.maybeClose)
    document.body.removeEventListener('keydown', this.onKeydown)
  }

  toggle () {
    this.setState({ open: !this.state.open })
  }

  maybeClose (evt) {
    if (!this.state.open) {
      return
    }

    for (let i = evt.target; i !== null; i = i.parentNode) {
      if (typeof (i.className) === 'string' && (i.className.indexOf('date-picker-ui') > -1 || i.className.indexOf('date-label') > -1)) {
        return
      }
    }

    this.toggle()
  }

  setPeriod (p) {
    return evt => {
      evt.preventDefault()

      const now = new Date()
      let d, startDate, endDate

      switch (p) {
        case 'today':
          startDate = new Date(now.getFullYear(), now.getMonth(), now.getDate(), 0, 0, 0)
          endDate = new Date(now.getFullYear(), now.getMonth(), now.getDate(), 23, 59, 59)
          break

        case 'this_week':
          d = now.getDate() - now.getDay() + startOfWeek
          if (now.getDay() < startOfWeek) {
            d = d - 7
          }

          startDate = new Date(now.getFullYear(), now.getMonth(), d, 0, 0, 0)
          endDate = new Date(now.getFullYear(), startDate.getMonth(), startDate.getDate() + 6, 23, 59, 59)
          break

        case 'last_28_days':
          startDate = new Date(now.getFullYear(), now.getMonth(), now.getDate() - 28, 0, 0, 0)
          endDate = new Date(now.getFullYear(), now.getMonth(), now.getDate(), 23, 59, 59)
          break

        case 'this_month':
          startDate = new Date(now.getFullYear(), now.getMonth(), 1, 0, 0, 0)
          endDate = new Date(startDate.getFullYear(), startDate.getMonth() + 1, 0, 23, 59, 59)
          break

        case 'this_quarter':
          startDate = new Date(now.getFullYear(), (Math.ceil((now.getMonth() + 1) / 3) - 1) * 3, 1, 0, 0, 0)
          endDate = new Date(startDate.getFullYear(), startDate.getMonth() + 3, 0, 23, 59, 59)
          break

        case 'this_year':
          startDate = new Date(now.getFullYear(), 0, 1, 0, 0, 0)
          endDate = new Date(startDate.getFullYear(), 12, 0, 23, 59, 59)
          break
      }

      this.setDates(startDate, endDate)
    }
  }

  setDates (startDate, endDate) {
    this.datepicker.setStartRange(startDate)
    this.datepicker.setEndRange(endDate)
    this.datepicker.gotoDate(endDate)
    this.setState({ startDate, endDate })
    this.props.onUpdate(startDate, endDate)
  }

  onKeydown (evt) {
    if (evt.ctrlKey && (evt.key === 'ArrowLeft' || evt.key === 'ArrowRight')) {
      this.quickNav(evt.key === 'ArrowLeft' ? 'prev' : 'next')
    }
  }

  onQuickNavClick (dir) {
    return (evt) => {
      evt.preventDefault()
      this.quickNav(dir)
    }
  }

  quickNav (dir) {
    let { startDate, endDate } = this.state
    const diff = (endDate.getTime() - startDate.getTime()) / 1000
    const diffInDays = Math.round(diff / 86400)
    const modifier = dir === 'prev' ? -1 : 1
    const cycleMonths = startDate.getDate() === 1 && isLastDayOfMonth(endDate)

    if (cycleMonths) {
      const monthsDiff = endDate.getMonth() - startDate.getMonth() + 1
      startDate = new Date(startDate.getFullYear(), startDate.getMonth() + (monthsDiff * modifier), 1, 0, 0, 0)
      endDate = new Date(endDate.getFullYear(), endDate.getMonth() + (monthsDiff * modifier) + 1, 0, 23, 59, 59)
    } else {
      startDate = addDays(startDate, diffInDays * modifier)
      endDate = addDays(endDate, diffInDays * modifier)
    }

    this.setDates(startDate, endDate)
  }

  render (props, state) {
    const { open } = state
    const { startDate, endDate } = props
    return (
      <div className='date-nav'>
        <div>
          <div className={'date-label'} onClick={this.toggle}>
            <span className='dashicons dashicons-calendar-alt' />
            <span>{format(startDate, 'MMM d, yyyy')}</span>
            <span> &mdash; </span>
            <span>{format(endDate, 'MMM d, yyyy')}</span>
          </div>
        </div>
        <div className='date-picker-ui' style={{ display: open ? '' : 'none' }}>
          <div className='date-quicknav cf'>
            <span onClick={this.onQuickNavClick('prev')} className='prev dashicons dashicons-arrow-left' title={i18n.Previous} />
            <span className='date'>
              <span>{format(startDate, 'MMM d, yyyy')}</span>
              <span> &mdash; </span>
              <span>{format(endDate, 'MMM d, yyyy')}</span>
            </span>
            <span onClick={this.onQuickNavClick('next')} className='next dashicons dashicons-arrow-right' title={i18n.Next} />
          </div>
          <div className='flex'>
            <div className='date-presets'>
              <strong>{i18n['Date presets']}</strong>
              <a href='' onClick={this.setPeriod('last_28_days')}>{i18n['Last 28 days']}</a>
              <a href='' onClick={this.setPeriod('today')}>{i18n.Today}</a>
              <a href='' onClick={this.setPeriod('this_week')}>{i18n['This week']}</a>
              <a href='' onClick={this.setPeriod('this_month')}>{i18n['This month']}</a>
              <a href='' onClick={this.setPeriod('this_quarter')}>{i18n['This quarter']}</a>
              <a href='' onClick={this.setPeriod('this_year')}>{i18n['This year']}</a>
            </div>
            <div className='date-picker'>
              <div ref={el => {
                this.datepickerContainer = el
              }} />
            </div>
          </div>
        </div>

        <input type='hidden' id='start-date-input' />
      </div>
    )
  }
}

Datepicker.propTypes = {
  startDate: PropTypes.instanceOf(Date).isRequired,
  endDate: PropTypes.instanceOf(Date).isRequired,
  onUpdate: PropTypes.func.isRequired
}
