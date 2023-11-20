import { attributesModule, eventListenersModule, init, h } from "snabbdom";
import {request} from '../util/api.js'
const limit = window.koko_analytics.items_per_page;
const patch = init([attributesModule, eventListenersModule]);

/**
 * @param {HTMLElement} root
 * @param {array} data
 * @param {Date} startDate
 * @param {Date} endDate
 * @param {string} apiEndpoint
 * @param {function} rowView
 * @param {function?} onUpdate
 * @returns {{update: update}}
 * @constructor
 */
export function BlockComponent(root, data, startDate, endDate, apiEndpoint, rowView, onUpdate) {
  let elPlaceholder = root.nextElementSibling;
  let pagination = elPlaceholder.nextElementSibling;
  let buttonPrev = pagination.children[0];
  let buttonNext = pagination.children[1];
  let offset = 0,
    total = data.length;
    root = patch(root, render(data));
  if (onUpdate) {
    onUpdate(data)
  }

  function update(newStartDate, newEndDate) {
    startDate = newStartDate
    endDate = newEndDate
    offset = 0;
    fetch()
  }

  function fetch() {
    request(apiEndpoint, {
      offset,
      limit,
      start_date: startDate,
      end_date: endDate
    }).then(items => {
      total = items.length
      root = patch(root, render(items))
      if (onUpdate) {
        onUpdate(items)
      }
    })
  }

  function render(items) {
    buttonNext.classList.toggle('disabled', total < limit)
    buttonPrev.classList.toggle('disabled', offset === 0 )

    elPlaceholder.style.display = items.length ? 'none' : '';
    pagination.style.display = (items.length < limit && offset === 0) ? 'none' : '';

    return h('div.ka-topx--body', items.map((item, i) => {
      return rowView(item, offset + i + 1)
    }))
  }

  buttonPrev.addEventListener('click', () => {
    if (offset === 0) {
      return;
    }

    offset = Math.max(0, offset - limit );
    fetch();
  })
  buttonNext.addEventListener('click', () => {
    if (total < limit) {
      return;
    }

    offset += limit;
    fetch();
  })

  return {update}
}

/**
 * @param {HTMLElement} root
 * @param {array} data
 * @param {Date} startDate
 * @param {Date} endDate
 * @param {function} onPageClick
 * @returns {{update: update}}
 */
export function PostsComponent(root, data, startDate, endDate, onPageClick) {
  return BlockComponent(root, data, startDate, endDate, '/posts', function(item, rank) {
    return h('div.ka-topx--row ka-fade', [
      h('div.ka-topx--rank', {}, rank),
      h('div.ka-topx--col', {}, [
        h('a', {
          attrs: {
            href: item.post_permalink,
          },
          on: {
            click: (evt) => {
              evt.preventDefault();
              onPageClick(item.id, item.post_title, item.post_permalink);
            }
          }
        },item.post_title || '(no title)'),
      ]),
      h('div.ka-topx--amount', Math.max(1, item.visitors)),
      h('div.ka-topx--amount', item.pageviews)
    ])
  })
}

/**
 * @param {{displayUrl, url}} item
 * @returns {*}
 */
function modifyUrlsForDisplay (item) {
  item.displayUrl = item.url.replace(/^https?:\/\/(www\.)?(.+?)\/?$/, '$2')

  if (item.url.indexOf('https://t.co/') === 0) {
    item.url = 'https://twitter.com/search?q=' + encodeURI(item.url)
  } else if (item.url.indexOf('android-app://') === 0) {
    item.displayUrl = item.url.replace('android-app://', 'Android app: ')
    item.url = item.url.replace('android-app://', 'https://play.google.com/store/apps/details?id=')
  }

  return item
}

/**
 * @param {HTMLElement} root
 * @param {array} data
 * @param {Date} startDate
 * @param {Date} endDate
 * @returns {{update: update}}
 */
export function ReferrersComponent(root, data, startDate, endDate) {
  return BlockComponent(root, data, startDate, endDate, '/referrers', function(item, rank) {
    item = modifyUrlsForDisplay(item)
    return h('div.ka-topx--row ka-fade', [
      h('div.ka-topx--rank', {}, rank),
      h('div.ka-topx--col', {}, [
        h('a', { attrs: { href: item.url } },item.displayUrl)
      ]),
      h('div.ka-topx--amount', Math.max(1, item.visitors)),
      h('div.ka-topx--amount', item.pageviews)
    ])
  })
}
