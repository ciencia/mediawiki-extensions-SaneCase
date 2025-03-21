The SaneCase extension automatic corrects case mistakes. For example if the page
`Test` exists, and someone goes to `TEST`, they will be automatically redirected
to `Test` with a 301.

This is really much more sane than the default case-sensitivity.

This implements the "Auto redirect option" from Case sensitivity of page names:

> Automatically redirect to a page that has same spelling but different
> capitalization (have the computer do the disambiguation pages when a spelling
> doesn't match an existing page)
>
> Negatives: Performance and possible search engine duplicate content penalties
> caused by MediaWiki's redirection mechanism.

My response to that:

- It's one very simple query, the performance hit should be almost unnoticeable
  for most sites.
- The user having to figure out the correct URL is often more of a performance
  hit, not to mention a huge usability hit. Plus these are the sort of responses
  that can be cached very well in Varnish or whatnot.
- A 301 "Moved Permanently" redirect should be fine for search engines.

**Note** This extension *doesn't make MediaWiki titles case-insensitive*! it only
affects visitors following a link or manually typing the article title in the URL.
Internal links are still case sensitive. See [Issue #3](https://github.com/ciencia/mediawiki-extensions-SaneCase/issues/3)
for reasoning.

Additional feature: try to fix broken links because of special chars
----

Sometimes, titles have some special characters, like parentheses, exclamation
or question marks, or other punctuation or special characters. When sharing
such links on IRC or social media, sometimes the link gets cut off at the
presence of that special character, pointing to nowhere.

With this new feature, the extension will try to find a title with the same prefix,
*followed by a special character*, and if it finds it, you'll get redirected
automatically to it.

Note the emphasis in *followed by a special character*. Just displaying any title
with the same prefix may be undesirable, since there may be more than one.
Matching only titles followed by a special character will drastically reduce the
chance to get a naive redirect to a very common prefix.

This feature is disabled by default. To activate it,
set `$wgSaneCaseAutofixSpecialCharBreak = true;` in LocalSettings.php

Also see: https://www.mediawiki.org/wiki/Extension:SaneCase
