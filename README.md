# D8 project

Sample of a project I built in my company in Drupal 8 CMS


## Public Environments

Development environments are stored on server at `~/environments/<env>` and accessible through sub-domains.

All environments except live/production use `robots.txt` to prevent web bots from crawling them using:

```
User-agent: *
Disallow: /
```


### 1. Dev

**Purpose**  
> Allow _project managers_ oversee development progress.

**URL**: XXXX  
**Path**: `~/environments/dev`  
**HTTP Protection**: dev@Ceres  
**Drush alias**: @ceres_f.dev  


### 2. Stage

**Purpose**  
> Allow _client_ test and stage content.

**URL**: xxx
**Path**: `~/environments/stage`  
**HTTP Protection**: stage@Ceres  
**Drush alias**: @ceres_f.stage  

---
