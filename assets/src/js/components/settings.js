'use strict';

import React from 'react';
import api from './../util/api.js';

const i18n = window.koko_analytics.i18n;
const roles = window.koko_analytics.user_roles;
const settings = window.koko_analytics.settings;
import Nav from './nav.js';

export default class Settings extends React.Component {

	constructor(props) {
		super(props);
		this.state = {
			settings,
			saving: false,
			buttonText: i18n['Save Changes'],
		};

		this.onSubmit = this.onSubmit.bind(this);
	}

	onSubmit(evt) {
		evt.preventDefault();

		this.setState({
			saving: true,
			buttonText: i18n['Saving - please wait']
		});
		let startTime = new Date();

		console.log(settings);
		api.request("/settings", {
			method: "POST",
			body: settings
		}).then(success => {
			window.setTimeout(() => {
				this.setState({
					buttonText: i18n['Saved!'],
				});
			}, Math.max(20, 400 - (+new Date() - startTime)));
		}).finally(() => {

			this.setState({
				saving: false
			});

			window.setTimeout(() => {
				this.setState({
					buttonText: i18n['Save Changes']
				});
			}, 4000);
		});
	}

	render() {
		let {saving, buttonText, settings} = this.state;
		return (
			<main>
				<div className={"grid"} style={{marginBottom: '24px'}}>
					<div style={{gridColumn: 'span 4'}}>
						<h1>{i18n['Settings']}</h1>
						<form method={"POST"} onSubmit={this.onSubmit}>
							<div className={"input-group"}>
								<label>{i18n['Exclude pageviews from these user roles']}</label>
								<select name="exclude_user_roles[]" multiple={true} value={settings.exclude_user_roles} onChange={(evt) => {
									settings.exclude_user_roles = [].filter.call(evt.target.options, el => el.selected).map(el => el.value);
									this.setState({settings});
								}}>
									{Object.keys(roles).map(key => {
										return (<option key={key} value={key}>{roles[key]}</option>)
									})}
								</select>
							</div>

							<p>
								<button type={"submit"} className={"button button-primary"}
										disabled={saving}>{buttonText}</button>
							</p>
						</form>
					</div>
					<Nav />
				</div>
				<div>
					<p className="help">Thank you for using Koko Analytics! Please <a
						href="https://wordpress.org/support/plugin/koko-analytics/reviews/#new-post">leave us a plugin
						review on WordPress.org</a> if our work helped you.</p>
				</div>
			</main>
		)
	}

}
