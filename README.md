
# Repos' Subversion images

First of all, why do we use svn? We see it as a self-hosted *blob store* with an *audit trail*. It comes with an HTTP API and has permanent URLs to every revision at every path. It stores binary files efficiently and supports *versioned metadata* and individual file branching.

For write access REST we use [rweb](https://github.com/Reposoft/rweb/).
