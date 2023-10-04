import React, { useState, useEffect, useRef } from 'react'
import addDays from 'date-fns/addDays'
import datePresets from '../util/date-presets.js'
import { format, isLastDayOfMonth, parseISO8601, toISO8601 } from '../util/dates.js'
import { __ } from '@wordpress/i18n'

export default function Datepicker ({
  startDate,
  endDate,
  onUpdate,
  initialPreset
}) {
  let [isOpen, setIsOpen] = useState(false)
  let [preset, setPreset] = useState(initialPreset)
  let [dateRange, setDateRange] = useState({
    startDate,
    endDate
  })
  const root = useRef(null)

  /**
   * Setup event listeners and initialize Pikaday on first render
   */
  useEffect(() => {
    document.addEventListener('keydown', onKeydown)
    document.addEventListener('click', maybeClose)

    return () => {
      document.removeEventListener('keydown', onKeydown)
      document.removeEventListener('click', maybeClose)
    }
  }, [])

  /**
   * Update Pikaday & rest of dashboard whenever date range state changes
   */
  useEffect(() => {
    onUpdate(dateRange.startDate, dateRange.endDate)
  }, [dateRange])

  /**
   * Toggle the date / period picker dropdown
   */
  function toggle () {
    setIsOpen(isOpen => !isOpen)
  }

  /**
   * Close the date / period picker dropdown if clicking anywhere outside it
   *
   * @param {MouseEvent} evt
   */
  function maybeClose (evt) {
    /* don't close if clicking anywhere inside this component */
    for (let el = evt.target; el !== null; el = el.parentNode) {
      if (el === root.current || (typeof el.className === 'string' && el.className.indexOf('ka-datepicker--label') > -1)) {
        return
      }
    }

    setIsOpen(false)
  }

  /**
   * Set selected preset period
   *
   * @param {string} key
   */
  function setPeriod (key) {
    if (key === 'custom') {
      setPreset(key)
      return
    }

    const p = datePresets.find((p) => p.key === key)
    const {
      startDate,
      endDate
    } = p.dates()
    setPreset(p.key)
    setDateRange({
      startDate,
      endDate
    })
  }

  /**
   * Handle quick nav between next and previous periods.
   * @param {string} dir Must be one of `prev` or `next`
   */
  function quickNav (dir) {
    const modifier = dir === 'prev' ? -1 : 1
    setDateRange(({
      startDate,
      endDate
    }) => {
      const cycleMonths = startDate.getDate() === 1 && isLastDayOfMonth(endDate)
      if (cycleMonths) {
        const monthsDiff = endDate.getMonth() - startDate.getMonth() + 1
        return {
          startDate: new Date(startDate.getFullYear(), startDate.getMonth() + (monthsDiff * modifier), 1, 0, 0, 0),
          endDate: new Date(endDate.getFullYear(), endDate.getMonth() + (monthsDiff * modifier) + 1, 0, 23, 59, 59)
        }
      } else {
        const diffInDays = Math.round((endDate - startDate) / 86400000)
        return {
          startDate: addDays(startDate, diffInDays * modifier),
          endDate: addDays(endDate, diffInDays * modifier),
        }
      }
    })

    setPreset('custom')
  }

  /**
   * Listen for key events, trigger quickNav() when arrow keys are pressed.
   *
   * @param {KeyboardEvent} evt
   */
  function onKeydown (evt) {
    if (evt.key === 'ArrowLeft' || evt.key === 'ArrowRight') {
      quickNav(evt.key === 'ArrowLeft' ? 'prev' : 'next')
    }
  }

  function onQuickNavClick (dir) {
    return (evt) => {
      evt.preventDefault()
      quickNav(dir)
    }
  }

  function setCustomStartDate (evt) {
    const startDate = parseISO8601(evt.target.value)
    if (startDate !== null) {
      setPreset('custom')
      startDate.setHours(0, 0, 0)
      setDateRange(({ endDate }) => ({
        startDate,
        endDate
      }))
    }
  }

  function setCustomEndDate (evt) {
    const endDate = parseISO8601(evt.target.value)
    if (endDate !== null) {
      setPreset('custom')
      endDate.setHours(23, 59, 59)
      setDateRange(({ startDate }) => ({
        startDate,
        endDate
      }))
    }
  }

  return (
    <div className="ka-datepicker">
      <div>
        <div className={'ka-datepicker--label'} onClick={toggle}>
          <span className="dashicons dashicons-calendar-alt"/>
          <span>{format(dateRange.startDate)}</span>
          <span> &mdash; </span>
          <span>{format(dateRange.endDate)}</span>
        </div>
      </div>
      <div className="ka-datepicker--dropdown" style={{ display: isOpen ? '' : 'none' }} ref={root}>
        <div className="ka-datepicker--quicknav">
          <span onClick={onQuickNavClick('prev')} className="ka-datepicker--quicknav-prev dashicons dashicons-arrow-left"
                title={__('Previous', 'koko-analytics')}/>
          <span className="date">
              <span>{format(dateRange.startDate)}</span>
              <span> &mdash; </span>
              <span>{format(dateRange.endDate)}</span>
            </span>
          <span onClick={onQuickNavClick('next')} className="ka-datepicker--quicknav-next dashicons dashicons-arrow-right"
                title={__('Next', 'koko-analytics')}/>
        </div>
        <div>
          <div className="ka-datepicker--presets">
            <div>
              <label htmlFor="ka-date-presets">{__('Date range', 'koko-analytics')}</label>
              <div>
                <select id="ka-date-presets" onChange={(evt) => { setPeriod(evt.target.value) }} value={preset}>
                {datePresets.map(p => <option key={p.key} value={p.key} >{p.label}</option>)}
              </select>
              </div>
            </div>
            <div style={{display: 'flex'}}>
              <div>
                <label htmlFor='ka-date-start' style={{display: 'block'}}>{__('Start date', 'koko-analytics')}</label>
                <input id='ka-date-start' type="date" value={toISO8601(dateRange.startDate).substring(0, 10)} size="10" placeholder="YYYY-MM-DD" onChange={setCustomStartDate} />
                <span>&nbsp;&mdash;&nbsp;</span>
              </div>
              <div>
                <label htmlFor='ka-date-end' style={{display: 'block'}}>{__('End date', 'koko-analytics')}</label>
                <input id='ka-date-end' type="date" value={toISO8601(dateRange.endDate).substring(0, 10)} size="10" placeholder="YYYY-MM-DD" onChange={setCustomEndDate} />
              </div>
            </div>
          </div>
        </div>
      </div>
      <input type="hidden" id="start-date-input"/>
    </div>
  )
}

