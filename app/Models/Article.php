<?php

namespace App\Models;

use DateTimeInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Article extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'type',
        'title',
        'content',
        'user_id',
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

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function articleImages()
    {
        return $this->hasMany(ArticleImage::class);
    }

    public function disease()
    {
        return $this->hasOne(Disease::class);
    }

    public function pesticide()
    {
        return $this->hasOne(Pesticide::class);
    }

    public function tags()
    {
        return $this->hasMany(ArticleTag::class)->with('tag');
    }

    public function comments()
    {
        return $this->hasMany(Comment::class)->with('user');
    }
}
