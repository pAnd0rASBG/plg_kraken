# Kraken Image Optimization Plugin for Joomla

Without doubt, one of the easier and most effective ways of optimizing your site starts with its images. Perfectly optimized images can cut your site's footprint by 50% or more, meaning (a little simplified) that your site will load twice as fast, because it only has half the size - on any device and without you having to dive into code and webserver configs.
Not only will visitors enjoy the faster experience, modern search engines, such as Google, rate your site under various factors and footprint/loading speed/current file sizes vs. optimized file sizes are factors, that are considered, influencing how good or bad the search engine will rate your site, eventually and ultimately where it will place it in the search results.
So much for the theory, at least. If you are anything like me, eventually you will post images to your site and maybe at that point have no access to your optimization app, are too stressed or you simply forget about it. You might also have Editors contributing to your site, who see even less motivation in optimizing their images or are simply "not tech savvy" and don't trust their skills to do image optimization.
So, eventually, your /images folder ends up as a potpourry of optimized and non-optimized images, at least partially reversing the positive effect, that optimized images have on your site. 
Even if you are that kind of well-organized person, who always has a grip on everything and you do optimize all your images - **isn't it tedious**? And even then - are your images really perfectly optimized? Photoshop's "Save for Web" won't do the trick.

### So, what is kraken.io and what can it do for me?
>Kraken.io is an image optimization and compression SaaS platform with additional manipulation capabilities such as image resizing. Our goal is to automatically shrink the byte size of images as much as possible, while keeping the visual information intact, and of consistently high quality to the extent that results never need to be manually checked for fidelity.
Read more about it at [https://kraken.io/about/](https://kraken.io/about/?ref=1689ba61070f)

In short, you can leave it to Kraken to get the best out of your images, while shrinking the Filesize as much, as possible.

#### ...and what does this plugin do?
This plugin makes it really easy for you to use Kraken in Joomla, do something really good for your site and don't waste another thought on it. In it's easiest form, you just install the plugin, enter your credentials and from there on every image, you upload in the **Joomla Media Manager** will be saved in it's Kraken-optimized form. 

## Installation and Usage

### Installation
The plugin comes as Joomla installation package, get the latest version [here](https://github.com/pAnd0rASBG/plg_kraken/releases) and install using the Joomla installer (Extensions > manage > install). After the installation has finished, go to the configuration (System > plugins, then search for "kraken"), enable the Plugin and enter your kraken.io API credentials. 
If you don't have any yet, [sign up here](https://kraken.io/signup/?ref=1689ba61070f). There is also a free account.Once signed up, visit the [API Dashboard](https://kraken.io/account/api-credentials/?ref=1689ba61070f) to find or generate your API Key and Secret.
When everything works fine, you will get an additional message about how much filesize was saved for every uploaded image.

### Advanced configuration
Advanced Configuration lets you either use the default settings or customize Quality, kept Metadata and image autorotation. 
Furthermore, you can switch on/off the automatic conversion to WebP and automatic lowercase filenames (**_both are on by default as best practice_**) and switch off SSL peer verification (**NOT RECOMMENDED!**). The latter usually happens on Windows if PHP is not configured with _curl.cainfo_.   
Descriptions for each Setting are in the tooltips on the Field Labels.


If you like the plugin, please feel free to donate to the Author, highly appreciated :)

[![Donate](https://www.paypalobjects.com/en_US/i/btn/btn_donateCC_LG.gif)](https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=PGLBLNLBN8RVQ)