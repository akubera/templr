Templr PHP Templating Library
=============================

Templr aims to be an easy to use html generating template library for php.

CURRENTLY IT DOES NOTHING. DO NOT ACTUALLY TRY TO USE THIS.

The main goal is to easily provide the ability to write different components of a website using any supported template languages.

Blocks
------

Each component of a page is defined with a block identifier tag, specifying the language the following text is written in (its engine), and a unique name to identify the block.
Multiple blocks can be included in one file.

[engine:block_name]

The block tag must start on the beginning of the line. 

The templr engines receive all text following the tag, until the next one is encountered.
The engine should produce raw php code which gets saved to a cached file, allowing subsequent hits to be run simply as a php script, with minimal pre-processing.

Engines
-------

?????


Using Templr
--------------
 
Somewhere in your code (a common.php or config.php type file) the following variables must be defined for templr to work:

TEMPLR_ROOT
    The directory where template files should be searched for

TEMPLR_WEB_ROOT
    The current website's address - necessary if the site is running from a specific uri 
    example.com/~user0/a_templr_example

TEMPLR_CACHE_DIR
    The directory to store the generated .php files - these are managed by templr and should not be tampered with.
    In fact, giving only the web server read and write access is probably a good idea.

TEMPLR_EXT
    The file name extension of the template files (should use .php or .tmpl)

TEMPLR_DEFAULT_NAME
    The default block identifier to render first. (default name is html_root)

Specifically, if you wanted to use .foo as the template extensions, before you call any templr code, this line should appear

`define('TEMPLR_EXT', '.foo');`

along with all the others.

After defining the environment, one must simply include the init file in the templr directory.

`require_once('path_to_templr/templr/init.php');`
 
All templr classes are in the namespace \templr.

The recommended way to use templr is to create a templr webpage object, initialized with a template which either gets printed or rendered.

`
$page = new \templr\WebPage("index");
$page->Render();
`

In the TEMPLR_ROOT path, there should be a file named index (or rather index.TEMPLR_EXT) which would look something like

```
#
# index.foo
#

[html:html_root]
<!doctype html>
<html>
  <head>
    {html_head}
  </head>
  <body>
    {htm_body}
  </body>
</html>

[html:html_head]
  <title>Example Page Title</title>

[text:html_body]
This is generated using the text engine - all html characters will be escaped, so everything looks exactly as typed.
So I can type &lt;b&gt;This is not bold&lt;/b&gt; without formatting.
```

which would render:

```
<!doctype html>
<html>
  <head>
    <title>Example Page Title</title>
  </head>
  <body>
This is generated using the text engine - all html characters will be escaped, so everything looks exactly as typed.
So I can type &lt;b&gt;This is not bold&lt;/b&gt; without formatting.
  </body>
</html>
```

More to come later...
