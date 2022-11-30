import { h, Component } from 'preact'
import api from './../util/api.js'
import Nav from './nav.js'
import datePresets from '../util/date-presets'
import { __ } from '@wordpress/i18n'
import ButtonReset from './button-reset.js'
/* eslint react/no-danger: "off" */

const data = window.koko_analytics
const roles = window.koko_analytics.user_roles
const settings = window.koko_analytics.settings

export default class Settings extends Component {
  constructor (props) {
    super(props)

    this.state = {
      settings,
      saving: false,
      buttonText: __('Save Changes', 'koko-analytics')
    }

    this.onSubmit = this.onSubmit.bind(this)
  }

  onSubmit (evt) {
    evt.preventDefault()

    this.setState({
      saving: true,
      buttonText: __('Saving - please wait', 'koko-analytics')
    })
    const startTime = new Date()

    api.request('/settings', {
      method: 'POST',
      body: settings
    }).then(() => {
      window.setTimeout(() => {
        this.setState({
          buttonText: __('Saved!', 'koko-analytics')
        })
      }, Math.max(20, 400 - (+new Date() - startTime)))
    }).finally(() => {
      window.setTimeout(() => {
        this.setState({
          buttonText: __('Save Changes', 'koko-analytics'),
          saving: false
        })
      }, 4000)
    })
  }

  handleRadioClick (key) {
    return (evt) => {
      settings[key] = parseInt(evt.target.value, 10)
      this.setState({ settings })
    }
  }

  render (props, state) {
    const { saving, buttonText, settings } = state
    return (
      <main>
        <div className='grid'>
          <div className='four'>
            <h1>{__('Settings', 'koko-analytics')}</h1>
            <form method='POST' onSubmit={this.onSubmit}>
              <div className='input-group'>
                <label>{__('Exclude pageviews from these user roles', 'koko-analytics')}</label>
                <select
                  name='exclude_user_roles[]' multiple onChange={(evt) => {
                    settings.exclude_user_roles = [].filter.call(evt.target.options, el => el.selected).map(el => el.value)
                    this.setState({ settings })
                  }}
                >
                  {Object.keys(roles).map(key => {
                    return (<option key={key} value={key} selected={settings.exclude_user_roles.indexOf(key) > -1}>{roles[key]}</option>)
                  })}
                </select>
                <p className='help'>
                  {__('Visits and pageviews from users with any of the selected roles will be ignored.', 'koko-analytics')}
                  {' '}
                  {__('Use CTRL to select multiple options.', 'koko-analytics')}
                </p>
              </div>

              <div className='input-group'>
                <label>{__('Use cookie to determine unique visitors and pageviews?', 'koko-analytics')}</label>
                <label className='cb'><input type='radio' name='use_cookie' value={1} onChange={this.handleRadioClick('use_cookie')} checked={settings.use_cookie === 1} /> {__('Yes', 'koko-analytics')}</label>
                <label className='cb'><input type='radio' name='use_cookie' value={0} onChange={this.handleRadioClick('use_cookie')} checked={settings.use_cookie === 0} /> {__('No', 'koko-analytics')}</label>
                <p className='help'>{__('Set to "no" if you do not want to use a cookie. Without the use of a cookie, Koko Analytics can not reliably detect returning visitors.', 'koko-analytics')}</p>
              </div>

              <div className='input-group'>
                <label>{__('Default date period', 'koko-analytics')}</label>
                <select
                  name='default_view' onChange={evt => {
                    settings.default_view = evt.target.value
                    this.setState({ settings })
                  }}>
                  {datePresets.map(i => <option value={i.key} key={i.key} selected={settings.default_view === i.key}>{i.label}</option>)}
                </select>
                <p className='help'>
                  {__('The default date period to show when opening the analytics dashboard.', 'koko-analytics')}
                </p>
              </div>

              <div className='input-group'>
                <label>{__('Automatically delete data older than how many months?', 'koko-analytics')}</label>
                <input
                  type='number' value={settings.prune_data_after_months} onChange={(evt) => {
                    settings.prune_data_after_months = parseInt(evt.target.value, 10)
                    this.setState({ settings })
                  }} step={1} min={0} max={600}
                /> {__('months', 'koko-analytics')}
                <p className='help'>{__('Statistics older than the number of months configured here will automatically be deleted. Set to 0 to disable.', 'koko-analytics')}</p>
              </div>

              <div className='input-group'>
                <p>
                  <button
                    type='submit' className='button button-primary'
                    disabled={saving}
                  >{buttonText}
                  </button>
                </p>
              </div>
            </form>
            <div className='margin-m' style={`display: ${data.multisite ? 'none' : ''}`}>
              <h2>{__('Performance', 'koko-analytics')}</h2>
              {data.custom_endpoint.enabled
                ? <p>✓ {__('The plugin is currently using an optimized tracking endpoint. Great!', 'koko-analytics')}</p>
                : (<div>
                  <p dangerouslySetInnerHTML={{
                    __html: '❌ ' + __('The plugin is currently not using an optimized tracking endpoint. To address, create a file named %1s in your WordPress root directory with the following file contents:', 'koko-analytics')
                      .replace('%1s', '<strong>koko-analytics-collect.php</strong>')
                  }}> </p>
                  <p><strong>{__('Filename:', 'koko-analytics')} </strong> <em>{data.custom_endpoint.wp_root_dir}/koko-analytics-collect.php</em></p>
                  <pre className='code' onClick={(evt) => {
                    const range = new Range()
                    range.setStart(evt.target, 0)
                    range.setEnd(evt.target, 1)
                    document.getSelection().removeAllRanges()
                    document.getSelection().addRange(range)
                  }}>{data.custom_endpoint.file_contents}</pre>
                  <p>{__('Please note that this is entirely optional and only recommended for high-traffic websites.', 'koko-analytics')}</p>
                </div>)
              }
            </div>
            <div className='margin-m'>
              <h2>{__('Data', 'koko-analytics')}</h2>
              <p>{__('Database size:', 'koko-analytics')} {data.dbSize} MB</p>
              <div className='input-group'>
                <p>{__('Use the button below to erase all of your current analytics data. You may have to clear your browser\'s cache afterwards for the effect to be evident.', 'koko-analytics')}</p>
                <p>
                  <ButtonReset />
                </p>
              </div>
            </div>
          </div>
          <Nav />
        </div>
      </main>
    )
  }
}
