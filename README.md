# Digital Signage

### Goals

* Keep it simple enough for non-technical people to easily use.
* Render PDFs as images and resize images to fill screen.  Additional document types are easily added.
* Display 2 flyers next to one another if they are small enough to fit side-by-side to maximize screen real estate.
* Support multiple screens.
* Automatically remove the slide when the expiration date and time occur.
* Allow URLs to be used as slides.
* Allow users to remove their slides.
* No authentication or authorization is included.  The scripts will rely on the web server to populate the Remote User environment field to determine ownership of slides.  

### Workflow

* The user uploads their flyer using the /forms/upload.php page.
* That page calls /upload.php to save the flyer.
* The user can remove their flyer or see all the current slides at /forms/remove.php.
* Any slide that is selected on that page is removed by /remove.php.
* The slide show is started by going to /get.php and including options as parameters. 

### Setup

1. Please review all the code for errors. 
1. Setup the external authorization program to protect the signage directories where you will place the upload and remove scripts.
1. Install PHP with sqlite extensions if you have not already.
1. Move files and directories into place.  Make sure the web server has read permission for all files and write permission for the db, log, and files directories.  Create links to the SignageLibrary.php and SignageOptions.php if you seperate the PHP scripts in different directories.  The scripts remove.php and upload.php as well as the forms directory will likely go into your SSL directory, and the get.php and files directory into the root directory of your server.  The directories db and log can go some place other than the root directory of the server.  Typically you would rename them signage and put them in /var/db/signage and /var/log/signage.
1.  Edit the SignageOptions.php file to indicate where all the files were moved to as well as the general signage options.
1.  Edit the /forms/upload.php and /forms/remove.php to add your site's window dressing.
1.  Edit the get.php file to modify the clock display if you wish.  The clock can be turned off by passing clock=0 as a URL parameter.
1.  Test all your edits as you make them and check the server log for possible errors if things are not working.
1.  Install Google Chrome on the computers that will drive the signs.  While most modern browsers work fine, the author finds that Chrome has the smallest delay between slides.
1.  Point Chrome's startup page to the get.php page, typically similar to: http://your.host/signage/get.php?screen=1
1.  Set Chrome to startup automatically when the computer boots with the startup option:  --start-fullscreen  
If the mouse pointer does not disappear, you will have to explore options to automatically move it to a bottom corner or have the OS start without it by passing -nocursor to startx.

### Bugs and New Features

Please report any errors you find to james.terry.riendeau at gmail.com.  Feature requests are also accepted.
