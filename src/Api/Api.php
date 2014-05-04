<?php
final class Api
{
	protected static $checkPrivileges = true;

	public static function run($job, $jobArgs)
	{
		$user = Auth::getCurrentUser();

		return \Chibi\Database::transaction(function() use ($job, $jobArgs)
		{
			$job->setArguments($jobArgs);
			$job->prepare();

			self::checkPrivileges($job);

			return $job->execute();
		});
	}

	public static function checkPrivileges(AbstractJob $job)
	{
		if (!self::$checkPrivileges)
			return;

		if ($job->requiresAuthentication())
			Access::assertAuthentication();

		if ($job->requiresConfirmedEmail())
			Access::assertEmailConfirmation();

		$privileges = $job->requiresPrivilege();
		if ($privileges !== false)
		{
			if (!is_array($privileges))
				$privileges = [$privileges];

			foreach ($privileges as $privilege)
				Access::assert($privilege);
		}
	}

	public static function runMultiple($jobs)
	{
		$statuses = [];
		\Chibi\Database::transaction(function() use ($jobs, &$statuses)
		{
			foreach ($jobs as $jobItem)
			{
				list ($job, $jobArgs) = $jobItem;
				$statuses []= self::run($job, $jobArgs);
			}
		});
		return $statuses;
	}

	public static function disablePrivilegeChecking()
	{
		self::$checkPrivileges = false;
	}

	public static function enablePrivilegeChecking()
	{
		self::$checkPrivileges = true;
	}
}
