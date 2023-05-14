<?php

namespace App\Models;

use DateTimeInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Tag extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'tag',
    ];

    /**
     * Prepare a date for array / JSON serialization.
     *
     * @param  \DateTimeInterface  $date
     * @return string
     */
    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }

    public function articleTags()
    {
        return $this->hasMany(ArticleTag::class);
    }

    public function diseaseTags()
    {
        return $this->hasMany(DiseaseTag::class);
    }

    public function pesticideTags()
    {
        return $this->hasMany(PesticideTag::class);
    }
}
