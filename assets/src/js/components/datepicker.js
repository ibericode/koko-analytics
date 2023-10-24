import { parseISO8601, format, toISO8601 } from '../util/dates'
import datePresets from '../util/date-presets'
import { isLastDayOfMonth, addDays } from 'date-fns'

export default function Datepicker(root, callback) {
  const
    dropdown = root.children[1],
    startDateInput = root.querySelector('#ka-date-start'),
    endDateInput = root.querySelector('#ka-date-end'),
    dropdownToggle = root.querySelector('.ka-datepicker--label'),
    dropdownHeading = root.querySelector('.ka-datepicker--quicknav-heading'),
    presetSelect = root.querySelector('#ka-date-presets'),
    quickNavPrevEl = root.querySelector('.ka-datepicker--quicknav-prev'),
    quickNavNextEl = root.querySelector('.ka-datepicker--quicknav-next')

  let startDate = parseISO8601(startDateInput.value)
  let endDate = parseISO8601(endDateInput.value)
  let isOpen = false;

  /**
   * @param {boolean|undefined} open
   */
  function toggle(open) {
    isOpen = typeof(open) === 'boolean' ? open : !isOpen;
    dropdown.style.display = isOpen ? '' : 'none';
  }

  /**
   * @param {boolean?} bubble Whether to signal to parent component that dates have changed
   */
  function updateDateRange(bubble) {
    const str = `${format(startDate)} — ${format(endDate)}`;
    dropdownToggle.lastElementChild.textContent = str
    dropdownHeading.textContent = str;

    if (bubble) {
      callback({startDate, endDate})
    }
  }

  /**
   * @param {int} modifier
   */
  function quickNav (modifier) {
    const cycleMonths = startDate.getDate() === 1 && isLastDayOfMonth(endDate)
    if (cycleMonths) {
      const monthsDiff = endDate.getMonth() - startDate.getMonth() + 1
      startDate = new Date(startDate.getFullYear(), startDate.getMonth() + (monthsDiff * modifier), 1, 0, 0, 0)
      endDate = new Date(endDate.getFullYear(), endDate.getMonth() + (monthsDiff * modifier) + 1, 0, 23, 59, 59)
    } else {
      const diffInDays = Math.round((endDate - startDate) / 86400000)
      startDate = addDays(startDate, diffInDays * modifier)
      endDate = addDays(endDate, diffInDays * modifier)
    }

    presetSelect.value = 'custom';
    startDateInput.value = toISO8601(startDate)
    endDateInput.value = toISO8601(endDate)
    updateDateRange(true)
  }

  document.addEventListener('click', evt => {
    /* don't close if clicking anywhere inside this component */
    for (let el = evt.target; el !== null; el = el.parentNode) {
      if (el === root) {
        return
      }
    }

    toggle(false);
  })

  startDateInput.addEventListener('change', evt => {
    let d = parseISO8601(evt.target.value)
    if (!d) {
      return;
    }

    startDate = d;
    presetSelect.value = 'custom'
    updateDateRange(true)
  })

  endDateInput.addEventListener('change', evt => {
    let d = parseISO8601(evt.target.value)
    if (!d) {
      return;
    }

    endDate = d
    presetSelect.value = 'custom'
    updateDateRange(true)
  })

  presetSelect.addEventListener('change', evt => {
    const p = datePresets.find((p) => p.key === evt.target.value)
    const dates = p.dates();
    startDate = dates.startDate
    endDate = dates.endDate
    startDateInput.value = toISO8601(startDate)
    endDateInput.value = toISO8601(endDate)
    updateDateRange(true)
  })

  document.addEventListener('keydown', evt => {
    if (evt.key === 'ArrowLeft' || evt.key === 'ArrowRight') {
      quickNav(evt.key === 'ArrowLeft' ? -1 : 1)
    }
  })

  quickNavPrevEl.addEventListener('click', () => quickNav(-1))
  quickNavNextEl.addEventListener('click', () => quickNav(1))
  dropdownToggle.addEventListener('click', toggle)
  updateDateRange();
}
