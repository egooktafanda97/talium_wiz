<?php

namespace TaliumAbstract\Trait;


use Illuminate\Http\Request;
use TaliumAbstract\Attributes\Contract\ReflectionMeta;
use TaliumAbstract\Attributes\Propertis;
use TaliumAbstract\Attributes\Ruters\Get;
use TaliumAbstract\Attributes\Ruters\Post;
use TaliumAbstract\Attributes\Service;
use TaliumAbstract\Attributes\StaticMethodRules;
use TaliumAbstract\Requests\RequestValidated;

trait CrudLib
{
    protected $model;
    protected $store_redirect;
    protected $serviceClass;
    protected $dataView = [];

    public function Initialize()
    {
        $props = (object)collect(ReflectionMeta::getAttribute($this, Propertis::class, 'props'))->toArray();
        $service = ReflectionMeta::getAttribute($this, Service::class, 'service');


        if (!empty($props->MainModel))
            $this->model = $props->MainModel;
        if (!empty($props->store_redirect))
            $this->store_redirect = $props->store_redirect;

        if (!empty($service)) {
            $this->serviceClass = new $service();
        }
        $StaticModelRules = ReflectionMeta::getAttribute($this, StaticMethodRules::class, 'rules');

        app()->bind(RequestValidated::class, function ($app) use ($StaticModelRules) {
            $instace_model = $StaticModelRules;
            return new RequestValidated($instace_model);
        });
    }

    // before store
    public function beforeStore($req, $request, callable $next)
    {
        $request = collect($request->validated());
        return $next($req, $request);
    }

    // before update
    public function beforeUpdate($req, $request, $id, callable $next)
    {
        $request = collect($request->validated());
        return $next($req, $request);
    }

    public function thenStore($request, $model)
    {
        return null;
    }

    public function thenUpdate($request, $model, $id)
    {
        return null;
    }

    public function __construct()
    {
        $this->Initialize();
    }

    #[Get("/show")]
    public function show(Request $request)
    {
        return view($request->view, $this->data_view());
    }

    public function data_view()
    {
        if (!empty($this->serviceClass)) {
            $Items = $this->serviceClass->all();
        } else {
            $Items = $this->model::all();
        }

        return ["Items" => $Items, ...$this->dataView];
    }

    #[Get("/create", middleware: "web")]
    public function create(Request $request)
    {
        return view($request->view, $this->data_view());
    }

    #[Get("/create/{id}", middleware: "web")]
    public function createUpdate(Request $request)
    {
        return view($request->view, $this->data_view());
    }

    #[Post("/store")]
    public function store(Request $req, RequestValidated $request)
    {
        try {
            return $this->beforeStore($req, $request, function ($req, $request) {

                $validatedData = $request;
                if (!empty($this->apped_save))
                    $validatedData->merge($this->apped_save);
                if (!empty($this->serviceClass))
                    $model = $this->serviceClass->create($validatedData->toArray());
                else
                    $model = $this->model::create($validatedData->toArray());

                if (!$model) {
                    throw new \Exception("Error saving data");
                }
                if (!empty($this->store_redirect)) {
                    $this->thenStore($req, $model);
                    return redirect($this->store_redirect)->with('success', 'Data successfully saved');
                }
                return redirect()->back()->with('success', 'Data successfully saved');
            });
        } catch (\Exception $e) {
            dd($e->getMessage());
            return redirect()->back()->with('error', $e->getMessage())->withInput();
        }
    }

    #[Post(["/update/{id}", "/store/{id}"])]
    public function update(Request $req, RequestValidated $request, $id)
    {

        try {
            return $this->beforeUpdate($req, $request, $id, function ($req, $request) use ($id) {
                if (!empty($this->apped_save))
                    $request->merge($this->apped_save);

                if (!empty($this->serviceClass))
                    $model = $this->serviceClass->update($request->toArray(), $id);
                else
                    $model = $this->model::where("id", $id)->update($request->toArray());
                if (!$model) {
                    throw new \Exception("Error update data");
                }
                if (!empty($this->store_redirect)) {
                    $this->thenUpdate($req, $model, $id);
                    return redirect($this->store_redirect)->with('success', 'Data successfully updated');
                }

                return redirect()->back()->with('success', 'Data successfully updated');
            });
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage())->withInput();
        }
    }

    #[Get("/destory/{id}")]
    public function destory($id)
    {
        try {

            $model = $this->serviceClass->delete($id);
            if (!$model) {
                throw new \Exception("Error delete data");
            }
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage())->withInput();
        }
        if (!empty($this->store_redirect)) {
            return redirect($this->store_redirect)->with('success', 'Data successfully deleted');
        }

        return redirect()->back()->with('success', 'Data successfully deleted');
    }
}
