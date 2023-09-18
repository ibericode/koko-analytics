import {magnitude, formatLargeNumber} from './numbers'

// eslint-disable-file no-undef
test('formatLargeNumber works', () => {
  expect(formatLargeNumber(1000)).toBe('1000')
  expect(formatLargeNumber(10000)).toBe('10K')
  expect(formatLargeNumber(100000)).toBe('100K')
  expect(formatLargeNumber(1000000)).toBe('1M')
  expect(formatLargeNumber(10000000)).toBe('10M')
  expect(formatLargeNumber(170000)).toBe('170K')
  expect(formatLargeNumber(17000)).toBe('17K')
  expect(formatLargeNumber(1700)).toBe('1700')
  expect(formatLargeNumber(170)).toBe('170')
  expect(formatLargeNumber(175500)).toBe('176K')
  expect(formatLargeNumber(17550)).toBe('17.6K')
  expect(formatLargeNumber(1755)).toBe('1755')
  expect(formatLargeNumber(175)).toBe('175')
  expect(formatLargeNumber(9999)).toBe('9999')
  expect(formatLargeNumber(99999)).toBe('100K')
  expect(formatLargeNumber(999999)).toBe('1000K')
  expect(formatLargeNumber(9999990)).toBe('10M')
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
    expect(magnitude(n)).toBe(expected)
  })
})
