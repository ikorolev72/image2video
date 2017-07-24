#						image 2 video converter


##  What is it?
##  -----------
Image to video converter with audio, logo , text + box.


##  The Latest Version

	version 1.1 2017.07.16

##  Documentation
##  -------------


##  Features
##  ------------
	1.	Support the png, jpg and gif( with animation ) images 
	2.	Images can be animated with effects: rotate, rotate_ccw, zooming, pan
	3.	Videos can be concatenated with transition effects: fade, crossfade, overlay
	4.	Final video with fade_in/fade_out effects
	5.	Audio on/off with fade_in/fade_out effects
	6.	Logo on/off
	7.	Multistring text with any font/font_size/color
	8.	Textbox with any color/opacity
	


##  Installation
##  ------------
Install required tools for this application ( ffmpeg and bc ).
```
$ sudo apt-get -y install ffmpeg bc
```


Extract archive to your www/html directory and set the owner www-data for image2video-master directory .
For example, if you use apache http server:
```
unzip image2video-master.zip
mv image2video-master image2video
chmod +x image2video/bin/*
sudo cp -pr image2video /var/www/html/
sudo chown -R www-data:www-data /var/www/html/image2video
```


##  How to use
##  ------------
	1.	Open the page http://your_ip/image2video in any browser.
	2.	Add new project
	3.	Add images in the wizard
	4.	Add logo in the wizard
	5.	Add audio in the wizard
	6.	Edit effects. 
	7. 	Chnge any of 'Logo', 'Audio', 'Add new image', 'Change image order', 'Remove the image', 'Edit effect'


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















	