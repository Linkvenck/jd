<?php
/**
 * @package     FOF
 * @copyright Copyright (c)2010-2018 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license     GNU GPL version 2 or later
 */

namespace FOF30\Form\Exception;

use Exception;

defined('_JEXEC') or die;

/**
 * Class GetStaticNotAllowed
 * @package FOF30\Form\Exception
 * @deprecated 3.1  Support for XML forms will be removed in FOF 4
 */
class GetStaticNotAllowed extends \LogicException
{
	public function __construct($className, $code = 0, Exception $previous = null)
	{
		$message = \JText::sprintf('LIB_FOF_FORM_ERR_GETSTATIC_NOT_ALLOWED', $className);

		parent::__construct($message, $code, $previous);
	}
}
