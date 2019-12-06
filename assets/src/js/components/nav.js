'use strict';

const i18n = window.koko_analytics.i18n;
import React from 'react';
import {NavLink} from "react-router-dom";

export default function Nav() {

	// do not show navigation if user can not access settings anyway
	if (false === window.koko_analytics.showSettings) {
		return '';
	}

	return (
		<div className={"two nav"}>
			<ul className="subsubsub">
				<li><NavLink to={"/"} exact activeClassName={"current"}>{i18n['Stats']}</NavLink> | </li>
				<li><NavLink to={"/settings"} activeClassName={"current"}>{i18n['Settings']}</NavLink></li>
			</ul>
		</div>
	)
}
