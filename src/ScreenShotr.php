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

    public static function getMovie($file)
    {
        if (!file_exists($file)) {
            throw new \Exception("The file '{$file}' does not exist.'");
        }

        return new \ffmpeg_movie($file, false);
    }

    /**
     * @param \ffmpeg_movie $movie
     * @param               $frameNumber
     *
     * @return \ffmpeg_frame
     * @throws Exception\NoFrameException
     */
    public static function getFrame(\ffmpeg_movie $movie, $frameNumber)
    {
        $frame = $movie->getFrame($frameNumber);

        if ($frame === false) {
            throw new NoFrameException(
                "There is no frame located at {$frameNumber} in the movie: {$movie->getFilename()}."
            );
        }

        return $frame;
    }

    /**
     * @param \ffmpeg_frame $frame
     *
     * @return \SplFileInfo
     * @throws \Exception
     */
    public static function getScreenshot(\ffmpeg_frame $frame)
    {
        $gd_image = $frame->toGDImage();
        $file     = tempnam(static::$screenshotDirectory, 'ss_');
        if ($file === false) {
            throw new \Exception("Could not create a file in: ".static::$screenshotDirectory);
        }

        imagepng($gd_image, $file);
        imagedestroy($gd_image);

        return new \SplFileInfo($file);
    }

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
        return static::getScreenshot(static::getFrame(static::getMovie($file), $frameNumber));
    }

    public static function generateScreenshotEveryFrame($file, $frameCount = 1)
    {
        $movie       = static::getMovie($file);
        $screenShots = [];
        $i           = 1;
        while ($frame = static::getFrame($movie, $i)) {
            $screenShots[$i] = static::getScreenshot($frame);
            $i += $frameCount;
        }

        return $screenShots;
    }
}

