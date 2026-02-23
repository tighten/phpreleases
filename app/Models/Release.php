<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Release extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $appends = ['needs_patch', 'needs_upgrade', 'changelog_url'];

    public function getNeedsUpgradeAttribute()
    {
        return $this->attributes['needs_upgrade'] = $this->active_support_until < now();
    }

    public function getNeedsPatchAttribute()
    {
        return $this->attributes['needs_patch'] = $this->needs_upgrade || $this->release !== Release::query()->latestReleaseForMinorVersion($this->major, $this->minor)->first()->release;
    }

    public function getChangelogUrlAttribute()
    {
        return "https://www.php.net/ChangeLog-{$this->major}.php#{$this->__toString()}";
    }

    #[Scope]
    protected function latestRelease($query)
    {
        return $query->orderByDesc('major')
            ->orderByDesc('minor')
            ->orderByDesc('release')
            ->limit(1);
    }

    #[Scope]
    protected function latestReleaseForMinorVersion($query, $major, $minor)
    {
        return $query->where('major', (string) $major)
            ->where('minor', (string) $minor)
            ->orderByDesc('release')
            ->limit(1);
    }

    #[Scope]
    protected function hasActiveSupport($query)
    {
        return $query->where('active_support_until', '>', now());
    }

    #[Scope]
    protected function hasSecuritySupport($query)
    {
        return $query->where('security_support_until', '>', now());
    }

    protected function casts(): array
    {
        return [
            'tagged_at' => 'datetime',
            'active_support_until' => 'datetime',
            'security_support_until' => 'datetime',
        ];
    }

    public function __toString()
    {
        return implode('.', [
            $this->major,
            $this->minor,
            $this->release,
        ]);
    }
}
