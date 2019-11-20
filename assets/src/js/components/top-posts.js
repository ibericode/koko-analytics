'use strict';

import React from 'react';
import {format} from "date-fns";
import api from '../util/api.js';
const i18n = window.koko_analytics.i18n;

export default class TopPosts extends React.PureComponent {
	constructor(props) {
		super(props);
		this.state =  {
			offset: 0,
			limit: 10,
			items: [],
		};
	}

	componentDidMount() {
		this.fetch();
	}

	componentDidUpdate(prevProps, prevState, snapshot) {
		if (this.props.startDate.getTime() === prevProps.startDate.getTime() && this.props.endDate.getTime() === prevProps.endDate.getTime()) {
			return;
		}

		this.fetch();
	}

	fetch(offset = this.state.offset) {
        api.request(`/posts`, {
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



	render() {
		let {offset, limit, items} = this.state;
		let {startDate, endDate} = this.props;
		return (
			<div className={"box top-posts"}>
				<div className="head box-grid">
					<div className={""}>
						<span className={"muted"}>#</span>
						{i18n['Pages']}
						<div className={"pagination"}>
							<span className={"prev " + (offset === 0 ? 'hidden' : '')}
								  title={i18n['Previous']} onClick={() => {
								let newOffset = Math.max(0, offset - limit);
								this.fetch(newOffset);
							}}>&larr;</span>
							<span className={"next " + (items.length < limit ? "hidden" : '')}
								  title={i18n['Next']} onClick={() => {
								let newOffset = offset + limit;
								this.fetch(newOffset);
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
					{items.length === 0 && (
						<div className={"box-grid"}>{i18n['There\'s nothing here, yet!']}</div>)}
				</div>
			</div>
		);
	}
}
