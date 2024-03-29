<?php

namespace TaliumAbstract\Trait;


use F9Web\ApiResponseHelpers;
use Illuminate\Http\Request;
use Spatie\RouteAttributes\Attributes\Get;
use Spatie\RouteAttributes\Attributes\Post;
use Spatie\RouteAttributes\Attributes\Put;
use TaliumAbstract\Attributes\Contract\ReflectionMeta;
use TaliumAbstract\Attributes\Model;
use TaliumAbstract\Attributes\Service;
use TaliumAbstract\Attributes\StaticMethodRules;
use TaliumAbstract\Requests\RequestValidated;
use TaliumAbstract\Services\AutoCrudApiServices;

trait AutoCrudApi
{
    use ApiResponseHelpers;
    protected $Model;
    protected $main_serveice;

    public function Initialize()
    {
        try {
            $model = ReflectionMeta::getAttribute($this, Model::class, 'model');
            $StaticModelRules = ReflectionMeta::getAttribute($this, StaticMethodRules::class, 'rules');
            if (!empty($StaticModelRules))
                $this->Request($StaticModelRules);
            if (!empty($model))
                $this->Model = new $model();
            $this->main_serveice  = new AutoCrudApiServices($this, $this->Model);
        } catch (\Exception $e) {
            throw new \Exception("Incomplite");
        }
    }
    public function __construct()
    {
        $this->Initialize();
    }

    // before actions
    public function before($req, $res, $next)
    {
        $next($req, $res);
    }

    // after actions
    public function after($req, $res, $next)
    {
        $next($req, $res);
    }

    /**
     * @overide
     */
    public function append_data()
    {
        return [];
    }

    public function Request($rules = [])
    {
        /**
         * append data in method $this->append_data()
         */
        $appended = collect($rules)->merge((!empty($this->append_data()) ? $this->append_data() : []))->toArray();
        app()->bind(RequestValidated::class, function ($app) use ($appended) {
            return new RequestValidated($appended);
        });
    }

    #[Get("/show")]
    #[Get("/")]
    public function get()
    {
        return $this->respondWithSuccess($this->main_serveice->get());
    }

    #[Get("/paginate")]
    public function paging(Request $request)
    {
        return $this->respondWithSuccess($this->main_serveice->paginate('', $request->get('page')));
    }

    #[Get("/find/{id}")]
    public function getId($id)
    {
        return $this->respondWithSuccess($this->main_serveice->find($id));
    }

    #[Post("/store")]
    #[Post("/")]
    public function store(RequestValidated $request)
    {
        try {
            $this->before($request, $this, function ($req, $res) {
                $data = $this->main_serveice->store($req);
                return $this->respondCreated($data);
            });
        } catch (\Exception $e) {
            return $this->respondError($e->getMessage());
        }
    }

    #[Post("/update/{id}")]
    #[Post("/store/{id}")]
    #[Put("/")]
    public function update(RequestValidated $request, $id)
    {
        try {
            $this->before($request, $id, function ($req, $res) {
                $data = $this->main_serveice->update($req, $res);
                return $this->respondWithSuccess($data);
            });
        } catch (\Exception $e) {
            return $this->respondError($e->getMessage());
        }
    }

    #[Get("/destory/{id}")]
    #[Get("/delete/{id}")]
    public function destory($id)
    {
        try {
            $model = $this->main_serveice->destory($id);
            if (!$model) {
                throw new \Exception("Error delete data");
            }
        } catch (\Exception $e) {
            return $this->respondError($e->getMessage());
        }
        return $this->respondOk('delete successfully!');
    }
}
