```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EvaluationVendeur extends Model
{
    use HasFactory;

    protected $table = 'evaluations_vendeurs';

    protected $fillable = [
        'article_id',
        'evaluateur_id',
        'vendeur_id',
        'note',
        'commentaire',
    ];

    public function article()
    {
        return $this->belongsTo(ArticleMarketplace::class, 'article_id');
    }

    public function evaluateur()
    {
        return $this->belongsTo(User::class, 'evaluateur_id');
    }

    public function vendeur()
    {
        return $this->belongsTo(User::class, 'vendeur_id');
    }
}
```
