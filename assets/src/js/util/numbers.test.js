const numbers = require('./numbers.js').default

// eslint-disable-file no-undef
test('formatPretty works', () => {
  expect(numbers.formatPretty(1000)).toBe('1000')
  expect(numbers.formatPretty(10000)).toBe('10K')
  expect(numbers.formatPretty(100000)).toBe('100K')
  expect(numbers.formatPretty(1000000)).toBe('1M')
  expect(numbers.formatPretty(10000000)).toBe('10M')
  expect(numbers.formatPretty(170000)).toBe('170K')
  expect(numbers.formatPretty(17000)).toBe('17K')
  expect(numbers.formatPretty(1700)).toBe('1700')
  expect(numbers.formatPretty(170)).toBe('170')
  expect(numbers.formatPretty(175500)).toBe('176K')
  expect(numbers.formatPretty(17550)).toBe('17.6K')
  expect(numbers.formatPretty(1755)).toBe('1755')
  expect(numbers.formatPretty(175)).toBe('175')
  expect(numbers.formatPretty(9999)).toBe('9999')
  expect(numbers.formatPretty(99999)).toBe('100K')
  expect(numbers.formatPretty(999999)).toBe('1000K')
  expect(numbers.formatPretty(9999990)).toBe('10M')
})
