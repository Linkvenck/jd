<?php
/**
 * Akeeba Engine
 * The modular PHP5 site backup engine
 * @copyright Copyright (c)2006-2018 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU GPL version 3 or, at your option, any later version
 * @package   akeebaengine
 *
 */

namespace Akeeba\Engine\Util;

// Protection against direct access
defined('AKEEBAENGINE') or die();

use Akeeba\Engine\Base\BaseObject;
use Akeeba\Engine\Factory;
use Akeeba\Engine\Platform;
use Psr\Log\LogLevel;

class Statistics extends BaseObject
{
	/** @var bool used to block multipart updating initializing the backup */
	private $multipart_lock = true;

	/** @var int The statistics record number of the current backup attempt */
	private $statistics_id = null;

	/** @var array Local cache of the stat record data */
	private $cached_data = array();

	/**
	 * Releases the initial multipart lock
	 */
	public function release_multipart_lock()
	{
		$this->multipart_lock = false;
	}

	/**
	 * Updates the multipart status of the current backup attempt's statistics record
	 *
	 * @param int $multipart The new multipart status
	 */
	public function updateMultipart($multipart)
	{
		if ($this->multipart_lock)
		{
			return;
		}

		Factory::getLog()->log(LogLevel::DEBUG, 'Updating multipart status to ' . $multipart);

		// Cache this change and commit to db only after the backup is done, or failed
		$registry = Factory::getConfiguration();
		$registry->set('volatile.statistics.multipart', $multipart);
	}

	/**
	 * Sets or updates the statistics record of the current backup attempt
	 *
	 * @param array $data
	 */
	public function setStatistics($data)
	{
		$ret = Platform::getInstance()->set_or_update_statistics($this->statistics_id, $data, $this);
		if ($ret !== false)
		{
			if (!is_null($ret))
			{
				$this->statistics_id = $ret;
			}
			$this->cached_data = array_merge($this->cached_data, $data);
			$result = true;
		}
		elseif ($ret === false)
		{
			$result = false;
		}

		return $result;
	}

	/**
	 * Returns the statistics record ID (used in DB backup classes)
	 * @return int
	 */
	public function getId()
	{
		return $this->statistics_id;
	}

	/**
	 * Returns a copy of the cached data
	 * @return array
	 */
	public function getRecord()
	{
		return $this->cached_data;
	}


	/**
	 * Returns all the filenames of the backup archives for the specified stat record,
	 * or null if the backup type is wrong or the file doesn't exist. It takes into
	 * account the multipart nature of Split Backup Archives.
	 *
	 * @param array $stat            The backup statistics record
	 * @param bool  $skipNonComplete Skips over backups with no files produced
	 *
	 * @return array|null The filenames or null if it's not applicable
	 */
	public static function get_all_filenames($stat, $skipNonComplete = true)
	{
		// Shortcut for database entries marked as having no files
		if ($stat['filesexist'] == 0)
		{
			return array();
		}

		// Initialize
		$base_directory = @dirname($stat['absolute_path']);
		$base_filename = $stat['archivename'];
		$filenames = array($base_filename);

		if (empty($base_filename))
		{
			// This is a backup with a writer which doesn't store files on the server
			return null;
		}

		// Calculate all the filenames for this backup
		if ($stat['multipart'] > 1)
		{
			// Find the base filename and extension
			$dotpos = strrpos($base_filename, '.');
			$extension = substr($base_filename, $dotpos);
			$basefile = substr($base_filename, 0, $dotpos);

			// Calculate the multiple names
			$multipart = $stat['multipart'];
			for ($i = 1; $i < $multipart; $i++)
			{
				// Note: For $multipart = 10, it will produce i.e. .z01 through .z10
				// This is intentional. If the backup aborts and multipart=1, we
				// might be stuck with a .z01 file instead of a .zip. So do not
				// change the less than or equal with a straight less than.
				$filenames[] = $basefile . substr($extension, 0, 2) . sprintf('%02d', $i);
			}
		}

		// Check if the files exist, otherwise attempt to provide relocated filename
		$ret = array();

		$ds = DIRECTORY_SEPARATOR;
		// $test_file is the first file which must have been created
		$test_file = count($filenames) == 1 ? $filenames[0] : $filenames[1];
		if (
			(!@file_exists($base_directory . $ds . $test_file)) ||
			(!is_dir($base_directory))
		)
		{
			// The test file wasn't detected. Use the configured output directory.
			$registry = Factory::getConfiguration();
			$base_directory = $registry->get('akeeba.basic.output_directory');
		}

		foreach ($filenames as $filename)
		{
			// Turn relative path to absolute
			$filename = $base_directory . $ds . $filename;

			// Return the new filename IF IT EXISTS!
			if (!@file_exists($filename))
			{
				$filename = '';
			}

			// Do not return filename for invalid backups
			if (!empty($filename))
			{
				$ret[] = $filename;
			}
		}

		// Edge case: still running backups, we have to brute force the scan
		// of existing files (multipart may be lying)
		if ($stat['status'] == 'run')
		{
			$base_filename = $stat['archivename'];
			$dotpos = strrpos($base_filename, '.');
			$extension = substr($base_filename, $dotpos);
			$basefile = substr($base_filename, 0, $dotpos);

			$registry = Factory::getConfiguration();
			$dirs = array(
				@dirname($stat['absolute_path']),
				$registry->get('akeeba.basic.output_directory')
			);

			// Look for base file
			foreach ($dirs as $dir)
			{
				if (@file_exists($dir . $ds . $base_filename))
				{
					$ret[] = $dir . $ds . $base_filename;
					break;
				}
			}

			// Look for added files
			$found = true;
			$i = 0;
			while ($found)
			{
				$i++;
				$found = false;
				$part_file_name = $basefile . substr($extension, 0, 2) . sprintf('%02d', $i);
				foreach ($dirs as $dir)
				{
					if (@file_exists($dir . $ds . $part_file_name))
					{
						$ret[] = $dir . $ds . $part_file_name;
						$found = true;
						break;
					}
				}
			}
		}

		if ((count($ret) == 0) && $skipNonComplete)
		{
			$ret = null;
		}

		if (!empty($ret) && is_array($ret))
		{
			$ret = array_unique($ret);
		}

		return $ret;
	}
}
