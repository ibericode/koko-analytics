This directory contains several sample JavaScript snippets for tracking certain custom events.

## About event tracking
[Event tracking](https://www.kokoanalytics.com/kb/tracking-events/) is a paid feature provided by [Koko Analytics Pro](https://pro.kokoanalytics.com/).

## How to use these snippets
For these snippets to work, first go into your Koko Analytics settings and create a new event type. The name of your event type has to match the first parameter passed to `koko_analytics.trackEvent(...)` exactly.

For example:

```js
koko_analytics.trackEvent('Screen width', 500);
```

Requires an event type with the name `Screen width` to exist in order for it to work.
