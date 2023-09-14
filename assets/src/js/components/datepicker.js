import React, { useState, useEffect, useRef } from 'react'
import Pikaday from 'pikaday'
import 'pikaday/css/pikaday.css'
import '../../sass/datepicker.scss'
import addDays from 'date-fns/addDays'
import datePresets from '../util/date-presets.js'
import { format, isLastDayOfMonth, parseISO8601 } from '../util/dates.js'
import { __ } from '@wordpress/i18n'

const startOfWeek = parseInt(window.koko_analytics.start_of_week, 10)
const settings = window.koko_analytics.settings
const defaultDateFormat = window.koko_analytics.date_format
let datepicker

export default function Datepicker ({
  startDate,
  endDate,
  onUpdate
}) {
  let [isOpen, setIsOpen] = useState(false)
  let [preset, setPreset] = useState(settings.default_view)
  let [dateRange, setDateRange] = useState({
    startDate,
    endDate
  })
  const datepickerContainer = useRef(null)
  const root = useRef(null)

  let tmpStartDate = null, tmpEndDate = null
  useEffect(() => {
    document.body.addEventListener('keydown', onKeydown)
    datepicker = new Pikaday({
      field: document.getElementById('start-date-input'),
      bound: false,
      firstDay: startOfWeek,
      numberOfMonths: window.innerWidth > 680 ? 2 : 1,
      enableSelectionDaysInNextAndPreviousMonths: true,
      showDaysInNextAndPreviousMonths: true,
      keyboardInput: false,
      onSelect: (date) => {
        setPreset('custom')

        if (tmpStartDate === null || date < tmpStartDate) {
          date.setHours(0, 0, 0)
          tmpStartDate = date
          datepicker.setStartRange(date)
          datepicker.setEndRange(null)
        } else {
          date.setHours(23, 59, 59)
          tmpEndDate = date
          datepicker.setEndRange(date)
        }

        if (tmpStartDate !== null && tmpEndDate !== null && tmpStartDate < tmpEndDate) {
          setDateRange({
            startDate: tmpStartDate,
            endDate: tmpEndDate
          })
          onUpdate(tmpStartDate, tmpEndDate)
          tmpStartDate = null
          tmpEndDate = null
        }

        datepicker.draw()
      },
      container: datepickerContainer.current
    })
    datepicker.setStartRange(startDate)
    datepicker.setEndRange(endDate)
    datepicker.gotoDate(endDate)

    return () => {
      datepicker.destroy()
      document.body.removeEventListener('keydown', onKeydown)
    }
  }, [])

  useEffect(() => {
    // update Pikaday selection range
    datepicker.setStartRange(dateRange.startDate)
    datepicker.setEndRange(dateRange.endDate)
    datepicker.gotoDate(dateRange.endDate)

    // update other components in dashboard
    onUpdate(dateRange.startDate, dateRange.endDate)
  }, [dateRange])

  function toggle () {
    setIsOpen(isOpen => !isOpen)
  }

  useEffect(() => {
    if (!isOpen) {
      return;
    }

    function maybeClose (evt) {
      /* don't close if clicking anywhere inside this component */
      for (let el = evt.target; el !== null; el = el.parentNode) {
        if (el === root.current) {
          return
        }
      }

      setIsOpen(false)
    }

    document.addEventListener('click', maybeClose)
    return () => {
      document.removeEventListener('click', maybeClose)
    }
  },[isOpen])

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
      endDate.setHours(23, 59, 59)
      setDateRange(({ startDate }) => ({
        startDate,
        endDate
      }))
    }
  }

  return (
    <div className="date-nav" ref={root}>
      <div>
        <div className={'date-label'} onClick={toggle}>
          <span className="dashicons dashicons-calendar-alt"/>
          <span>{format(dateRange.startDate, defaultDateFormat)}</span>
          <span> &mdash; </span>
          <span>{format(dateRange.endDate, defaultDateFormat)}</span>
        </div>
      </div>
      <div className="date-picker-ui" style={{ display: isOpen ? '' : 'none' }}>
        <div className="date-quicknav cf">
          <span onClick={onQuickNavClick('prev')} className="prev dashicons dashicons-arrow-left"
                title={__('Previous', 'koko-analytics')}/>
          <span className="date">
              <span>{format(dateRange.startDate, defaultDateFormat)}</span>
              <span> &mdash; </span>
              <span>{format(dateRange.endDate, defaultDateFormat)}</span>
            </span>
          <span onClick={onQuickNavClick('next')} className="next dashicons dashicons-arrow-right"
                title={__('Next', 'koko-analytics')}/>
        </div>
        <div className="flex">
          <div className="date-presets">
            <div>
              <label htmlFor="ka-date-presets">{__('Date range', 'koko-analytics')}</label>
              <select id="ka-date-presets" onChange={(evt) => { setPeriod(evt.target.value) }} defaultValue={preset}>
                {datePresets.map(p => <option key={p.key} value={p.key}>{p.label}</option>)}
              </select>
            </div>
            <div>
              <label>{__('Custom', 'koko-analytics')}</label>
              <input type="text" value={format(dateRange.startDate, 'Y-m-d')} size="10" onChange={setCustomStartDate}
                     disabled={preset !== 'custom'} placeholder="YYYY-MM-DD" maxLength="10" minLength="6"/>
              <span> - </span>
              <input type="text" value={format(dateRange.endDate, 'Y-m-d')} size="10" onChange={setCustomEndDate}
                     disabled={preset !== 'custom'} placeholder="YYYY-MM-DD" maxLength="10" minLength="6"/>
            </div>
          </div>
          <div className="date-picker">
            <div ref={datepickerContainer}/>
          </div>
        </div>
      </div>
      <input type="hidden" id="start-date-input"/>
    </div>
  )
}

