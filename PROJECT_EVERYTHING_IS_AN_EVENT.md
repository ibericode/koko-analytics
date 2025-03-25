# Goal

To have siteviews, pageviews, referrers and custom events all use a single unified storage mechanism. Creating a custom event would simply mean creating another backing database table of the correct structure.

## Challenges

Different event types can take a different number of parameters.

- Siteviews takes no parameters.
- Pageviews take a single numeric parameter (the post ID)
- Referrers takes a single textual parameter (the referring URL)
- Custom events should be able to accept 1...n parameters. Maybe even 0 which changes the dashboard visualization to a simple counter, much like the totals bar?


How to migrate existing data into the new format?

