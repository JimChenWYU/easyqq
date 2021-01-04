<?php

namespace EasyQQ\Kernel\Contracts;

use ArrayAccess;

/**
 * Interface Arrayable
 *
 * @author JimChen <imjimchen@163.com>
 */
interface Arrayable extends ArrayAccess
{
	/**
	 * Get the instance as an array.
	 *
	 * @return array
	 */
	public function toArray();
}
