const M = 1000000
const K = 1000
const REGEX_TRAILING_ZEROES = /\.0+$/

/**
 * Format large numbers by replacing zeros with either M (millions) or K (thousands)
 * Numbers lower than 10.000 are left untouched.
 *
 * @param {number} num
 * @returns {string}
 */
export function formatLargeNumber (num) {
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

/**
 * Format a percentage amount (eg 0.55) as a human-readable percentage (eg 55%).
 *
 * @param {number} p The percentage amount, must conform to -1.00 <= p <= 1.00
 * @returns {string}
 */
export function formatPercentage (p) {
  p = Math.round(p * 100)
  return p >= 0 ? `+${p}%` : `${p}%`
}

/**
 *  Return a nice human-comprehensible number.
 *
 *  n < 10 = 10
 *  n < 100 = rounds up to the nearest power of 10
 *  n < 1000 = rounds up to the nearest power of 100
 * @param {number} n
 * @returns {number}
 */
export function magnitude (n) {
  if (n < 10) {
    return 10
  }

  const exponent = Math.floor(Math.log10(n))
  const pow = Math.pow(10, exponent)
  return Math.ceil(n / pow) * pow
}
