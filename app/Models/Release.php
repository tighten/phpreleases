<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Release extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $appends = ['needs_patch', 'needs_upgrade', 'changelog_url'];

    protected function casts(): array
    {
        return [
            'tagged_at' => 'datetime',
            'active_support_until' => 'datetime',
            'security_support_until' => 'datetime',
        ];
    }

    public function scopeLatestRelease($query)
    {
        return $query->orderByDesc('major')
            ->orderByDesc('minor')
            ->orderByDesc('release')
            ->limit(1);
    }

    public function scopeLatestReleaseForMinorVersion($query, $major, $minor)
    {
        return $query->where('major', (string) $major)
            ->where('minor', (string) $minor)
            ->orderByDesc('release')
            ->limit(1);
    }

    public function scopeHasActiveSupport($query)
    {
        return $query->where('active_support_until', '>', now());
    }

    public function scopeHasSecuritySupport($query)
    {
        return $query->where('security_support_until', '>', now());
    }

    public function getNeedsUpgradeAttribute()
    {
        return $this->attributes['needs_upgrade'] = $this->active_support_until < now();
    }

    public function getNeedsPatchAttribute()
    {
        return $this->attributes['needs_patch'] = $this->needs_upgrade || $this->release !== Release::latestReleaseForMinorVersion($this->major, $this->minor)->first()->release;
    }

    public function getChangelogUrlAttribute()
    {
        return "https://www.php.net/ChangeLog-{$this->major}.php#{$this->__toString()}";
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
