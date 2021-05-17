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

test('nice works properly', () => {
  [
    [1, 10],
    [2, 10],
    [3, 10],
    [4, 10],
    [6, 10],
    [7, 10],
    [8, 10],
    [9, 10],
    [10, 10],
    [11, 20],
    [21, 30],
    [24, 30],
    [99, 100],
    [100, 100],
    [101, 200],
    [1001, 2000],
    [1200, 2000],
    [20000, 20000],
    [20001, 30000]
  ].forEach((args) => {
    const [n, expected] = args
    expect(numbers.nice(n)).toBe(expected)
  })
})
