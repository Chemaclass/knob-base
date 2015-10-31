# README #

### What's this repository? ###

* Knob-base
* Project base to use Knob MVC PHP. Framework
* This is a PHP MVC Framework for creating Wordpress templates easier and with more fun than ever before.
* Author: José María Valera Reales

## Knob-base is the kernel from [Knob-mvc](https://github.com/Chemaclass/knob-mvc/)
* This is a Framework base on MVC pattern. 
* Inspired by latest frameworks we have nowadays to web development like Symfony or Laravel.


### Models to get all values from your DB
* You can find all models as Entities from your DB in 'Knob\Models' (src/models/ directory).
* You will be provided with libraries to prepare your Actions and Filters (from Wordpress) 
* Also you will be able to get or create your own Widgets as new models. You have the basics in 'Knob\Widgets' (src/widgets/ directory)
* All of these on the best&easy way ever in 'Knob\libs' (src/libs/ directory)

### Views based on [Mustache](http://mustache.github.com/) templates
* All you have to care basically are your templates. Thats why we choose Mustache. Is simple, flexible and funny.
* This will be your main part; that's becuase your "Wordpress Template", dont forget it ;-)

### Controllers to pull everything together
* From 'Knob\Controllers' (src/controllers/ directory) 
* You will be provided a ´´´Knob\Controllers\BaseController´´´ to extends your own controllers. 
Then from your controller just need to do something like:
´´´php

use Knob\Controllers\BaseController as KnobBaseController;
use Knob\Controllers\HomeControllerInterface;

class HomeController extends KnobBaseController implements HomeControllerInterface
	
	/**
     * home.php
     */
    public function getHome()
    {
        $args = [
            'posts' => Post::getAll(get_option('posts_per_page'))
        ];
        return $this->renderPage('base/home', $args);
    }
    
´´´

# Before the start... you'll need! #

### Install ruby and compass ###
* sudo apt-get install ruby
* sudo gem update --system
* sudo apt-get install ruby1.9.1-dev
* sudo gem install compass
* sudo gem install rake

### Then, you will be able to compile the scss in the directory of your project: ###
* /knob-mvc $> rake watch_scss

### You'll need a PHP graphics library to be able to use the image editor: ###
* apt-get install php5-imagick php5-gd
* service apache2 reload 

