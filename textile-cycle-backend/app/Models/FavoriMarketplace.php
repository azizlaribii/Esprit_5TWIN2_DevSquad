<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FavoriMarketplace extends Model
{
    protected $table = 'favoris_marketplace';
    protected $fillable = ['user_id', 'article_id'];

    public function article()
    {
        return $this->belongsTo(ArticleMarketplace::class, 'article_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
