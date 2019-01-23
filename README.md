rtCamp/report-command
=====================

Generates a report for themes and plugins in a Multisite environment.

Quick links: [Overview](#overview) | [Using](#using) | [Installing](#installing) | [Contributing](#contributing)

## Overview

`wp report` lets you easily generate a report of all the themes and plugins in multisite environment.

`wp report` provides you the compiled report for all the sites on your MU setup with the details of all the active, network active and inactive plugins and themes.

```
$ wp report --all
+---------+-----------------+-------------+-----------------+--------------+----------+-----------+--------------+----------------+
| blog_id | domain          | site_status | current_theme   | parent_theme | akismet  | gutenberg | health-check | hello          |
+---------+-----------------+-------------+-----------------+--------------+----------+-----------+--------------+----------------+
| 1       | demo.test       | public      | twentyseventeen |              | inactive | active    | active       | network active |
| 2       | test1.demo.test | public      | twentysixteen   |              | active   | active    | inactive     | network active |
+---------+-----------------+-------------+-----------------+--------------+----------+-----------+--------------+----------------+
```

Want to pipe the results into another system? Use `--format=json` or `--format=csv` to render checks in a machine-readable format.


## Using

This package implements the following commands:

### wp report --all

Generate a report for all the plugins and themes for all the blogs.

### wp report --plugins

Generate a report for all the plugins for all the blogs.

### wp report --themes

Generate a report for all the themes for all the blogs.

## Installing

You can install this package with:

    wp package install git@github.com:rtCamp/wp-cli-report-command.git

You may face issues while installing regarding memory limits. For that you can refer to [this solution](https://make.wordpress.org/cli/handbook/common-issues/#php-fatal-error-allowed-memory-size-of-999999-bytes-exhausted-tried-to-allocate-99-bytes).

## Contributing

We appreciate you taking the initiative to contribute to this project.


### Reporting a bug

Think you’ve found a bug? We’d love for you to help us get it fixed.

Before you create a new issue, you should [search existing issues](https://github.com/rtCamp/wp-cli-report-command/issues) to see if there’s an existing resolution to it, or if it’s already been fixed in a newer version.

Once you’ve done a bit of searching and discovered there isn’t an open or fixed issue for your bug, please [create a new issue](https://github.com/rtCamp/wp-cli-report-command/issues/new). Include as much detail as you can, and clear steps to reproduce if possible.

### Creating a pull request

Want to contribute a new feature? Please first [open a new issue](https://github.com/rtCamp/wp-cli-report-command/issues/new) to discuss whether the feature is a good fit for the project.

Once you are done working on the feature or issue, create a pull request with appropriate information.
