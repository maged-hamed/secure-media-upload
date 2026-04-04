<?php

declare(strict_types=1);

namespace Maged\SecureMediaUpload\Processing;

use Maged\SecureMediaUpload\Contracts\PostUploadProcessor;
use Maged\SecureMediaUpload\Results\UploadResult;

class NullPostUploadProcessor implements PostUploadProcessor
{
	public function process(UploadResult $result, array $context): void
	{
		// Intentionally left blank: default processor does nothing.
	}
}

