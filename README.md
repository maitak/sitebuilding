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
1. Prepare the Drupal 8 repository via [Jenkins](https://jenkins.amazeelabs.com/job/AmazeeIO-SetupD8Github/build?delay=0sec)
2. use the future website url as repo_name i.e. example.com will get example_com (this makes it much easier for everyone to find the project)
3. The new repository will be available under `github.com/AmazeeLabs/$REPO_NAME`

### Prepare server environment

Ask Bastian or Michael for now - Create a Ticket in your Jira project and assign it to one of them.

### Install site

The following commands should be executed from the root of the newly created repository, via your terminal from ~/git/repo_name

1. Update `sites/default/aliases.drushrc.php` with the GIT Repo and the Sitename (given by Bastian or Michi) *(if it is already the site_name you are already good)*
1. Check your docker-compose.yml for the correct sitename.
1. `cd d8-starter`
  1. Run `git submodule update --init` 
1. Run `npm install`
1. Get Docker Running (https://docs.amazee.io/step_by_step_guides/get_your_drupal_site_running_on_amazeeio.html)
  1. `docker-compose up -d`
  1. `docker-compose exec --user drupal drupal bash`
   OR
  1. `docker-compose exec --user drupal d8-starter.docker.amazee.io bash`


1. Install Drupal, sync DB and files.
  1. `composer install` (we no longer use `drush site-install config_installer`)
  1. `dsql @dev`
  1. `dfiles @dev`

1. `exit` docker bash container or open new terminal window and go to the ~/git/repo_name
  1. `gulp compile` to compile the sass.

1. If you exited above: `docker-compose exec --user drupal drupal bash`
  1. `drush cr`
 
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
- Read this for more Config Management info: http://confluence.amazeelabs.com/display/KNOWLEDGE/Drupal+8+Configuration+Management

##  <a name="update-existing"></a>Updating a Drupal 8 installation based on d8-starter

1. Make sure that d8-starter is updated to the latest Drupal core version, if it's not, [update it first](#user-content-update-starter-core)

1. Make sure you have d8-starter available as a remote  
`git remote add d8-starter git@github.com:AmazeeLabs/d8-starter.git`
1. Apply d8-starter changes
  1. Fetch updates from d8-starter  
  `git fetch d8-starter`
    1. Merge changes into your Drupal 8 installation's dev  
    `chmod 755 sites/default && git merge d8-starter/core` (for the *8.0.x* version)
  OR  
  1. *If you want to upgrade from 8.0.x to the new 8.1.x version*:
    1. First remove the `/vendor` dir and commit this.
    2. add `/vendor` to `.gitignore` so it is not committed again (if not already done)
  1. Then run this (this also applies If you are just updating 8.1.x versions:)
    1. Run `chmod 755 sites/default && git merge d8-starter/core-8.1.3` 
    2. Fix any merge conflicts: Probably composer.json and composer.lock
    3. add `composer install` to all `before_deploy ` sections of `.amazeeio.yml`, E.g. https://github.com/AmazeeLabs/d8-starter/blob/dev/.amazeeio.yml 
    4. Add this and commit.
    
1. To then test with 8.1.X on _your local_ you'll need to run `composer install`. (inside Docker)
1. Update Drupal database (inside Docker)  
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
`chmod 755 sites/default && git fetch && git checkout -b core origin/core 2> /dev/null || git checkout core && git pull`
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
1. Update contrib modules
  1. Get list of available updates  
    `drush -n up`
  1. Check which modules should be updated (if a contrib module uses dev version, `drush up` may falsely report that update is required like `7.x-2.2+9-dev > 8.x-2.x-dev`, in this case, it's better to update the module manually)
  1. Run updates for certain modules  
    `drush -y up <MODULE1> <MODULE2> <...>`
  1. Commit/push changes (run `drush updb` on Dev, if you did the above in Vagrant)
