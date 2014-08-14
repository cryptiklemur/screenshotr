<?php

/**
 * This file is part of ScreenShotr
 *
 * (c) Aaron Scherer <aequasi@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE
 */

namespace ScreenShotr;

use ScreenShotr\Exception\NoFrameException;

/**
 * @author Aaron Scherer <aequasi@gmail.com>
 */
class Core
{
    /**
     * @var string $screenshotDirectory
     */
    public static $screenshotDirectory = '/tmp';

    /**
     * @param $file
     * @param $frameNumber
     *
     * @return string
     * @throws Exception\NoFrameException
     * @throws \Exception
     */
    public static function generateScreenshot($file, $frameNumber)
    {
        if (!file_exists($file)) {
            throw new \Exception("The file '{$file}' does not exist.'");
        }

        $movie = new \ffmpeg_movie($file, false);
        $frame = $movie->getFrame($frameNumber);

        if ($frame === false) {
            throw new NoFrameException("There is no frame located at {$frameNumber} in the movie: {$file}.");
        }

        $gd_image = $frame->toGDImage();
        $file = tempnam(static::$screenshotDirectory, 'ss_');
        if ($file === false) {
            throw new \Exception("Could not create a file in: ".static::$screenshotDirectory);
        }


        imagepng($gd_image, $file);
        imagedestroy($gd_image);

        return $file;
    }
}

