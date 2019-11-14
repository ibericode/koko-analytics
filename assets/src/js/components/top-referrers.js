'use strict';

import m from 'mithril';
import {format} from "date-fns";
import api from '../util/api.js';
const i18n = window.koko_analytics.i18n;
const URL_REGEX = /^https?:\/\/(www\.)?(.+?)\/?$/;

function Component() {
    let items = [];
    let offset = 0;
    let limit = 10;
    let currentParams = {};

    const fetch = function(startDate, endDate) {
        let params = {
            start_date: format(startDate, 'yyyy-MM-dd'),
            end_date: format(endDate, 'yyyy-MM-dd'),
            offset: offset,
            limit: limit,
        };
        if (JSON.stringify(params) === JSON.stringify(currentParams)) {
            return;
        }

        currentParams = params;
        api.request(`/referrers`, {params })
            .then(p => {
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
                                <div className={""}>
                                    <span className={"muted"}>#</span>
                                    {i18n['Referrers']}
                                    <div className={"pagination"}>
                                        <span className={"prev " + (offset === 0 ? 'hidden' : '')} title={i18n['Previous']} onclick={() => {
                                            offset = Math.max(0, offset - limit);
                                            fetch(vnode.attrs.startDate, vnode.attrs.endDate);
                                        }}>&larr;</span>
                                        <span className={"next " + (items.length < limit ? "hidden" : '')} title={i18n['Next']} onclick={() => {
                                            offset += limit;
                                            fetch(vnode.attrs.startDate, vnode.attrs.endDate);
                                        }}>&rarr;</span>
                                    </div>
                                </div>
                                <div className={"amount-col"}>{i18n['Visitors']}</div>
                                <div className={"amount-col"}>{i18n['Pageviews']}</div>
                            </div>
                            <div className={"body"}>
                            {items.map((p, i) => (
                                <div key={p.id} className={"box-grid"}>
                                    <div>
                                        <span className={"muted"}>{offset + i + 1}</span>
                                        <a href={p.url}>{formatUrl(p.url)}</a>
                                    </div>
                                    <div className={"amount-col"}>{Math.max(p.visitors, 1)}</div>
                                    <div className={"amount-col"}>{p.pageviews}</div>
                                </div>
                            ))}
                            {items.length === 0 && (<div className={"box-grid"}>{i18n['There\'s nothing here, yet!']}</div>)}
                            </div>
                    </div>
            )
        }
    }
}

export default Component;
