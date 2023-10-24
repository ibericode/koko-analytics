import {request} from './util/api'
import * as dates from './util/dates'

Object.assign(window.koko_analytics, {
  components: {},
  util: {request, dates}
})
