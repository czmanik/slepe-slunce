<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
class ContentTranslation extends Model {
 protected $fillable=['locale','content','machine_translated_at','reviewed_at'];
 protected function casts(): array { return ['content'=>'array','machine_translated_at'=>'datetime','reviewed_at'=>'datetime']; }
 public function translatable(): MorphTo { return $this->morphTo(); }
}