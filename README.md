screenshotr
===========

Video Screenshot Utility


## Install

```sh
$ composer require aequasi/screenshotr "~1.0.0"
```

## Usage

```php

// Second argument is the tmp dir the images are created in. Defaults to /tmp
$screenShotr = new \ScreenShotr\Core('/path/to/movie/file');

// Generate a single screenshot at the 300 second mark, returns a \SplFileInfo to a file in /tmp
$screenshot = $screenShotr->generateScreenshot(300);

// Generate screenshots for a file, every 5 seconds, starting at the 300 second mark
// Returns an array of \SpFileInfo objects like above
$screenshots = $screenShotr->generateScreenshotsEveryFrame(300, 5);
```
