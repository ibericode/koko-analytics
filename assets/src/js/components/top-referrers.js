'use strict';

import React from 'react';
import {format} from "date-fns";
import api from '../util/api.js';
const i18n = window.koko_analytics.i18n;
const URL_REGEX = /^https?:\/\/(www\.)?(.+?)\/?$/;

function formatUrl(url) {
    return url.replace(URL_REGEX, '$2')
}

export default class TopReferrers extends React.PureComponent {
	constructor(props) {
		super(props);
		this.state = {
			offset: 0,
			limit: 10,
			items: [],
		};
	}

	componentDidUpdate(prevProps, prevState, snapshot) {
		if (this.props.startDate.getTime() === prevProps.startDate.getTime() && this.props.endDate.getTime() === prevProps.endDate.getTime()) {
			return;
		}

		this.fetch();
	}

    fetch(offset = this.state.offset) {
        api.request(`/referrers`, {
            body: {
                start_date: format(this.props.startDate, 'yyyy-MM-dd'),
                end_date: format(this.props.endDate, 'yyyy-MM-dd'),
                offset: offset,
                limit: this.state.limit,
            }
        }).then(items => {
          	this.setState({items, offset});
        });
    }

    componentDidMount() {
		this.fetch();
	}

	render() {
		let {offset, limit, items} = this.state;
		let {startDate, endDate} = this.props;
		return (
			<div className={"box fade top-referrers"}>
				<div className="box-grid head">
					<div className={""}>
						<span className={"muted"}>#</span>
						{i18n['Referrers']}
						<div className={"pagination"}>
							<span className={"prev " + (offset === 0 ? 'disabled' : '')}
								  title={i18n['Previous']} onClick={() => {
								let newOffset = Math.max(0, offset - limit);
								this.fetch(newOffset);
							}}><span className="dashicons dashicons-arrow-left" /></span>
							<span className={"next " + (items.length < limit ? "disabled" : '')}
								  title={i18n['Next']} onClick={() => {
								let newOffset = offset + limit;
								this.fetch(newOffset);
							}
							}><span className="dashicons dashicons-arrow-right" /></span>
						</div>
					</div>
					<div className={"amount-col"}>{i18n['Visitors']}</div>
					<div className={"amount-col"}>{i18n['Pageviews']}</div>
				</div>
				<div className={"body"}>
					{items.map((p, i) => (
						<div key={p.id} className={"box-grid fade"}>
							<div>
								<span className={"muted"}>{offset + i + 1}</span>
								<a href={p.url}>{formatUrl(p.url)}</a>
							</div>
							<div className={"amount-col"}>{Math.max(p.visitors, 1)}</div>
							<div className={"amount-col"}>{p.pageviews}</div>
						</div>
					))}
					{items.length === 0 && (
						<div className={"box-grid"}>{i18n['There\'s nothing here, yet!']}</div>)}
				</div>
			</div>
		)
	}
}
