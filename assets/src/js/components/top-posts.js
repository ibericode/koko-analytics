'use strict';

import m from 'mithril';
import {format} from "date-fns";
import './top-posts.css';

function Component(vnode) {

    let startDate = null;
    let endDate = null;
    let posts = [];

    const fetch = function(s, e) {
        if (startDate !== null && endDate !== null && s.getTime() === startDate.getTime() && e.getTime() === endDate.getTime()) {
            return;
        }

        startDate = s;
        endDate = e;
        m.request(`${aaa.root}aaa-stats/v1/posts?start_date=${format(s, 'yyyy-MM-dd')}&end_date=${format(e, 'yyyy-MM-dd')}&count=1`)
            .then(p => {
                posts = p;
            });
    };

    return {
        view(vnode) {
            fetch(vnode.attrs.startDate, vnode.attrs.endDate);
            return (
                <div className={"top-posts"}>
                    <table>
                        <thead className="">
                            <th className={"main-col"}>Page</th>
                            <th className={"amount-col"}>Visitors</th>
                            <th className={"amount-col"}>Pageviews</th>
                        </thead>
                        <tbody>
                        {posts.map(p => (
                            <tr key={p.id} className={""}>
                                <td><a href={p.post_permalink}>{p.post_title}</a></td>
                                <td className={"amount-col"}>{p.visitors}</td>
                                <td className={"amount-col"}>{p.pageviews}</td>
                            </tr>
                        ))}
                        {posts.length === 0 && (<tr><td colSpan={3}>There's nothing here.</td></tr>)}
                        </tbody>
                    </table>
                </div>
            )
        }
    }
}

export default Component;