#						image 2 video converter and youtube uploader


##  What is it?
##  -----------
Image to video converter with audio, logo , text + box.


##  The Latest Version

	version 1.4 2017.09.04

##  Documentation
##  -------------


##  Features
##  ------------
	1.	Support the png, jpg and gif( with animation ) images 
	2.	Images can be animated with effects: rotate, rotate_ccw, zooming, pan
	3.	Videos can be concatenated with transition effects: fade, crossfade, overlay
	4.	Final video with fade_in/fade_out effects
	5.	Several audio tracks, selected or randomize track, audio on/off, fade_in/fade_out effects
	6.	Logo resize, location, on/off
	7.	Crest resize, location, on/off
	8.	Multistring text with any font/font_size/color
	9.	Textbox with any color/opacity
	10.	Youtube uploads
	11.	Google maps screenshots
	12. Import crest and map screenshots to main images array



##  Installation
##  ------------
Install required tools for this application ( ffmpeg and bc ) and default font.
```
$ sudo apt-get -y install ffmpeg bc fonts-roboto
```

Extract archive to your www/html directory and set the owner www-data for image2video-master directory .
For example, if you use apache http server:
```
tar xvf image2video.tar 
sudo cp -pr image2video /var/www/html/
sudo chown -R www-data:www-data /var/www/html/image2video
```

For upload to youtube, you need install the 'youtube-upload' project `https://github.com/tokland/youtube-upload`
```
sudo apt-get -y install pip
sudo pip install --upgrade google-api-python-client progressbar2
wget https://github.com/tokland/youtube-upload/archive/master.zip
unzip master.zip
cd youtube-upload-master
sudo python setup.py install
```

You have the predefined the credential and client_id files, the are in `image2video/uploads` directory.
You need set the absolute path to thise files in `video2image.php` :

```
public static $youtube_client_id="/var/www/html/image2video/uploads/1502285316715b1958831c????.json"; # youtube api key
public static $youtube_crentials="/var/www/html/image2video/uploads/15026718274b5c729e0ae2????.json"; # youtube api credenials
```

For Google map screenshots you need install `phantomjs` tool ( `http://phantomjs.org/` ).
Fast installation ( from `https://gist.github.com/julionc/7476620`):
```
sudo aptitude update
sudo aptitude install build-essential chrpath libssl-dev libxft-dev \
  libfreetype6 libfreetype6-dev libfontconfig1 libfontconfig1-dev
PHANTOM_JS="phantomjs-2.1.1-linux-x86_64"
cd ~
wget https://bitbucket.org/ariya/phantomjs/downloads/$PHANTOM_JS.tar.bz2
tar -xvjf $PHANTOM_JS.tar.bz2
sudo mv $PHANTOM_JS /usr/local/share
sudo ln -s /usr/local/share/$PHANTOM_JS/bin/phantomjs /usr/local/bin
phantomjs --version
```


#### Upload the files largest 2mb
By default, php do not upload files largest 2mb. 
If you need upload images and audio largest than 2mb, you need set the values in php.ini or .htaccess by this doc
`https://www.sitepoint.com/upload-large-files-in-php/`
and restart apache.



##  How to use
##  ------------
	1.	Open the page http://your_ip/image2video in any browser.
	2.	Add new project
	3.	Add images in the wizard
	4.	Add logo in the wizard
	5.	Add audio in the wizard
	6.	Edit effects. 
	7.	Change any of 'Logo', 'Crest', 'Audio', 'Add new image', 'Change image order', 'Remove the image', 'Edit effect', etc
	8.	Upload your video to youtube

#### Recomendation
	1.	The best results for the animation of images you get with the settings of "rotate", "rotate_ccw" and "zooming".
	2.	Use images with good resolution.
	3.	Some fonts can break the processing of ffmpeg. You can see this in the log files.
	4.	If you are going to use FullHD (1920x1080) or a large resolution, you need a lot of memory on your host (4 GB or more). In the other case, ffmpeg may break.

## Special variables
##  ------------
You can set the next variables in `image2video.php` :
```
public static $output_width=1280;
public static $output_height=720;
public static $font_size=50; # default value for font_size
public static $font='/usr/share/fonts/truetype/roboto/hinted/Roboto-Bold.ttf'; # default value for font

public static $google_map_api_key="AIzaSyCgO4NtJv7hqYId6_yohnS?????";  # your google map api key
public static $youtube_client_id="/var/www/html/image2video/uploads/1502285316715b1958831c????.json"; # youtube api key
public static $youtube_crentials="/var/www/html/image2video/uploads/15026718274b5c729e0ae2????.json"; # youtube api credenials
```




##  Bugs
##  ------------
	1. Upload files have restriction in 2mb ( it is the default php settings )



  Licensing
  ---------
	GNU

  Contacts
  --------

     o korolev-ia [at] yandex.ru
     o http://www.unixpin.com

