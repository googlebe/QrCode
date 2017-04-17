<?php

/*
 * (c) Jeroen van den Enden <info@endroid.nl>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Endroid\QrCode;

use Endroid\QrCode\Exception\InvalidLabelFontPathException;
use Endroid\QrCode\Exception\InvalidPathException;
use Endroid\QrCode\Exception\MissingWriterException;
use Endroid\QrCode\Writer\BinaryWriter;
use Endroid\QrCode\Writer\DataUriWriter;
use Endroid\QrCode\Writer\EpsWriter;
use Endroid\QrCode\Writer\PngWriter;
use Endroid\QrCode\Writer\SvgWriter;
use Endroid\QrCode\Writer\WriterInterface;

class QrCode
{
    const LABEL_FONT_PATH_DEFAULT = __DIR__ . '/../font/open_sans.ttf';

    /**
     * @var WriterInterface[]
     */
    private $writers;

    /**
     * @var string
     */
    private $text;

    /**
     * @var int
     */
    private $size;

    /**
     * @var int
     */
    private $quietZone;

    /**
     * @var array
     */
    private $foregroundColor = [
        'r' => 0,
        'g' => 0,
        'b' => 0
    ];

    /**
     * @var array
     */
    private $backgroundColor = [
        'r' => 255,
        'g' => 255,
        'b' => 255
    ];

    /**
     * @var string
     */
    private $encoding = 'UTF-8';

    /**
     * @var ErrorCorrectionLevel
     */
    private $errorCorrectionLevel;

    /**
     * @var string
     */
    private $label;

    /**
     * @var int
     */
    private $labelFontSize = 16;

    /**
     * @var string
     */
    private $labelFontPath = self::LABEL_FONT_PATH_DEFAULT;

    /**
     * @var LabelAlignment
     */
    private $labelAlignment;

    /**
     * @var array
     */
    private $labelMargin = [
        't' => 0,
        'r' => 0,
        'b' => 0,
        'l' => 0,
    ];

    /**
     * @var string
     */
    private $logoPath;

    /**
     * @var int
     */
    private $logoSize;

    /**
     * @var bool
     */
    private $validateResult = false;

    /**
     * @param string $text
     */
    public function __construct($text = '')
    {
        $this->writers = [];
        $this->writersByExtension = [];

        $this->text = $text;

        $this->errorCorrectionLevel = new ErrorCorrectionLevel(ErrorCorrectionLevel::LOW);
        $this->labelAlignment = new LabelAlignment(LabelAlignment::CENTER);

        $this->registerBuiltInWriters();
    }

    protected function registerBuiltInWriters()
    {
        $this->registerWriter(new BinaryWriter($this));
        $this->registerWriter(new DataUriWriter($this));
        $this->registerWriter(new EpsWriter($this));
        $this->registerWriter(new PngWriter($this));
        $this->registerWriter(new SvgWriter($this));
    }

    /**
     * @param WriterInterface $writer
     */
    public function registerWriter(WriterInterface $writer)
    {
        if (!isset($this->writers[get_class($writer)])) {
            $this->writers[get_class($writer)] = $writer;
        }
    }

    /**
     * @param string $text
     * @return $this
     */
    public function setText($text)
    {
        $this->text = $text;

        return $this;
    }

    /**
     * @return string
     */
    public function getText()
    {
        return $this->text;
    }

    /**
     * @param int $size
     * @return $this
     */
    public function setSize($size)
    {
        $this->size = $size;

        return $this;
    }

    /**
     * @return int
     */
    public function getSize()
    {
        return $this->size;
    }

    /**
     * @param int $quietZone
     * @return $this
     */
    public function setQuietZone($quietZone)
    {
        $this->quietZone = $quietZone;

        return $this;
    }

    /**
     * @return int
     */
    public function getQuietZone()
    {
        return $this->quietZone;
    }

    /**
     * @param array $foregroundColor
     * @return $this
     */
    public function setForegroundColor($foregroundColor)
    {
        $this->foregroundColor = $foregroundColor;

        return $this;
    }

    /**
     * @return array
     */
    public function getForegroundColor()
    {
        return $this->foregroundColor;
    }

    /**
     * @param array $backgroundColor
     * @return $this
     */
    public function setBackgroundColor($backgroundColor)
    {
        $this->backgroundColor = $backgroundColor;

        return $this;
    }

    /**
     * @return array
     */
    public function getBackgroundColor()
    {
        return $this->backgroundColor;
    }

    /**
     * @param string $encoding
     * @return $this
     */
    public function setEncoding($encoding)
    {
        $this->encoding = $encoding;

        return $this;
    }

    /**
     * @return string
     */
    public function getEncoding()
    {
        return $this->encoding;
    }

    /**
     * @param string $errorCorrectionLevel
     * @return $this
     */
    public function setErrorCorrectionLevel($errorCorrectionLevel)
    {
        $this->errorCorrectionLevel = new ErrorCorrectionLevel($errorCorrectionLevel);

        return $this;
    }

    /**
     * @return string
     */
    public function getErrorCorrectionLevel()
    {
        return $this->errorCorrectionLevel->getValue();
    }

    /**
     * @param string $label
     * @param int $labelFontSize
     * @param string $labelFontPath
     * @param string $labelAlignment
     * @param array $labelMargin
     * @return $this
     */
    public function setLabel($label, $labelFontSize = null, $labelFontPath = null, $labelAlignment = null, $labelMargin = null)
    {
        $this->label = $label;

        if ($labelFontSize !== null) {
            $this->setLabelFontSize($labelFontSize);
        }

        if ($labelFontPath !== null) {
            $this->setLabelFontPath($labelFontPath);
        }

        if ($labelAlignment !== null) {
            $this->setLabelAlignment($labelAlignment);
        }

        if ($labelMargin !== null) {
            $this->setLabelMargin($labelMargin);
        }

        return $this;
    }

    /**
     * @return string
     */
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * @param int $labelFontSize
     * @return $this
     */
    public function setLabelFontSize($labelFontSize)
    {
        $this->labelFontSize = $labelFontSize;

        return $this;
    }

    /**
     * @return int
     */
    public function getLabelFontSize()
    {
        return $this->labelFontSize;
    }

    /**
     * @param string $labelFontPath
     * @return $this
     * @throws InvalidPathException
     */
    public function setLabelFontPath($labelFontPath)
    {
        $labelFontPath = realpath($labelFontPath);

        if (!is_file($labelFontPath)) {
            throw new InvalidPathException('Invalid label font path: ' . $labelFontPath);
        }

        $this->labelFontPath = $labelFontPath;

        return $this;
    }

    /**
     * @return string
     */
    public function getLabelFontPath()
    {
        return $this->labelFontPath;
    }

    /**
     * @param string $labelAlignment
     * @return $this
     */
    public function setLabelAlignment($labelAlignment)
    {
        $this->labelAlignment = new LabelAlignment($labelAlignment);

        return $this;
    }

    /**
     * @return string
     */
    public function getLabelAlignment()
    {
        return $this->labelAlignment->getValue();
    }

    /**
     * @param array $labelMargin
     * @return $this
     */
    public function setLabelMargin(array $labelMargin)
    {
        $this->labelMargin = array_merge($this->labelMargin, $labelMargin);

        return $this;
    }

    /**
     * @return array
     */
    public function getLabelMargin()
    {
        return $this->labelMargin;
    }

    /**
     * @param string $logoPath
     * @return $this
     * @throws InvalidPathException
     */
    public function setLogoPath($logoPath)
    {
        $logoPath = realpath($logoPath);

        if (!is_file($logoPath)) {
            throw new InvalidPathException('Invalid logo path: ' . $logoPath);
        }

        $this->logoPath = $logoPath;

        return $this;
    }

    /**
     * @return string
     */
    public function getLogoPath()
    {
        return $this->logoPath;
    }

    /**
     * @param int $logoSize
     * @return $this
     */
    public function setLogoSize($logoSize)
    {
        $this->logoSize = $logoSize;

        return $this;
    }

    /**
     * @return int
     */
    public function getLogoSize()
    {
        return $this->logoSize;
    }

    /**
     * @param bool $validateResult
     * @return $this
     */
    public function setValidateResult($validateResult)
    {
        $this->validateResult = $validateResult;

        return $this;
    }

    /**
     * @return bool
     */
    public function getValidateResult()
    {
        return $this->validateResult;
    }

    /**
     * @param string $writerClass
     * @return string
     * @throws MissingWriterException
     */
    public function getContentType($writerClass)
    {
        $this->assertValidWriterClass($writerClass);

        return $this->writers[$writerClass]->getContentType();
    }

    /**
     * @param string $writerClass
     * @throws MissingWriterException
     */
    protected function assertValidWriterClass($writerClass)
    {
        if (!isset($this->writers[$writerClass])) {
            throw new MissingWriterException('Invalid writer "'.$writerClass.'"');
        }
    }

    /**
     * @param string $writerClass
     * @return string
     */
    public function writeString($writerClass)
    {
        $this->assertValidWriterClass($writerClass);

        return $this->writers[$writerClass]->writeString();
    }

    /**
     * @param string $path
     * @param string $writerClass
     */
    public function writeFile($path, $writerClass = null)
    {
        $writer = $this->getWriterByPath($path);

        if ($writerClass !== null) {
            $this->assertValidWriterClass($writerClass);
            $writer = $this->writers[$writerClass];
        }

        return $writer->writeFile($path);
    }

    /**
     * @param string $path
     * @return WriterInterface
     */
    public function getWriterByPath($path)
    {
        $extension = pathinfo($path, PATHINFO_EXTENSION);

        return $this->getWriterByExtension($extension);
    }

    /**
     * @param string $extension
     * @return WriterInterface
     * @throws MissingWriterException
     */
    public function getWriterByExtension($extension)
    {
        foreach ($this->writers as $writer) {
            if (in_array($extension, $writer->getSupportedExtensions())) {
                return $writer;
            }
        }

        throw new MissingWriterException('Missing writer for extension "'.$extension.'"');
    }
}
