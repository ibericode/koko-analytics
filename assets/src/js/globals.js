import {request} from './util/api'
import Pagination from './components/table-pagination'

Object.assign(window.koko_analytics, {
  components: {
    Pagination
  },
  request
})
