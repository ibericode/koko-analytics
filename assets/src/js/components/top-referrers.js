'use strict';

import m from 'mithril';
import {format} from "date-fns";
import './top-referrers.css';
import api from '../util/api.js';

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
        api.request(`/referrers?start_date=${format(s, 'yyyy-MM-dd')}&end_date=${format(e, 'yyyy-MM-dd')}`)
            .then(p => {
                items = p;
            });
    };

    return {
        view(vnode) {
            fetch(vnode.attrs.startDate, vnode.attrs.endDate);
            return (
                <div className={"top-referrers"}>
                    <table>
                        <thead className="">
                            <th className={"main-col"}>Referrers</th>
                            <th className={"amount-col"}>Visitors</th>
                            <th className={"amount-col"}>Pageviews</th>
                        </thead>
                        <tbody>
                        {items.map(p => (
                            <tr key={p.id} className={""}>
                                <td><a href={p.url}>{p.url}</a></td>
                                <td className={"amount-col"}>{Math.max(p.visitors, 1)}</td>
                                <td className={"amount-col"}>{p.pageviews}</td>
                            </tr>
                        ))}
                        {items.length === 0 && (<tr><td colSpan={3}>There's nothing here.</td></tr>)}
                        </tbody>
                    </table>
                </div>
            )
        }
    }
}

export default Component;