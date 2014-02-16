#!md
JDWordpress Plugin for CakePHP
==============================

©2014 by Jim Derry and balthisar.com

Purpose
-------

This plugin lets you integrate your Wordpress database into your CakePHP
site with minimum effort. It uses none of Wordpress’ functionality to
perform any of its work; it’s completely independent and requires only
Wordpress’s database.

Oh, unlike Wordpress, you can write your articles in MarkdownExtra
format. And, if you like, any source code can be syntax highlighted
with the built-in GeSHi.

Note that there’s currently *not* any support for Wordpress’ comments
system or user management at all. This is a design feature and not a
bug or oversight, since presumably you have an existing CakePHP application
and want to use your own security and user management systems.

Also there’s no support for generating Wordpress content. You should
use Wordpress and its excellent tools for that, otherwise why have
Wordpress at all? We’re only interested in showing Wordpress’ data.


Some Cool Features
------------------

The most prominent feature is that you don’t have to run Wordpress as
your front-end; you can run your CakePHP site and pull in content
wherever you want it. Because JDWordpress only requires the database
from Wordpress, you can run Wordpress backend in another directory,
subdomain, or even other machine.

Live support for index pages showing all tags, all categories, all
articles. Programmatic support for all taxonomies and a rich set
of structures and elements for displaying them on your own site.

"Magic" view method that accepts article slugs, a quantity of articles,
category slugs, author slugs, tag slugs -- whatever -- and delivers
the right page with the right results. No more forcing your users
to type URIs with `/tag/cool`, `category/swimming`, `author/balthisar`,
or `article/2014/02/01/my-cool-swimming-article-by-balthisar`.

Aside from whole blog pages, JDWordpress offers three ways to pull a
lot of different data into your own application for display.

1. Several controller actions are meant especially for `requestAction`
   calls, as these are easy for designers to implement. You
   shouldn't use them, but it’s quick and easy to do during development.

2. The Helper provides several methods that can pull data and provide
   a rendered element on demand for any of your application’s pages
   that you wish. You probably shouldn't use the helper for this,
   but they’re quick and easy to use during development.

3. The component can be used in your own AppController to make any
   data available to any or all of your views, and you can render
   this data easily with built-in or your own view elements. This
   is the CakePHP way of doing things.


API Documentation
-----------------
http://balthisar.github.io/JDWordpress


Quick Installation
------------------

1. Drop the whole plugin directory into your `app/Plugin` folder.

2. Modify your own `Config/database.php` file to add a new config
   called `$JDWordpressDB` properly setup for your Wordpress database.

3. Modify your own `Config/bootstrap.php` file to load the the plugin:
   `CakePlugin\::load(array('JDWordpress' => array('routes' => true)));`

4. Edit the file `app/Plugin/JDWordpress/Config/routes.php` so that
   the constant `JDWP_PATH` matches the URI path to your blog. *This is
   important* because the plugin needs to know where permalinks and
   such should point to.

5. The default views are in `JDWordpress/View/JDBlogPosts` per
   convention. They’re pretty spartan, but don’t be in a hurry to
   replace them. You should instead override them with your own
   versions in `app/View/Plugin/JDWordpress/JDBlogPosts/`, per
   CakePHP convention.

6. If you want to use the helper in your views, then add
   `JDWordpress.JDBlogPost` to your `$helpers` array in
   `Controllers/AppController.php` or in the individual controllers
   whose views you want the helper available.

7. If you want to use the component then add the component to your
   `$components` array in `Controllers/AppController.php` or in
   the individual controllers whose views you want to populate with
   data. Use this format for adding the component:

    `'JDWordpress.JDBlogPostData' => [ 'jd_vars' => [] ] );`

   You can look at values to put into the `jd_vars` array in the
   file `JDWordpress/Controller/Component/JDBlogPostDataComponent.php`,
   although you don’t have to put anything there for now.

