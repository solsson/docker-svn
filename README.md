
# Repos' Subversion images

First of all, why do we use svn? We see it as a self-hosted *blob store* with an *audit trail*. It comes with an HTTP API and has permanent URLs to every revision at every path. It stores binary files efficiently and supports *versioned metadata* and individual file branching.

For write access REST we use [rweb](https://github.com/Reposoft/rweb/).

Subversion project activity is slowing.
We see 1.8.x as latest stable, partly because [SvnKit](https://svnkit.com/) is inn't completely compatible with 1.9.x.
There is a branch `1.9.x` for those who are interested in the backend optimizations.
At Repos our source is in git, but we haven't found something better than svn for documents, graphics and configuration.
Our next best bet would be something like [IPFS](https://ipfs.io/), but we're in no hurry.

## [solsson/svn-httpd](https://hub.docker.com/r/solsson/svn-httpd/)

Runtime configuration, as environment variables:
 * `ADMIN_REST_ACCESS` non-empty to enable `/admin/repocreate` REST endpoint

Runtime configuration, as [CMD](https://docs.docker.com/engine/reference/builder/#cmd) override:
 * `-DAUTHN=anon` enables [mod_auth_anon](http://httpd.apache.org/docs/current/mod/mod_authn_anon.html) so that usernames from reverse proxy end up in svn logs
 * `-DAUTHZ=svn` enables [mod_autnz_svn](http://svnbook.red-bean.com/nightly/en/svn.serverconfig.httpd.html#svn.serverconfig.httpd.ref.mod_authz_svn) with path `/svn/authz`
 * `-DRWEB=fpm` is used from `solsson/rweb-httpd` (see below) to enable rweb config directives

### The `/r` Location alongside `/svn`

For content hosting you may want to keep URLs backend-neutral.
For that purpose this image will expose `/r` as read-only variant of `/svn`.
This is done only if `-DAUTHN=anon`, where it's up to the reverse proxy to expose `/r` or not.
Also we only enable this with `RWEB`.

### `solsson/svn-httpd:proxied`

Deprecated. Use `-DAUTHN=anon` instead.

## [solsson/rweb](https://hub.docker.com/r/solsson/rweb/)

Because svn runs in httpd, and (mod_)php doesn't scale in httpd,
we recommend using [httpd](https://hub.docker.com/_/httpd/) and [php:fpm](https://hub.docker.com/_/php/) together, with rweb at the same path.
This image and the one below is meant to be used together. In Kubernetes that makes a good Pod.

## [solsson/rweb-httpd](https://hub.docker.com/r/solsson/rweb-httpd/)

`svn-httpd` with [rweb](https://github.com/Reposoft/rweb/) installed and enabled.

## [solsson/svnsync](https://hub.docker.com/r/solsson/svnsync/)

Tries to keep two repositories with same uuid in sync.

### `solsson/rweb:libs`

Deprecated. Omit the tag and get the full rweb instead.

## [solsson/svnclient](https://hub.docker.com/r/solsson/svnclient/)

Stable Debian with latest subversion release. Useful because apt repositories are far behind.

## Building

```
docker run -v /var/run/docker.sock:/var/run/docker.sock -v $(pwd)/:/source solsson/build-contract
```
