'use strict';

import m from 'mithril';
import {format} from "date-fns";
import '../../sass/top-referrers.scss';
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
                                    {i18n['Referrers']}
                                    <div className={"pagination"}>
                                        {offset > 0 && <span className="prev" onclick={() => {
                                            offset = Math.max(0, offset - limit);
                                            fetch(vnode.attrs.startDate, vnode.attrs.endDate);
                                        }
                                        }>&larr;</span>}
                                        {items.length >= limit && <span className="next" onclick={() => {
                                            offset += limit;
                                            fetch(vnode.attrs.startDate, vnode.attrs.endDate);
                                        }
                                        }>&rarr;</span>}
                                    </div>
                                </div>
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