8. Later (advanced) you’ll probably want to make changes to the file
   in `JDWordpress/Controller/Component/JDBlogPostDataComponent.php`.
   It’s not really configuration per se, but if you get tired of
   setting CSS classes for the plugin in your code per step 7, you can
   set the defaults in this file. Really, don’t worry about it for now.


Quick Start
-----------

Note than in step 4, the default is “blog” and that’s how I will refer to the
URI path for the rest of these instructions. Also let’s assume that you
already have at least a few published posts available in your Wordpress
database.

Try visiting these new sections of your site:

1. http://www.example.com/blog

2. http://www.example.com/blog/tags

3. http://www.example.com/blog/archives

4. http://www.example.com/blog/categories

5. http://www.example.com/blog/some-article-tag

6. http://www.example.com/blog/some-category-slug

7. http://www.example.com/blog/rss

8. http://www.example.com/blog/rss/some-category-slug

And so on.


Some Quick Examples
-------------------

All of the many pages above are nice if all you want to do is allow your
visitors to view whole pages. In general, though, you will want to show
last five posts, list all categories, etc., someplace else on your site.
These three examples show how you might do it.

1. `echo $this->JDBlogPost->tocRecent('developer');`

   This example uses the helper to return a fully rendered element with
   the data you requested. In this case it would return a rendered
   `tocRecent` element with a table of contents for some slug called
   'developer'. It could be tag, category, or author.


2. `echo $this->requestAction("/blog/tocRecent/developer");`

   Same as above, but it uses `requestAction` to get the view from
   the controller. Note that `requestAction` currently cannot access
   plugin controllers in the array format, and so this will cause a
   complete CakePHP request cycle. It’s not a recommended best practice
   to use.

3. `echo $this->element('requestAction/tocRecent', $dataForTocRecentSlug, ['plugin' => 'JDWordpress'] );`

   The same as above, but simply, harmlessly draws one of the built-in
   elements using data supplied by your controller. You could use your
   element (and not need the plugin array). Note that for organization
   all of the built-in elements are located in Elements/requestAction/
   of the plugin.

   This last example is a best-practice and the most Cake-like way of
   doing things. However it requires an extra step not shown in the
   example; you actually have to set the `$dataForTocRecentSlug`
   view variable in your controller (typically this might be your
   AppController). In this example the `AppController::beforeFilter`
   would have to have a line such as
   `$this->set( 'dataForTocRecentSlug', $this->JDBlogPostData->tocRecent('developer'));`
   which illustrates how to use the component (*Quick Installation*, step 7).


Use at the Source, Luke!
------------------------

The source code is fairly well (if unconventionally) documented. Even if
you just want to use the Plugin without knowing how it works, take a
look at the comments for the methods in the following:

1. `JDBlogPostsController` which has all of the actions (including
   `requestAction` actions) you might employ.

2. `JDBlogPostDataComponent` to see which CSS classes are assigned when
   using the default views and templates and helpers. It also has
   all of the component methods for populating your view data. **Look
   at them**.

3. `JDBlogPostHelper` to see which helper methods are available to you.


How to use MarkdownExtra
------------------------

Rather than requiring you add extra data fields to your Wordpress
database, we’ve simply adopted a shebang. Make sure you edit your
Wordpress posts in text format, and also make sure that the first
line consists of `#!md` -- that’s it. JDWordpress will take care
of the rest.

How to use GeSHi
----------------

JDWordpress introduces _rudimentary_ GeSHi support for syntax highlighting
blocks of code in your posts. Simply format your code such:

~~~~~~~~~~~~ {.geshi .html}
<pre>
<code class="geshi geshilinenumbers language">
...
</code>
</pre>
~~~~~~~~~~~~

Where “language” is the GeSHi supported language that you’s like to highlight,
and “geshilinenumbers” is optional; include it only if you want line numbers.

If you want fancier styling, take a look at the generated HTML for the
classes that GeSHi generates, and you can improve appearance in CSS.


License
-------
Copyright (c) 2013 by Jim Derry

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in
all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
THE SOFTWARE.
