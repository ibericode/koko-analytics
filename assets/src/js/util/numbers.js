'use strict';

const M = 1000000;
const K = 1000;
const rx = new RegExp('0+$');
const commaRx = new RegExp('(\\d+)(\\d{3})');

function formatPretty(num) {
    let decimals = 0;

    if (num >= M) {
        num /= M;
        decimals = 3 - ((Math.round(num) + "").length) || 0;
        return (num.toFixed(decimals > -1 ? decimals : 0).replace(rx, '') + 'M').replace('.00', '');
    }

    if (num >= (K)) {
        num /= K;
        decimals = 3 - ((Math.round(num) + "").length) || 0;
        return num.toFixed(decimals).replace(rx, '').trimRight(0) + 'K';
    }

    return formatWithComma(num);
}

function formatWithComma(nStr) {
    nStr += '';
    if(nStr.length < 4 ) {
        return nStr;
    }

    let	x = nStr.split('.');
    let x1 = x[0];
    let x2 = x.length > 1 ? '.' + x[1] : '';
    while (commaRx.test(x1)) {
        x1 = x1.replace(commaRx, '$1' + ',' + '$2');
    }
    return x1 + x2;
}

function formatDuration(seconds) {
    seconds = Math.round(seconds);
    let date = new Date(null);
    date.setSeconds(seconds); // specify value for SECONDS here
    return date.toISOString().substr(14, 5);
}

function formatPercentage(p) {
    if (p < 1 && p > -1) {
        p = Math.round(p*100);
    }

    return p >= 0 ? `+${p}%` : `${p}%`;
}

export default {
    formatPretty,
    formatWithComma,
    formatDuration,
    formatPercentage
}
