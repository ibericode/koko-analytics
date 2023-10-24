import {request} from './util/api'
import * as dates from './util/dates'
import { BlockComponent } from './components/block-components'

Object.assign(window.koko_analytics, {
  components: {BlockComponent},
  util: {request, dates}
})
