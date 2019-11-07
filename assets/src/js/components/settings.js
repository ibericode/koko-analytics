'use strict';

import m from 'mithril';
import api from './../util/api.js';
import Datepicker from "./datepicker";
const i18n = window.koko_analytics.i18n;
const roles = window.koko_analytics.user_roles;

function Component() {
    const settings = window.koko_analytics.settings;
    let buttonText = i18n['Save Changes'];
    let saving = false;

    function onSubmit(evt) {
        evt.preventDefault();

        saving = true;
        buttonText = i18n['Saving - please wait'];
        let startTime = new Date();

        api.request("/settings", {
            method: "POST",
            body: settings
        }).then(success => {
            window.setTimeout(() => {
                buttonText = i18n['Saved!'];
                m.redraw();
            }, Math.max(1, 400 - (+new Date() - startTime)));
        }).finally(() => {
            saving = false;
            window.setTimeout(() => {
                buttonText = i18n['Save Changes'];
                m.redraw();
            }, 4000);
        })
    }

    return {
        view: () => {
            return (
                <main>
                    <div className={"grid"} style={"margin-bottom: 24px;"}>
                        <div style={"grid-column: span 4;"}>
                            <h1>{i18n['Settings']}</h1>
                            <form method={"POST"} onSubmit={onSubmit}>
                                <div className={"input-group"}>
                                    <label>{i18n['Exclude pageviews from these user roles']}</label>
                                    <select name="exclude_user_roles[]" multiple={"true"} onChange={(evt) => {
                                        settings.exclude_user_roles = [].filter.call(evt.target.options, el => el.selected).map(el => el.value);
                                    }}>
                                        {Object.keys(roles).map(key => {
                                            return (<option key={key} value={key}
                                                            selected={settings.exclude_user_roles.indexOf(key) > -1}>{roles[key]}</option>)
                                        })}
                                    </select>
                                </div>

                                <p>
                                    <button type={"submit"} className={"button button-primary"}
                                            disabled={saving}>{buttonText}</button>
                                </p>
                            </form>
                        </div>
                        <div style="grid-column: span 2;">
                            <ul className="nav subsubsub">
                                <li><a href={"#!/"}>{i18n['Stats']}</a> | </li>
                                <li><a href={"#!/settings"} className="current">{i18n['Settings']}</a></li>
                            </ul>
                        </div>
                    </div>

					<div>
						<p className="help">Thank you for using Koko Analytics! Please <a href="https://wordpress.org/support/plugin/koko-analytics/reviews/#new-post">leave us a plugin review on WordPress.org</a> if our work helped you.</p>
					</div>
                </main>
            )
        }
    }
}

export default Component;
