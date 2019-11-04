'use strict';

import m from 'mithril';
import {format} from "date-fns";
import './top-posts.css';
import api from '../util/api.js';
const i18n = window.koko_analytics.i18n;

function Component() {
    let startDate = null;
    let endDate = null;
    let posts = [];

    const fetch = function(s, e) {
        if (startDate !== null && endDate !== null && s.getTime() === startDate.getTime() && e.getTime() === endDate.getTime()) {
            return;
        }

        startDate = s;
        endDate = e;
        api.request(`/posts`, {
            params: {
                start_date: format(startDate, 'yyyy-MM-dd'),
                end_date: format(endDate, 'yyyy-MM-dd')
            }
        }).then(p => {
                posts = p;
            });
    };

    return {
        view(vnode) {
            fetch(vnode.attrs.startDate, vnode.attrs.endDate);
            return (
                <div className={"box top-posts"}>
                    <div className="head box-grid">
                        <div className={""}>{i18n['Pages']}</div>
                        <div className={"amount-col"}>{i18n['Visitors']}</div>
                        <div className={"amount-col"}>{i18n['Pageviews']}</div>
                    </div>
                    <div className={"body"}>
                        {posts.map(p => (
                            <div key={p.id} className={"box-grid"}>
                                <div><a href={p.post_permalink}>{p.post_title || '(no title)'}</a></div>
                                <div className={"amount-col"}>{Math.max(1, p.visitors)}</div>
                                <div className={"amount-col"}>{p.pageviews}</div>
                            </div>
                        ))}
                        {posts.length === 0 && (<div>{i18n['There\'s nothing here, yet!']}</div>)}
                    </div>
                </div>

            )
        }
    }
}

export default Component;
