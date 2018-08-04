<?php
/**
 * Image thumbnail class file.
 * @author Christoffer Niska <ChristofferNiska@gmail.com>
 * @copyright Copyright &copy; Christoffer Niska 2011-
 * @license http://www.opensource.org/licenses/bsd-license New BSD License
 * @since 0.5
 */

/**
 * Wraps the ThumbBase object to provide extended oop-functionality.
 * @see http://phpthumb.gxdlabs.com/
 */
class ImgThumb extends CComponent
{
	/**
	 * @property ThumbBase the PhpThumb object.
	 */
	private $_thumb;

	/**
	 * Creates a new thumbnail.
	 * @param ThumbBase $thumb the PhpThumb object.
	 */
	public function __construct($thumb)
	{
		$this->_thumb=$thumb;
	}

	/**
	 * Applies the given options onto this image.
	 * @param ImgOptions $options the image options.
	 */
	public function applyOptions($options)
	{
		if( ($options->width!==null && $options->height!==null) || $options->resizeMethod!==null)
		{
			if(isset($options->resizeMethod)===false)
				$options->resizeMethod=Img::METHOD_RESIZE;

			switch($options->resizeMethod)
			{
				case Img::METHOD_RESIZE:
					if($options->width!==null && $options->height!==null)
						$this->resize($options->width,$options->height);
					break;

				case Img::METHOD_ADAPTIVE_RESIZE:
					if($options->width!==null && $options->height!==null)
						$this->adaptiveResize($options->width,$options->height);
					break;

				case Img::METHOD_RESIZE_PERCENT:
					if($options->percent!==null)
						$this->resizePercent($options->percent);
					break;

				default:
					throw new ImgException(Img::t('error','Failed to resize image! Resize method is unknown.'));
			}
		}

		if($options->cropMethod!==null)
		{
			switch($options->cropMethod)
			{
				case Img::METHOD_CROP:
					if($options->cropX!==null && $options->cropY!==null && $options->cropWidth!==null && $options->cropHeight!==null)
						$this->crop($options->cropX,$options->cropY,$options->cropWidth,$options->cropHeight);
					break;

				case Img::METHOD_CROP_CENTER:
					if($options->cropWidth!==null)
						$this->cropFromCenter($options->cropWidth,$options->cropHeight);
					break;

				default:
					throw new ImgException(Img::t('error','Failed to crop image! Crop method is unknown.'));
			}
		}

		if($options->rotateMethod!==null)
		{
			switch($options->rotateMethod)
			{
				case Img::METHOD_ROTATE:
					if($options->rotateDirection!==null)
						$this->rotate($options->rotateDirection);
					break;

				case Img::METHOD_ROTATE_DEGREES:
					if($options->rotateDegrees!==null)
						$this->rotateDegrees($options->rotateDegrees);
					break;

				default:
					throw new ImgException(Img::t('error','Failed to rotate image! Rotate method is unknown.'));
			}
		}
	}

	/**
	 * Re-sizes this image to the given dimensions.
	 * If either param is set to zero, then that dimension will not be considered as a part of the resize.
	 * @param integer $maxWidth the maximum width.
	 * @param integer $maxHeight the maximum height.
	 * @return ImgThumb
	 */
	public function resize($maxWidth=0,$maxHeight=0)
	{
		$this->_thumb=$this->_thumb->resize($maxWidth,$maxHeight);
		return $this;
	}

	/**
	 * Re-sizes this image so that it is as close to the given dimensions as possible,
	 * and then crops the remaining overflow (from the center).
	 * @param integer $width the width to crop the image to.
	 * @param integer $height the height to crop the image to.
	 * @return ImgThumb
	 */
	public function adaptiveResize($width,$height)
	{
		$this->_thumb=$this->_thumb->adaptiveResize($width,$height);
		return $this;
	}

	/**
	 * Re-sizes this image by the given percent uniformly.
	 * @param integer $percent the percent to resize by.
	 * @return ImgThumb
	 */
	public function resizePercent($percent)
	{
		$this->_thumb=$this->_thumb->resizePercent($percent);
		return $this;
	}

	/**
	 * Crops this image from the given coordinates with the specified width and height.
	 * This is also known as Vanilla-cropping.
	 * @param integer $x the starting x-coordinate.
	 * @param integer $y the starting y-coordinate.
	 * @param integer $width the width to crop with.
	 * @param integer $height the height to crop with.
	 * @return ImgThumb
	 */
	public function crop($x,$y,$width,$height)
	{
		$this->_thumb=$this->_thumb->crop($x,$y,$width,$height);
		return $this;
	}

	/**
	 * Crops this image from the center with the specified width and height.
	 * @param integer $width the width to crop with.
	 * @param integer $height the height to crop with, if null the height will be the same as the width.
	 * @return ImgThumb
	 */
	public function cropFromCenter($width,$height=null)
	{
		$this->_thumb=$this->_thumb->cropFromCenter($width,$height);
		return $this;
	}

	/**
	 * Rotates this image by 90 degrees in the specified direction.
	 * @param string $direction the direction to rotate the image in.
	 * @return ImgThumb
	 */
	public function rotate($direction=Img::DIRECTION_CLOCKWISE)
	{
		$this->_thumb=$this->_thumb->rotateImage($direction);
		return $this;
	}

	/**
	 * Rotates this image by the specified amount of degrees.
	 * The image is always rotated clock-wise.
	 * @param integer $degrees the amount of degrees.
	 * @return ImgThumb
	 */
	public function rotateDegrees($degrees)
	{
		$this->_thumb=$this->_thumb->rotateImageNDegrees($degrees);
		return $this;
	}

	/**
	 * Saves this image.
	 * @param string $path the path where to save the image.
	 * @param string $extension the file extension.
	 * @return ImgThumb
	 */
	public function save($path,$extension=null)
	{
		$this->_thumb=$this->_thumb->save($path,$extension);
		return $this;
	}

	/**
	 * Renders this image.
	 * @return ImgThumb
	 */
	public function render()
	{
		$this->_thumb=$this->_thumb->show();
		return $this;
	}
}
