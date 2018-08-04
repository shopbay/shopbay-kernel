<?php
/**
 * Image helper class file.
 * @author Christoffer Niska <ChristofferNiska@gmail.com>
 * @copyright Copyright &copy; Christoffer Niska 2011-
 * @license http://www.opensource.org/licenses/bsd-license New BSD License
 * @since 0.5
 */
class Img
{
	const METHOD_RESIZE='resize';
	const METHOD_RESIZE_PERCENT='resizePercent';
	const METHOD_ADAPTIVE_RESIZE='adaptiveResize';
	const METHOD_CROP='crop';
	const METHOD_CROP_CENTER='cropFromCenter';
	const METHOD_ROTATE='rotate';
	const METHOD_ROTATE_DEGREES='rotateDegrees';

	const DIRECTION_CLOCKWISE='CW';
	const DIRECTION_COUNTER_CLOCKWISE='CCW';

	/**
	 * Translates a message to the specified language.
	 * @param string $category message category.
	 * @param string $message the original message
	 * @param array $params parameters to be applied to the message.
	 * @param string $source which message source application component to use.
	 * @param string $language the target language.
	 * @return string the translated message.
	 * @see YiiBase::t()
	 */
	public static function t($category,$message,$params=array(),$source=null,$language=null)
	{
		return Yii::t('ImageModule.'.$category,$message,$params,$source,$language);
	}
}
