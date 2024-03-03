### documentations
--- penggunaan
###
### Scema Model
```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Lerouse\LaravelRepository\HasRepository;
use TaliumAbstract\Attributes\Table;
use TaliumAbstract\Trait\InjectModel;

#[Table("nama tabel")]
class ... extends Model
{
    use HasFactory;
    /**
     * untuk menyusun fillable dan table pada atribute dan rules
     */
    use InjectModel;
    
    /**
     * jika menggunakan __construct tambahkan 
     *  $this->Iject(); agar attribute dapat di exekusi
     */
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->Iject();
    }

    /**
     * harus dengan nama rules 
     */
    public static function rules()
    {
        return [
            "name" => "required",
            "description" => "nullable"
        ];
    }
}

```

### Scema Controller
``` php
<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\MastersStatus;
use Illuminate\Http\Request;
use Spatie\RouteAttributes\Attributes\Middleware;
use Spatie\RouteAttributes\Attributes\Prefix;

// attribute yang digunakan
use TaliumAbstract\Attributes\Service;
use TaliumAbstract\Attributes\StaticMethodRules;
use TaliumAbstract\Trait\AutoCrudApi;

#[Prefix("api/status")]
#[Middleware("api")]
/**
 * sebuah class yang didalam nya terdapat static method rules dengan response
 * array
 */
#[StaticMethodRules(MastersStatus::class)]
/**
 * ini dapat menggunakan Class Service atau model
 */
#[Model(MastersStatus::class)]
class MastersStatusController extends Controller
{
    use AutoCrudApi;


}

```

### Iject store data
``` php
#[Model(MastersStatus::class)]
class MastersStatusController extends Controller
{
     use AutoCrudApi;
     public function __construct(Request $request)
     {
        $request->merge([
            'status_anggota' => 'aktif',
            'bawaslu_id' => 1,
            'user_id' => 1,
        ]);
        $this->Initialize();
    }
}
```


###
### Api Request Http
```php
    http://domain/api
 
    /**
     * request basis all
    */
    #[Get("/show")]
    #[Get("/")]
   
    /**
     * request basis paginate
    */
    #[Get("/paginate")]
    
    /**
     * request basis firstId
    */
    #[Get("/find/{id}")]
   

    /**
     * request store
    */
    #[Post("/store")]
    #[Post("/")]
   

    /**
     * request update
    */
    #[Post("/update/{id}")]
    #[Post("/store/{id}")]
    #[Put("/")]
   

    /**
     * request delete
    */
    #[Get("/destory/{id}")]
    #[Get("/delete/{id}")]
```