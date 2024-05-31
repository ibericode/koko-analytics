import {magnitude, formatLargeNumber} from './numbers.js';

function assert_equals(left, right) {
  if (left !== right) {
    throw Error(`Failed asserting that ${left} equals ${right}`);
  }
}

[
  [0, '0'],
  [1, '1'],
  [1000, '1000'],
  [10000, '10K'],
  [100000, '100K'],
  [1000000, '1M'],
  [10000000, '10M'],
  [170000, '170K'],
  [17000, '17K'],
  [1700, '1700'],
  [170, '170'],
  [17, '17'],
  [175500, '176K'],
  [17550, '17.6K'],
  [1755, '1755'],
  [175, '175'],
  [9999, '9999'],
  [99999, '100K'],
  [999999, '1000K'],
  [9999990, '10M'],
].forEach((args) => {

  const left = formatLargeNumber(args[0]);
  const right = args[1];
  console.log((left == right ? "OK" : "FAIL") + "    formatLargeNumber("+args[0]+")");
  assert_equals(left, right);
});


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
  [20001, 30000],
  [100000, 100000],
  [101000, 110000],
  [151000, 160000],
].forEach((args) => {
  const left = magnitude(args[0]);
  const right = args[1];
  console.log((left == right ? "OK" : "FAIL") + "    magnitude("+args[0]+")");
  assert_equals(left, right);
})
