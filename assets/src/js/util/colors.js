/**
 * Lighten or darken a hex color by a certain amount.
 *
 * @param {string} color
 * @param {int} percent
 * @returns {string}
 */
export function modify (color, percent) {
  let R = parseInt(color.substring(1, 3), 16)
  let G = parseInt(color.substring(3, 5), 16)
  let B = parseInt(color.substring(5, 7), 16)

  R = parseInt(R * (100 + percent) / 100, 10)
  G = parseInt(G * (100 + percent) / 100, 10)
  B = parseInt(B * (100 + percent) / 100, 10)

  R = (R < 255) ? R.toString(16) : 'ff'
  G = (G < 255) ? G.toString(16) : 'ff'
  B = (B < 255) ? B.toString(16) : 'ff'

  const RR = ((R.length === 1) ? '0' + R : R)
  const GG = ((G.length === 1) ? '0' + G : G)
  const BB = ((B.length === 1) ? '0' + B : B)

  return '#' + RR + GG + BB
}
