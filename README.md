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

Again, none of these actually work and may not currently or _ever_ exist.

#### HTML
Given html code, produce html code
Pretty much interprets everything as given - no transformations needed

#### Text

Given formatted text, produce html which escapes the 'special chars' 

#### Markdown

Might be nice to render markdown automatically.

#### HAML/Jade

Generate html from haml or jade markup


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
    <p id="text_engine">
      {txt_body}
    </p>
    <p id="html_engine">
      {html_body}
    </p>
    <p id="jade_engine">
      {jb}
    </p>
  </body>
</html>

[html:html_head]
  <title>Example Page Title</title>

[text:txt_body]
This is generated using the text engine - all html characters will be escaped, so everything looks exactly as typed.
So I can type <b>This is not bold</b> (without formatting).

[text:txt_body]
This is generated using the html engine - all html characters will be drawn as such, so everything looks exactly as typed.
So I can type <b>This <u>is</u> bold</b> (uses formatting).

[jade:jb]

span#aww_jade Bringing jade to php is a good thing, right?
ul#jade_list
  li.selected: a(href="#") item_one
  li: a(href="#") item_two

```

which would render:

```
<!doctype html>
<html>
  <head>
    <title>Example Page Title</title>
  </head>
  <body>
    <p id="text_engine">
      This is generated using the text engine - all html characters will be escaped, so everything looks exactly as typed.
So I can type &lt;b&gt;This is not bold&lt;/b&gt; without formatting.
    </p>
    <p id="html_engine">
      This is generated using the html engine - all html characters will be 
drawn as such, so everything looks exactly as typed. So I can type <b>This <u>is</u> bold</b> (uses formatting).
    </p>
    <p id="jade_engine">
      <span id='aww_jade'>Bringing jade to php is a good thing, right?</span>
      <ul id='jade_list'>
        <li class='selected'> <a href="#"> item_one </a> </li>
        <li> <a href="#"> item_two </a> </li>
      </ul>
    </p>
  </body>
</html>
```

More to come later...
