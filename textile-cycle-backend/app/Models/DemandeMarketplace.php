<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DemandeMarketplace extends Model
{
    protected $table = 'demandes_marketplace';
    protected $fillable = ['article_id', 'acheteur_id', 'statut', 'message'];

    public function article()
    {
        return $this->belongsTo(ArticleMarketplace::class, 'article_id');
    }

    public function acheteur()
    {
        return $this->belongsTo(User::class, 'acheteur_id');
    }
}
