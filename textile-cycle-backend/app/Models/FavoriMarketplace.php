```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FavoriMarketplace extends Model
{
    use HasFactory;

    protected $table = 'favoris_marketplace';

    protected $fillable = [
        'user_id',
        'article_id',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function article()
    {
        return $this->belongsTo(ArticleMarketplace::class, 'article_id');
    }
}
```
