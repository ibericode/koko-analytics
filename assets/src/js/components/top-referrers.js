'use strict';

import m from 'mithril';
import {format} from "date-fns";
import './top-referrers.css';
import api from '../util/api.js';
const i18n = window.koko_analytics.i18n;
const URL_REGEX = /^https?:\/\/(www\.)?(.+?)\/?$/;

function Component() {
    let startDate = null;
    let endDate = null;
    let items = [];

    const fetch = function(s, e) {
        if (startDate !== null && endDate !== null && s.getTime() === startDate.getTime() && e.getTime() === endDate.getTime()) {
            return;
        }

        startDate = s;
        endDate = e;
        api.request(`/referrers`, {
            params: {
                start_date: format(startDate, 'yyyy-MM-dd'),
                end_date: format(endDate, 'yyyy-MM-dd')
            }
        }).then(p => {
            items = p;
        });
    };

    function formatUrl(url) {
		return url.replace(URL_REGEX, '$2')
	}

    return {
        view(vnode) {
            fetch(vnode.attrs.startDate, vnode.attrs.endDate);
            return (
                    <div className={"box top-referrers"}>
                            <div className="box-grid head">
                                <div className={""}>{i18n['Referrers']}</div>
                                <div className={"amount-col"}>{i18n['Visitors']}</div>
                                <div className={"amount-col"}>{i18n['Pageviews']}</div>
                            </div>
                            <div className={"body"}>
                            {items.map(p => (
                                <div key={p.id} className={"box-grid"}>
                                    <div><a href={p.url}>{formatUrl(p.url)}</a></div>
                                    <div className={"amount-col"}>{Math.max(p.visitors, 1)}</div>
                                    <div className={"amount-col"}>{p.pageviews}</div>
                                </div>
                            ))}
                            {items.length === 0 && (<div>{i18n['There\'s nothing here, yet!']}</div>)}
                            </div>
                    </div>
            )
        }
    }
}

export default Component;
