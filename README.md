# Amazee Drupal 8 Starter

How to:

- [create new project](#user-content-create-new)
- [update existing project](#user-content-update-existing)
- [work with d8-starter (improve, update, etc.)](#user-content-improve-starter)

The DEV site could be found at [d8-starter.io.dev.dev1.compact.amazee.io](http://d8-starter.io.dev.dev1.compact.amazee.io/) (user 1 login: `admin`, password: `D8rules!!!`)

This website is pre-configured to meet most of our usual needs on new projects.

This installation is used as a base for new Amazee Drupal 8 installations. Unlike the [Drupal 7 process](https://github.com/AmazeeLabs/new-site.com#readme), the database of this installation is not used. Instead, we import already prepared configuration to a new installation.

## <a name="create-new"></a>Creating new Drupal 8 installation from d8-starter

### Prepare repository

1. Clone the d8-starter repository  
`git clone git@github.com:AmazeeLabs/d8-starter.git <PATH/TO/NEW/LOCATION>`
1. Go to the newly created folder  
`cd <PATH/TO/NEW/LOCATION>`
1. Update and sync sub-modules  
`git submodule update --init && git submodule sync`
1. At [github.com/organizations/AmazeeLabs/repositories/new](https://github.com/organizations/AmazeeLabs/repositories/new) create a new private repository.
1. Copy the Git URL of the newly created repository
1. Update the URL of the cloned repository  
`git remote set-url origin <REPOSITORY_URL>`
1. Push the code  
`git push`
1. Add the Tech Team to the repository on Github
`https://github.com/AmazeeLabs/<SITENAME>/settings/collaboration`

### Prepare environment

TBD (Ask Michael or Bastian for now)

### Install site

The following commands should be executed from the root of the newly created repository being logged in as a newly created bash user in the vagrant/dev.

1. Update `$settings['hash_salt']` in `sites/default/settings.all.php` (go to http://www.miniwebtool.com/sha512-hash-generator/ enter some random text, use generated hash)
1. Update `sites/default/aliases.drushrc.php` with the GIT Repo and the Sitename (given by Bastian or Michi)
1. Install Drupal  
`drush site-install config_installer` (this will take some minutes)
1. Remove this file (README.md)  
`rm README.md`  
or replace its contents with relevant information  
`echo 'Repository for the <SITENAME>.' > README.md`
1. Commit and push changes  
`git add . && git commit -m '<TICKET-123> Prepared <SITENAME> installation' && git push`

### Configure site

Review the configuration pages to see if some information (like the site name) should be updated. The most critical paths:

- `admin/config/system/site-information`
- `admin/config/regional/settings`
- `admin/config/media/file-system`

After you done, export configuration and commit/push changes.

##  <a name="update-existing"></a>Updating a Drupal 8 installation based on d8-starter

1. Make sure that d8-starter is updated to the latest Drupal core version, if it's not, [update it first](#user-content-update-starter-core)
1. Make sure you have d8-starter available as a remote  
`git remote add d8-starter git@github.com:AmazeeLabs/d8-starter.git`
1. Apply d8-starter changes 
  1. Fetch updates from d8-starter  
  `git fetch d8-starter`
  1. Merge changes into your Drupal 8 installation's dev  
  `git merge d8-starter/core`
  1. Update Drupal database (inside Vagrant)  
  `drush updb`

##  <a name="improve-starter"></a>Working on d8-starter

### Branches

- core (https://github.com/AmazeeLabs/d8-starter/tree/core)  
  Contains the current 8.x.x core we're using with our core patches applied
- dev (https://github.com/AmazeeLabs/d8-starter/tree/dev)  
  Our actual starter site with beaker, cointrib modules, config, etc.

### Working on dev server

After you made any configuration change run `drush config-export -y` and commit/push changes. Also, don't forget to review/commit/push any code changes you (or someone) had made.

### Working on Vagrant

1. If `d8-starter_io` local Vagrant environment is not yet prepared, do it [as usual](http://confluence.amazeelabs.com/display/KNOWLEDGE/Amazee.IO+Vagrant), so clone this repository, initialize submodules, set mountings, and reload Vagrant.
1. Pull the newest code and sync the database with `drush sql-sync @dev1 default`
1. Rebuild cache with `drush cr`
1. Do your changes
1. Export configuration, commit/push the changes
1. To get your changes on dev: wait for the deployment, import the configuration

### <a name="update-starter-core">Updating Drupal core

Do this in the Vagrant!

1. Checkout core branch  
`git fetch && git checkout -b core origin/core 2> /dev/null || git checkout core && git pull`
1. Add Drupal Git Repository as remote  
`git remote add drupal http://git.drupal.org/project/drupal.git`
1. Fetch tags  
`git fetch --tags drupal`
1. Merge drupal into core branch  
`git merge 8.0.1`
1. Fix maybe existing merge conflicts because of core patches
1. Publish core branch  
`git push origin core`
1. Switch to dev branch  
`git checkout dev`
1. Merge core branch into dev branch  
`git merge core`
1. Run `drush updb` in Vagrant and test site still working
1. Publish dev branch  
`git push origin dev`
1. Wait for the deployment to happen (see [#missioncontrol](https://amazee.slack.com/messages/missioncontrol/) Slack channel)
1. Run `drush updb` at the @dev server

### Updating pre-installed contrib modules

1. Get list of available updates  
  `drush up -n`
1. Check which modules should be updated (if a contrib module uses dev version, `drush up` may falsely report that update is required like `7.x-2.2+9-dev > 8.x-2.x-dev`, in this case, it's better to update the module manually)
1. Run updates for certain modules  
  `drush up <MODULE1> <MODULE2> <...> -y`
1. Commit/push changes (run `drush updb` on Dev, if you did the above in Vagrant)
