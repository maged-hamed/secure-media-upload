<?php

declare(strict_types=1);

namespace Maged\SecureMediaUpload\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Maged\SecureMediaUpload\Contracts\PostUploadProcessor;
use Maged\SecureMediaUpload\Results\UploadResult;

class RunPostUploadProcessorJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /**
     * @param array{type:string,disk:string} $context
     */
    public function __construct(
        public array $result,
        public array $context,
    ) {
    }

    public function handle(PostUploadProcessor $processor): void
    {
        $processor->process(UploadResult::fromArray($this->result), $this->context);
    }
}

