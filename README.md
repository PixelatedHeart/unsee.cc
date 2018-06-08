Unsee.cc - Secure and private image hosting
==============

This is the official Git repository for the Unsee image hosting. The deployment on production server is as simple as:
```
git fetch
git reset --hard origin/master
rm application/configs/env.php
```
This is done for the sake of ease and transparency.


Installation
---------
To run your copy of Unsee locally you'll probably need 

Requirements
-----
- *nix environment
- Nginx
 - Secure link module
- Php
 - Redis module
 - Imagick module
- Redis
- Image magick
