'use strict';

import Chart from './components/chart';
import React from 'react';
import ReactDOM from 'react-dom';
const el = document.getElementById('koko-analytics-dashboard-widget-mount');
const now = new Date();
const startDate = new Date(now.getFullYear(), now.getMonth(), now.getDate() - 14, 0, 0, 0);
const endDate = new Date(now.getFullYear(), now.getMonth(), now.getDate(), 23, 59, 59);

ReactDOM.render(<Chart startDate={startDate} endDate={endDate} height={200} />, el);
