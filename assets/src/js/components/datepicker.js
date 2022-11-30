import { h, Component } from 'preact'
import PropTypes from 'prop-types'
import Pikaday from 'pikaday'
import 'pikaday/css/pikaday.css'
import '../../sass/datepicker.scss'
import format from 'date-fns/format'
import addDays from 'date-fns/addDays'
import datePresets from '../util/date-presets.js'
import { isLastDayOfMonth, parseISO8601 } from '../util/dates.js'
import { __ } from '@wordpress/i18n'
const startOfWeek = parseInt(window.koko_analytics.start_of_week, 10)
const settings = window.koko_analytics.settings

export default class Datepicker extends Component {
  constructor (props) {
    super(props)

    this.state = {
      open: false,
      picking: false,
      preset: settings.default_view,
      startDate: new Date(props.startDate.getTime()),
      endDate: new Date(props.endDate.getTime())
    }
    this.datepicker = null
    this.datepickerContainer = null

    this.toggle = this.toggle.bind(this)
    this.maybeClose = this.maybeClose.bind(this)
    this.setPeriod = this.setPeriod.bind(this)
    this.onKeydown = this.onKeydown.bind(this)
    this.setCustomStartDate = this.setCustomStartDate.bind(this)
    this.setCustomEndDate = this.setCustomEndDate.bind(this)
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
          picking: !this.state.picking,
          preset: 'custom'
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

  setPeriod (key) {
    if (key === 'custom') {
      this.setState({ preset: key })
      return
    }

    const p = datePresets.filter((p) => p.key === key).shift()
    this.setState({ preset: p.key })
    const { startDate, endDate } = p.dates()
    this.setDates(startDate, endDate)
  }

  setDates (startDate, endDate) {
    this.datepicker.setStartRange(startDate)
    this.datepicker.setEndRange(endDate)
    this.datepicker.gotoDate(endDate)
    this.setState({ startDate, endDate })
    this.props.onUpdate(startDate, endDate)
  }

  onKeydown (evt) {
    if (evt.key === 'ArrowLeft' || evt.key === 'ArrowRight') {
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

    this.setState({ preset: 'custom' })
    this.setDates(startDate, endDate)
  }

  setCustomStartDate (evt) {
    const date = parseISO8601(evt.target.value)
    if (date !== null) {
      date.setHours(0, 0, 0)
      this.setDates(date, this.state.endDate)
    }
  }

  setCustomEndDate (evt) {
    const date = parseISO8601(evt.target.value)
    if (date !== null) {
      date.setHours(23, 59, 59)
      this.setDates(this.state.startDate, date)
    }
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
            <span onClick={this.onQuickNavClick('prev')} className='prev dashicons dashicons-arrow-left' title={__('Previous', 'koko-analytics')} />
            <span className='date'>
              <span>{format(startDate, 'MMM d, yyyy')}</span>
              <span> &mdash; </span>
              <span>{format(endDate, 'MMM d, yyyy')}</span>
            </span>
            <span onClick={this.onQuickNavClick('next')} className='next dashicons dashicons-arrow-right' title={__('Next', 'koko-analytics')} />
          </div>
          <div className='flex'>
            <div className='date-presets'>
              <div>
                <label for='ka-date-presets'>{__('Date range', 'koko-analytics')}</label>
                <select id='ka-date-presets' onChange={(evt) => { this.setPeriod(evt.target.value) }}>
                  {datePresets.map(p => <option key={p.key} value={p.key} selected={state.preset === p.key}>{p.label}</option>)}
                </select>
              </div>
              <div>
                <label>{__('Custom', 'koko-analytics')}</label>
                <input type='text' value={format(startDate, 'yyyy-MM-dd')} size='10' onChange={this.setCustomStartDate} disabled={state.preset !== 'custom'} placeholder='YYYY-MM-DD' maxlength='10' minlength='6' />
                <span> - </span>
                <input type='text' value={format(endDate, 'yyyy-MM-dd')} size='10' onChange={this.setCustomEndDate} disabled={state.preset !== 'custom'} placeholder='YYYY-MM-DD' maxlength='10' minlength='6' />
              </div>
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
