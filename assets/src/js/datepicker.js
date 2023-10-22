export default function Datepicker(root) {
  const dropdown = root.children[1];
  let isOpen = false;

  document.addEventListener('click', (evt) => {
    /* don't close if clicking anywhere inside this component */
    for (let el = evt.target; el !== null; el = el.parentNode) {
      if (el === root || (typeof el.className === 'string' && el.className.indexOf('ka-datepicker--label') > -1)) {
        return
      }
    }

    toggle(false);
  })

  function toggle(open) {
    isOpen = typeof(open) === 'boolean' ? open : !isOpen;
    dropdown.style.display = isOpen ? '' : 'none';
  }

  root.children[0].addEventListener('click', toggle)

}
