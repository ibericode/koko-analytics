'use strict';

import m from 'mithril';
import {format} from "date-fns";
import api from '../util/api.js';
const i18n = window.koko_analytics.i18n;

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
        api.request(`/posts`, {params })
            .then(p => {
                items = p;
            });
    };

    return {
        view(vnode) {
            fetch(vnode.attrs.startDate, vnode.attrs.endDate);
            return (
                <div className={"box top-posts"}>
                    <div className="head box-grid">
                        <div className={""}>
                            <span className={"muted"}>#</span>
                            {i18n['Pages']}
                            <div className={"pagination"}>
                                <span className={"prev " + (offset === 0 ? 'hidden' : '')} title={i18n['Previous']} onclick={() => {
                                    offset = Math.max(0, offset - limit);
                                    fetch(vnode.attrs.startDate, vnode.attrs.endDate);
                                }}>&larr;</span>
                                <span className={"next " + (items.length < limit ? "hidden" : '')} title={i18n['Next']} onclick={() => {
                                    offset += limit;
                                    fetch(vnode.attrs.startDate, vnode.attrs.endDate);
                                }
                                }>&rarr;</span>
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
                                    <a href={p.post_permalink}>{p.post_title || '(no title)'}</a>
                                </div>
                                <div className={"amount-col"}>{Math.max(1, p.visitors)}</div>
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
