<?php

namespace App\Http\Resources;

use App\Models\Release;
use Illuminate\Http\Resources\Json\JsonResource;

class ReleaseResource extends JsonResource
{
    public static $wrap = 'provided';

    public function toArray($request)
    {
        return [
            'major' => $this->major,
            'minor' => $this->minor,
            'release' => $this->release,
            'tagged_at' => $this->tagged_at,
            'active_support_until' => $this->active_support_until,
            'security_support_until' => $this->security_support_until,
            'needs_patch' => $this->needs_patch,
            'needs_upgrade' => $this->needs_upgrade,
            'changelog_url' => $this->changelog_url,
        ];
    }

    public function with($request)
    {
        return [
            'latest_release' => (string) Release::orderByDesc('major')
                ->orderByDesc('minor')
                ->orderByDesc('release')
                ->first(),
        ];
    }
}
