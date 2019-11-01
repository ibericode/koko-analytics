'use strict';

import m from 'mithril';
import {format} from "date-fns";
import './top-posts.css';
import api from '../util/api.js';

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
        api.request(`/posts?start_date=${format(s, 'yyyy-MM-dd')}&end_date=${format(e, 'yyyy-MM-dd')}`)
            .then(p => {
                posts = p;
            });
    };

    return {
        view(vnode) {
            fetch(vnode.attrs.startDate, vnode.attrs.endDate);
            return (
                <div className={"box top-posts"}>
                    <div className="head box-grid">
                        <div className={""}>Page</div>
                        <div className={"amount-col"}>Visitors</div>
                        <div className={"amount-col"}>Pageviews</div>
                    </div>
                    <div className={"body"}>
                        {posts.map(p => (
                            <div key={p.id} className={"box-grid"}>
                                <div><a href={p.post_permalink}>{p.post_title || '(no title)'}</a></div>
                                <div className={"amount-col"}>{Math.max(1, p.visitors)}</div>
                                <div className={"amount-col"}>{p.pageviews}</div>
                            </div>
                        ))}
                        {posts.length === 0 && (<tr><td colSpan={3}>There's nothing here.</td></tr>)}
                    </div>
                </div>

            )
        }
    }
}

export default Component;