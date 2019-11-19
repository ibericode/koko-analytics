'use strict';

import m from 'mithril';
import {format} from "date-fns";
import api from '../util/api.js';
const i18n = window.koko_analytics.i18n;

function Component(vnode) {
    let state = {
        offset: 0,
        items: [],
        startDate: vnode.attrs.startDate,
        endDate: vnode.attrs.endDate,
    };
    let limit = 10;

    function fetch(startDate, endDate, offset = state.offset) {
        api.request(`/posts`, {
          params: {
              start_date: format(startDate, 'yyyy-MM-dd'),
              end_date: format(endDate, 'yyyy-MM-dd'),
              offset: offset,
              limit: limit,
          }
        }).then(p => {
            state.startDate = startDate;
            state.endDate = endDate;
            state.items = p;
            state.offset = offset;
        });
    }

    fetch(vnode.attrs.startDate, vnode.attrs.endDate, state.offset);

    return {
        view(vnode) {
            // check if startDate or endDate attribute changed
            if (vnode.attrs.startDate.getTime() !== state.startDate.getTime() || vnode.attrs.endDate.getTime() !== state.endDate.getTime()) {
                fetch(vnode.attrs.startDate, vnode.attrs.endDate, 0);
            }

            return (
                <div className={"box top-posts"}>
                    <div className="head box-grid">
                        <div className={""}>
                            <span className={"muted"}>#</span>
                            {i18n['Pages']}
                            <div className={"pagination"}>
                                <span className={"prev " + (state.offset === 0 ? 'hidden' : '')} title={i18n['Previous']} onclick={() => {
                                    let newOffset = Math.max(0, state.offset - limit);
                                    fetch(vnode.attrs.startDate, vnode.attrs.endDate, newOffset);
                                }}>&larr;</span>
                                <span className={"next " + (state.items.length < limit ? "hidden" : '')} title={i18n['Next']} onclick={() => {
                                    let newOffset = state.offset + limit;
                                    fetch(vnode.attrs.startDate, vnode.attrs.endDate, newOffset);
                                }
                                }>&rarr;</span>
                            </div>
                        </div>
                        <div className={"amount-col"}>{i18n['Visitors']}</div>
                        <div className={"amount-col"}>{i18n['Pageviews']}</div>
                    </div>
                    <div className={"body"}>
                        {state.items.map((p, i) => (
                            <div key={p.id} className={"box-grid"}>
                                <div>
                                    <span className={"muted"}>{state.offset + i + 1}</span>
                                    <a href={p.post_permalink}>{p.post_title || '(no title)'}</a>
                                </div>
                                <div className={"amount-col"}>{Math.max(1, p.visitors)}</div>
                                <div className={"amount-col"}>{p.pageviews}</div>
                            </div>
                        ))}
                        {state.items.length === 0 && (<div className={"box-grid"}>{i18n['There\'s nothing here, yet!']}</div>)}
                    </div>
                </div>

            )
        }
    }
}

export default Component;
