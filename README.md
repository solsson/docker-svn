
# Repos' Subversion images

First of all, why do we use svn? We see it as a self-hosted *blob store* with an *audit trail*. It comes with an HTTP API and has permanent URLs to every revision at every path. It stores binary files efficiently and supports *versioned metadata* and individual file branching.

For write access REST we use [rweb](https://github.com/Reposoft/rweb/).

Subversion is no longer actively maintained.
We see 1.8.x as latest stable, partly because [SvnKit](https://svnkit.com/) is inn't completely compatible with 1.9.x.
There is a branch `1.9.x` for those who are interested in the backend optimizations.
At Repos our source is in git, but we haven't found something better than svn for documents, graphics and configuration.
Our next best bet would be something like [IPFS](https://ipfs.io/), but we're in no hurry.

## Two images required in production

Because svn runs in httpd, and (mod_)php doesn't scale in httpd,
we recommend using [httpd](https://hub.docker.com/_/httpd/) and [php:fpm](https://hub.docker.com/_/php/) together, with rweb at the same path.
That's what the images [solsson/rweb-httpd]() and [solsson/rweb-fpm]() from this repo are meant for.
