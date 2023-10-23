import { parseISO8601, format } from './util/dates'
import datePresets from './util/date-presets'

export default function Datepicker(root, callback) {
  const dropdown = root.children[1];
  const dateStartEl = root.querySelector('#ka-date-start');
  const dateEndEl = root.querySelector('#ka-date-end');

  let startDate = parseISO8601(dateStartEl.value)
  let endDate = parseISO8601(dateEndEl.value)
  let isOpen = false;
  let preset = '';

  document.addEventListener('click', (evt) => {
    /* don't close if clicking anywhere inside this component */
    for (let el = evt.target; el !== null; el = el.parentNode) {
      if (el === root || (typeof el.className === 'string' && el.className.indexOf('ka-datepicker--label') > -1)) {
        return
      }
    }

    toggle(false);
  })

  function updateElements(key) {
    const str = format(key === 'startDate' ? startDate : endDate)
    root.querySelectorAll(`[data-bind="${key}"]`).forEach(el => {
      el.textContent = str
    });
  }



  updateElements('startDate')
  updateElements('endDate')

  dateStartEl.addEventListener('change', (evt) => {
    startDate = parseISO8601(evt.target.value)
    callback({startDate, endDate})
    updateElements('startDate')
  })

  dateEndEl.addEventListener('change', (evt) => {
    endDate = parseISO8601(evt.target.value)
    callback({startDate, endDate})
    updateElements('endDate')
  })

  root.querySelector('#ka-date-presets').addEventListener('change', (evt) => {
    const p = datePresets.find((p) => p.key === evt.target.value)
    const dates = p.dates();
    startDate = dates.startDate
    endDate = dates.endDate
    updateElements('startDate')
    updateElements('endDate')
    callback({startDate, endDate})
  })

  root.children[0].addEventListener('click', toggle)

  function toggle(open) {
    isOpen = typeof(open) === 'boolean' ? open : !isOpen;
    dropdown.style.display = isOpen ? '' : 'none';
  }

}
