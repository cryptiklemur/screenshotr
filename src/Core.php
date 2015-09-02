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

use FFMpeg\Coordinate\TimeCode;
use FFMpeg\FFMpeg;
use FFMpeg\FFProbe;
use FFMpeg\Media\Frame;
use FFMpeg\Media\Video;

/**
 * @author Aaron Scherer <aequasi@gmail.com>
 */
class Core
{
    /**
     * @var  $file
     */
    private $file;

    /**
     * @var string $screenshotDirectory
     */
    private $screenshotDirectory;

    /**
     * @var FFMpeg $ffmpeg
     */
    private $ffmpeg;

    /**
     * @var FFProbe $ffprobe
     */
    private $ffprobe;

    /**
     * @var \FFMpeg\Media\Audio|Video $movie
     */
    private $movie;

    /**
     * @var FFProbe\DataMapping\Format $info
     */
    private $info;

    public function __construct($file, $screenshotDirectory = '/tmp')
    {
        $this->file                = $file;
        $this->screenshotDirectory = $screenshotDirectory;
        $this->ffmpeg              = FFMpeg::create();
        $this->ffprobe             = FFProbe::create();
        $this->movie               = $this->ffmpeg->open($file);
        $this->info                = $this->ffprobe->format($file);
    }

    /**
     * @return FFMpeg
     */
    public function getFfmpeg()
    {
        return $this->ffmpeg;
    }

    /**
     * @return FFProbe
     */
    public function getFfprobe()
    {
        return $this->ffprobe;
    }

    /**
     * @return mixed
     */
    public function getFile()
    {
        return $this->file;
    }

    /**
     * @return FFProbe\DataMapping\Format
     */
    public function getInfo()
    {
        return $this->info;
    }

    /**
     * @return \FFMpeg\Media\Audio|Video
     */
    public function getMovie()
    {
        return $this->movie;
    }

    /**
     * @param int $frameNumber
     *
     * @return Frame
     */
    public function getFrame($frameNumber)
    {
        return $this->movie->frame(TimeCode::fromSeconds($frameNumber));
    }

    /**
     * @param Frame $frame
     *
     * @throws \Exception
     * @return \SplFileInfo
     */
    public function getScreenshot(Frame $frame)
    {


        if (!file_exists($this->screenshotDirectory)) {
            mkdir($this->screenshotDirectory, 0777, true);
        }

        $file = tempnam($this->screenshotDirectory, 'ss_');
        $file = rename($file, $file.'.png') ? $file.'.png' : $file;

        if ($file === false) {
            throw new \Exception("Could not create a file in: ".$this->screenshotDirectory);
        }

        try {
            $frame->save($file, true);
        } catch (\Exception $e) {
            // Do nothing
        }

        chmod($file, 0777);

        return new \SplFileInfo($file);
    }

    /**
     * @param int $frameNumber
     *
     * @return \SplFileInfo
     * @throws \Exception
     */
    public function generateScreenshot($frameNumber)
    {
        return $this->getScreenshot($this->getFrame($frameNumber));
    }

    /**
     * @param int  $start
     * @param int  $frameCount
     * @param bool $skipException
     *
     * @return \SplFileInfo[]
     * @throws \Exception
     */
    public function generateScreenshotEveryFrame($start = 1, $frameCount = 1, $skipException = true)
    {
        $screenShots = [];
        while ($start <= $this->info->get('duration')) {
            try {
                $screenShots[$start] = $this->generateScreenshot($start);
            } catch (\Exception $e) {
                if (!$skipException) {
                    throw $e;
                }
            }

            $start += $frameCount;
        }

        return $screenShots;
    }
}

