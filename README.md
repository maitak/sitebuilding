# Amazee Drupal 8 Starter

The DEV site could be found at [d8-starter.io.dev.dev1.compact.amazee.io](http://d8-starter.io.dev.dev1.compact.amazee.io/) (user 1 login: `admin`, password: `D8rules!!!`)

This website is pre-configured to meet most of our usual needs on new projects.

This installation is used as a base for new Amazee Drupal 8 installations. Unlike the [Drupal 7 process](https://github.com/AmazeeLabs/new-site.com#readme), the database of this installation is not used. Instead, we import already prepared configuration to a new installation.

## Creating new Drupal 8 installation from d8-starter

### Prepare repository

1. Clone the d8-starter repository  
`git clone git@github.com:AmazeeLabs/d8-starter.git <PATH/TO/NEW/LOCATION>`
1. go to the newly created folder  
`cd <PATH/TO/NEW/LOCATION>`
1. At [github.com/organizations/AmazeeLabs/repositories/new](https://github.com/organizations/AmazeeLabs/repositories/new) create a new private repository.
1. Copy the Git URL of the newly created repository
1. Update the URL of the cloned repository  
`git remote set-url origin <REPOSITORY_URL>`
1. Push the code  
`git push`

### Prepare environment

TBD (Ask Michael or Bastian for now)

### Install site

The following commands should be executed from the root of the newly created repository being logged in as a newly created bash user in the vagrant/dev.

1. Generate hash salt (go to http://www.miniwebtool.com/sha512-hash-generator/ enter some random text, generate hash, copy hash, add has here: `sites/default/settings.all.php`)
1. Update `sites/default/aliases.drushrc.php` with the GIT Repo and the Sitename (given by Bastian or Michi)
1. Install Drupal  
`drush site-install --keep-config` (this will take some minutes)
1. Prepare the configuration to be imported  
`drush php-script profiles/amazee/config_prepare.php`
1. Import configuration  
`drush config-import`  
1. Remove helper script  
`rm -rf profiles/amazee`
1. Remove this file (README.md)  
`rm README.md`  
or replace its contents with relevant information  
`echo 'Repository for the <SITENAME>.' > README.md`
1. Update sitename and Git URL in the sites/default/aliases.drushrc.php
1. Commit and push changes  
`git add . && git commit -m '<TICKET-123> Prepared <SITENAME> installation' && git push`

### Configure site

Review the configuration pages to see if some information (like the site name) should be updated. The most critical paths:

- `admin/config/system/site-information`
- `admin/config/regional/settings`
- `admin/config/media/file-system`

After you done, export configuration and commit/push changes.

## Updating a Drupal 8 installation based on d8-starter

### Make sure you have d8-starter available as a remote
1. Do this once:  
`git remote add d8-starter git@github.com:AmazeeLabs/d8-starter.git`

### Apply d8-starter changes 
1. Fetch updates from d8-starter  
`git fetch d8-starter`
1. Merge changes into your Drupal 8 installation's dev  
`git merge d8-starter/dev`

## Working on d8-starter

### Branches

- core (https://github.com/AmazeeLabs/d8-starter/tree/core)  
  Contains the current 8.x.x core we're using with our core patches applied
- dev (https://github.com/AmazeeLabs/d8-starter/tree/dev)  
  Our actual starter site with beaker, contrib modules, config, etc.

### Working on dev server

After you made any configuration change run `drush config-export -y` and commit/push changes. Also, don't forget to review/commit/push any code changes you (or someone) had made.

### Working on Vagrant

1. If `d8-starter_io` local Vagrant environment is not yet prepared, do it [as usual](http://confluence.amazeelabs.com/display/KNOWLEDGE/Amazee.IO+Vagrant), so clone this repository, set mountings, and reload Vagrant.
1. Pull the newest code and sync the database with `drush sql-sync @dev1.compact default`
1. Rebuild cache with `drush cr`
1. Do your changes
1. TODO: we need more info about `drush config-merge`
1. Export configuration, commit/push the changes
1. On dev: pull the changes and import the configuration
1. Remember to git submodule update --init to pull in modules (modules/contrib)

### Merging newest Drupal 8 upstream

1. Checkout core branch  
`git checkout core`
1. Add Drupal Git Repository as remote  
`git remote add drupal http://git.drupal.org/project/drupal.git`
1. Fetch tags  
`git fetch --tags drupal`
1. Merge drupal into core branch  
`git merge drupal/8.0.0-rc2`
1. Fix maybe existing merge conflicts because of core patches
1. Publish core branch  
`git push origin core`
1. Switch to dev branch  
`git checkout dev`
1. Merge core branch into dev branch  
`git merge core`
1. Test site still working
1. Publish dev branch  
`git push origin dev`
