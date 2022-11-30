const M = 1000000
const K = 1000
const REGEX_TRAILING_ZEROES = /\.0+$/

// format large numbers using M (millions) or K (thousands)
// numbers lower than 10.000 are left untouched
function formatPretty (num) {
  let decimals = 0

  if (num >= M) {
    num = num / M
    decimals = Math.max(0, 3 - String(Math.round(num)).length)
    return num.toFixed(decimals).replace(REGEX_TRAILING_ZEROES, '') + 'M'
  }

  // start showing numbers with K after 10K
  if (num >= K * 10) {
    num = num / K
    decimals = Math.max(0, 3 - (String(Math.round(num)).length))
    return num.toFixed(decimals).replace(REGEX_TRAILING_ZEROES, '') + 'K'
  }

  return String(num)
}

function formatPercentage (p) {
  if (p < 1 && p > -1) {
    p = Math.round(p * 100)
  }

  return p >= 0 ? `+${p}%` : `${p}%`
}

/**
 Return a nice human-comprehensible number.

 n < 10 = 10
 n < 100 = rounds up to the nearest power of 10
 n < 1000 = rounds up to the nearest power of 100
 ....
 @return int
 */
function nice (n) {
  if (n < 10) {
    return 10
  }

  const exponent = Math.floor(Math.log10(n))
  const pow = Math.pow(10, exponent)
  const fraction = n / pow
  return Math.ceil(fraction) * pow
}

export default {
  formatPretty,
  formatPercentage,
  nice
}
